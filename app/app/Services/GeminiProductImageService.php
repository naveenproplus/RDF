<?php

namespace App\Services;

use App\enums\docTypes;
use App\helper\helper;
use App\Models\DocNum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GeminiProductImageService
{
    public const MAX_BYTES = 1048576;
    public const TARGET_SIZE = 1024;

    /** @var array<string, string> */
    private array $slotAngles = [
        'main' => 'hero product shot: generous neat pile of the edible product centered, soft even studio lighting, slight top-down angle',
        'gallery_1' => 'extreme close-up macro texture shot showing natural detail and quality of the product only',
        'gallery_2' => 'product arranged in a simple white ceramic bowl centered on pure white seamless background, classic e-commerce style',
        'gallery_3' => 'alternate angle: product spread in a clean circular mound, slight 45-degree camera angle, same pure white studio setup',
    ];

    public function webRoot(): string
    {
        // Document root = repo root (index.php + uploads/). Laravel lives in /app
        return dirname(base_path());
    }

    public function isProductImageEmpty(?string $productImage): bool
    {
        $value = trim((string) $productImage);
        if ($value === '') {
            return true;
        }

        $lower = strtolower($value);
        if (
            str_contains($lower, 'no-image')
            || str_contains($lower, 'no_image')
            || str_contains($lower, 'no-images')
            || str_contains($lower, 'placeholder')
        ) {
            return true;
        }

        return !is_file($this->absolutePath($value));
    }

    /**
     * @return Collection<int, object>
     */
    public function eligibleProducts(?int $limit = null, ?string $productId = null): Collection
    {
        $query = DB::table('tbl_products as P')
            ->leftJoin('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
            ->leftJoin('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
            ->where('P.DFlag', 0)
            ->where('P.ActiveStatus', 'Active')
            ->select(
                'P.ProductID',
                'P.ProductName',
                'P.ProductImage',
                'P.ShortDescription',
                'PC.PCName',
                'PSC.PSCName'
            )
            ->orderBy('P.ProductID');

        if ($productId) {
            $query->where('P.ProductID', $productId);
        }

        $rows = $query->get()
            ->filter(fn ($row) => $this->isProductImageEmpty($row->ProductImage ?? null))
            ->values();

        if ($limit !== null && $limit > 0) {
            $rows = $rows->take($limit)->values();
        }

        return $rows;
    }

    public function buildPrompt(object $product, string $slot): string
    {
        $angle = $this->slotAngles[$slot] ?? $this->slotAngles['main'];
        $name = trim((string) ($product->ProductName ?? 'dry fruit product'));
        $category = trim((string) ($product->PCName ?? 'Dry Fruits'));
        $sub = trim((string) ($product->PSCName ?? ''));
        $subBit = $sub !== '' ? ", subcategory {$sub}" : '';

        return implode(' ', [
            "Professional e-commerce product photograph of \"{$name}\" ({$category}{$subBit})",
            'for Indian premium brand Royal Dry Fruits.',
            $angle . '.',
            'Pure white seamless infinity studio background, soft diffused lighting, centered composition,',
            'photorealistic food photography, sharp focus, natural colors, high detail,',
            'square 1:1 aspect ratio, no packaging text, no labels, no logo, no watermark,',
            'no hands, no people, no props except what the angle requires, no clutter.',
        ]);
    }

    /**
     * Generate 1 main + 3 gallery images and map them to the product.
     *
     * @return array<string, mixed>
     */
    public function generateAndMap(object $product, bool $dryRun = false): array
    {
        $slots = ['main', 'gallery_1', 'gallery_2', 'gallery_3'];
        $generated = [];

        foreach ($slots as $slot) {
            $prompt = $this->buildPrompt($product, $slot);
            if ($dryRun) {
                $generated[$slot] = ['prompt' => $prompt];
                continue;
            }

            $binary = $this->callImageApi($prompt);
            $jpeg = $this->toSquareJpegUnder1Mb($binary);
            $generated[$slot] = [
                'prompt' => $prompt,
                'bytes' => $jpeg,
                'size' => strlen($jpeg),
            ];
        }

        if ($dryRun) {
            return [
                'product_id' => $product->ProductID,
                'product_name' => $product->ProductName,
                'prompts' => array_map(fn ($g) => $g['prompt'], $generated),
                'dry_run' => true,
            ];
        }

        $saved = $this->persist($product->ProductID, $generated);
        $saved['product_name'] = $product->ProductName;
        $saved['prompts'] = array_map(fn ($g) => $g['prompt'], $generated);
        $saved['sizes'] = array_map(fn ($g) => $g['size'] ?? null, $generated);

        return $saved;
    }

    public function callImageApi(string $prompt): string
    {
        $provider = strtolower((string) config('app.AI_IMAGE_PROVIDER', 'openai'));

        return match ($provider) {
            'openai', 'chatgpt' => $this->callOpenAiImage($prompt),
            'gemini' => $this->callGeminiImage($prompt),
            default => throw new RuntimeException("Unsupported AI_IMAGE_PROVIDER [{$provider}]. Use openai or gemini."),
        };
    }

    public function callOpenAiImage(string $prompt): string
    {
        $apiKey = (string) config('app.OPENAI_API_KEY');
        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is not set in .env (use an OpenAI platform API key, not ChatGPT Go login)');
        }

        // Prefer gpt-image-1 — many accounts no longer expose dall-e-3.
        $model = (string) config('app.OPENAI_IMAGE_MODEL', 'gpt-image-1');
        $size = (string) config('app.OPENAI_IMAGE_SIZE', '1024x1024');

        // Keep payload minimal — newer OpenAI image models reject response_format.
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'size' => $size,
        ];

        if ($model === 'dall-e-2' || $model === 'dall-e-3') {
            $payload['n'] = 1;
            if ($model === 'dall-e-3') {
                $payload['quality'] = (string) config('app.OPENAI_IMAGE_QUALITY', 'standard');
            }
        } elseif (str_starts_with($model, 'gpt-image')) {
            // gpt-image-1: quality = low|medium|high|auto (not standard/hd)
            $quality = strtolower((string) config('app.OPENAI_IMAGE_QUALITY', 'medium'));
            $qualityMap = [
                'standard' => 'medium',
                'hd' => 'high',
                'low' => 'low',
                'medium' => 'medium',
                'high' => 'high',
                'auto' => 'auto',
            ];
            $payload['quality'] = $qualityMap[$quality] ?? 'medium';
        }

        $response = Http::timeout(180)
            ->withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/images/generations', $payload);

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException("OpenAI image API failed ({$response->status()}): {$message}");
        }

        $item = data_get($response->json(), 'data.0');
        if (!$item) {
            Log::warning('OpenAI response missing image data', ['body' => $response->json()]);
            throw new RuntimeException('OpenAI response did not include an image.');
        }

        if (!empty($item['b64_json'])) {
            $decoded = base64_decode($item['b64_json'], true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        if (!empty($item['url'])) {
            $imageResponse = Http::timeout(120)->get($item['url']);
            if ($imageResponse->successful() && $imageResponse->body() !== '') {
                return $imageResponse->body();
            }
            throw new RuntimeException('OpenAI returned an image URL but download failed.');
        }

        throw new RuntimeException('OpenAI response did not include b64_json or url.');
    }

    public function callGeminiImage(string $prompt): string
    {
        $apiKey = (string) config('app.GEMINI_API_KEY');
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is not set in .env');
        }

        $model = (string) config('app.GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(180)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseModalities' => ['TEXT', 'IMAGE'],
                    'imageConfig' => [
                        'aspectRatio' => '1:1',
                    ],
                ],
            ]);

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException("Gemini image API failed ({$response->status()}): {$message}");
        }

        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
            if (!$inline || empty($inline['data'])) {
                continue;
            }
            $decoded = base64_decode($inline['data'], true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        Log::warning('Gemini response missing image part', ['body' => $response->json()]);
        throw new RuntimeException('Gemini response did not include an image.');
    }

    public function toSquareJpegUnder1Mb(string $binary): string
    {
        if (!function_exists('imagecreatefromstring')) {
            throw new RuntimeException('PHP GD extension is required.');
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            throw new RuntimeException('Unable to decode generated image.');
        }

        $jpeg = $this->encodeSquareJpeg($src, self::TARGET_SIZE);
        imagedestroy($src);
        if ($jpeg !== null) {
            return $jpeg;
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            throw new RuntimeException('Unable to decode image for fallback compress.');
        }
        $jpeg = $this->encodeSquareJpeg($src, 800);
        imagedestroy($src);
        if ($jpeg !== null) {
            return $jpeg;
        }

        throw new RuntimeException('Unable to compress image under 1 MB.');
    }

    /**
     * @param array<string, array{prompt: string, bytes: string, size?: int}> $generated
     * @return array{product_id: string, product_image: string, gallery: array<int, string>, dry_run: bool}
     */
    public function persist(string $productId, array $generated): array
    {
        $relativeDir = "uploads/master/product/products/{$productId}/";
        $relativeGalleryDir = $relativeDir . 'gallery/';

        foreach ([$relativeDir, $relativeGalleryDir] as $rel) {
            $abs = $this->absolutePath($rel);
            if (!is_dir($abs) && !mkdir($abs, 0777, true) && !is_dir($abs)) {
                throw new RuntimeException("Cannot create directory: {$abs}");
            }
        }

        $previousCwd = getcwd();
        chdir($this->webRoot());

        $mainRelative = null;
        $galleryPaths = [];

        try {
            DB::beginTransaction();

            $stamp = date('YmdHis');
            $mainFile = Helper::RandomString(10) . "_{$stamp}.jpg";
            $mainRelative = $relativeDir . $mainFile;
            if (file_put_contents($mainRelative, $generated['main']['bytes']) === false) {
                throw new RuntimeException("Failed writing main image for {$productId}");
            }
            @chmod($mainRelative, 0644);

            $resized = $this->safeImageResize($mainRelative, $relativeDir);

            $existing = DB::table('tbl_products')->where('ProductID', $productId)->first();
            if (!$existing) {
                throw new RuntimeException("Product not found in DB: {$productId}");
            }
            $this->deleteStoredImages($existing->ProductImage ?? null, $existing->Images ?? null);
            $actor = (string) ($existing->UpdatedBy ?? $existing->CreatedBy ?? '');

            $updated = DB::table('tbl_products')->where('ProductID', $productId)->update([
                'ProductImage' => $mainRelative,
                'Images' => serialize($resized),
                'UpdatedOn' => date('Y-m-d H:i:s'),
                'UpdatedBy' => $actor,
            ]);
            if ($updated < 1) {
                throw new RuntimeException("DB update affected 0 rows for ProductID {$productId}");
            }

            $oldGallery = DB::table('tbl_products_gallery')->where('ProductID', $productId)->get();
            foreach ($oldGallery as $old) {
                $this->deleteStoredImages($old->gImage ?? null, $old->Images ?? null);
            }
            DB::table('tbl_products_gallery')->where('ProductID', $productId)->delete();

            foreach (['gallery_1', 'gallery_2', 'gallery_3'] as $i => $slot) {
                $gFile = Helper::RandomString(12) . "_{$stamp}_{$i}.jpg";
                $gRelative = $relativeGalleryDir . $gFile;
                if (file_put_contents($gRelative, $generated[$slot]['bytes']) === false) {
                    throw new RuntimeException("Failed writing gallery image for {$productId}");
                }
                @chmod($gRelative, 0644);

                $gResized = $this->safeImageResize($gRelative, $relativeGalleryDir);
                $imgId = 'AI' . Helper::RandomString(12);
                $slno = DocNum::getDocNum(docTypes::ProductGallery->value, '', Helper::getCurrentFY());
                if ($slno === '' || $slno === null) {
                    throw new RuntimeException('DocNum for Product-Gallery is empty. Check tbl_docnum.');
                }

                $ok = DB::table('tbl_products_gallery')->insert([
                    'SLNO' => $slno,
                    'ProductID' => $productId,
                    'ImgID' => $imgId,
                    'gImage' => $gRelative,
                    'Images' => serialize($gResized),
                    'CreatedBy' => $actor,
                    'CreatedOn' => date('Y-m-d H:i:s'),
                ]);
                if (!$ok) {
                    throw new RuntimeException("Failed inserting gallery row for {$productId}");
                }

                DocNum::updateDocNum(docTypes::ProductGallery->value);
                $galleryPaths[] = $gRelative;
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            if ($previousCwd) {
                chdir($previousCwd);
            }
        }

        // Prove the write stuck (catches silent 0-row updates / wrong DB)
        $check = DB::table('tbl_products')->where('ProductID', $productId)->value('ProductImage');
        $galleryCount = DB::table('tbl_products_gallery')->where('ProductID', $productId)->count();
        if (trim((string) $check) === '' || $check !== $mainRelative) {
            throw new RuntimeException(
                "Import wrote files but DB ProductImage not saved (got: " . json_encode($check) . "). Check DB connection."
            );
        }
        if ($galleryCount < 3) {
            throw new RuntimeException("Import expected 3 gallery rows, found {$galleryCount}.");
        }
        if (!is_file($this->absolutePath($mainRelative))) {
            throw new RuntimeException("DB saved but main file missing on disk: {$mainRelative}");
        }

        return [
            'product_id' => $productId,
            'product_image' => $mainRelative,
            'gallery' => $galleryPaths,
            'dry_run' => false,
        ];
    }

    private function encodeSquareJpeg($src, int $target): ?string
    {
        $width = imagesx($src);
        $height = imagesy($src);
        $side = min($width, $height);
        $srcX = (int) (($width - $side) / 2);
        $srcY = (int) (($height - $side) / 2);

        $dst = imagecreatetruecolor($target, $target);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $target, $target, $side, $side);

        for ($quality = 85; $quality >= 40; $quality -= 5) {
            ob_start();
            imagejpeg($dst, null, $quality);
            $jpeg = ob_get_clean();
            if ($jpeg !== false && strlen($jpeg) <= self::MAX_BYTES) {
                imagedestroy($dst);
                return $jpeg;
            }
        }

        imagedestroy($dst);
        return null;
    }

    /** @return array<string|int, array{width?: mixed, height?: mixed, url: string}> */
    private function safeImageResize(string $fileUrl, string $destinationDir): array
    {
        try {
            return helper::ImageResize($fileUrl, $destinationDir);
        } catch (Throwable $e) {
            Log::warning('ImageResize fallback', ['file' => $fileUrl, 'error' => $e->getMessage()]);
            $size = @getimagesize($fileUrl);

            return [
                '100' => [
                    'width' => $size[0] ?? self::TARGET_SIZE,
                    'height' => $size[1] ?? self::TARGET_SIZE,
                    'url' => $fileUrl,
                ],
            ];
        }
    }

    private function deleteStoredImages(?string $mainPath, ?string $serializedImages): void
    {
        if ($mainPath) {
            $abs = $this->absolutePath($mainPath);
            if (is_file($abs)) {
                @unlink($abs);
            } elseif (is_file($mainPath)) {
                @unlink($mainPath);
            }
        }

        if (!$serializedImages) {
            return;
        }

        $images = @unserialize($serializedImages);
        if (!is_array($images)) {
            return;
        }

        foreach ($images as $item) {
            $url = is_array($item) ? ($item['url'] ?? null) : null;
            if (!$url) {
                continue;
            }
            $abs = $this->absolutePath($url);
            if (is_file($abs)) {
                @unlink($abs);
            } elseif (is_file($url)) {
                @unlink($url);
            }
        }
    }

    private function absolutePath(string $relative): string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        return rtrim($this->webRoot(), '/') . '/' . $relative;
    }

    /**
     * Default folder for manual image drop (FTP-friendly):
     * RDF/uploads/product-image-import/
     */
    public function defaultImportDirectory(): string
    {
        return $this->webRoot() . '/uploads/product-image-import';
    }

    /**
     * Resolve main + 3 gallery files for a ProductID from an import folder.
     *
     * Naming (any of these work):
     *   P2526-0000021.jpg | P2526-0000021_1.jpg | _2.jpg | _3.jpg
     *   P2526-0000021-main.jpg | -1.jpg | -2.jpg | -3.jpg
     *   P2526-0000021/main.jpg | 1.jpg | 2.jpg | 3.jpg
     *
     * @return array{main: string, gallery: array<int, string>}|null
     */
    public function resolveImportFiles(string $productId, string $importDir): ?array
    {
        $importDir = rtrim($importDir, '/\\');
        if (!is_dir($importDir)) {
            return null;
        }

        $exts = ['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'];
        $main = null;
        foreach (["{$productId}", "{$productId}-main", "{$productId}_main", "{$productId}-cover"] as $base) {
            foreach ($exts as $ext) {
                $path = "{$importDir}/{$base}.{$ext}";
                if (is_file($path)) {
                    $main = $path;
                    break 2;
                }
            }
        }

        $folder = "{$importDir}/{$productId}";
        if ($main === null && is_dir($folder)) {
            foreach (['main', 'cover', '1', '01'] as $base) {
                foreach ($exts as $ext) {
                    $path = "{$folder}/{$base}.{$ext}";
                    if (is_file($path)) {
                        $main = $path;
                        break 2;
                    }
                }
            }
        }

        if ($main === null) {
            return null;
        }

        $gallery = [];
        foreach ([1, 2, 3] as $i) {
            $found = null;
            foreach (["{$productId}_{$i}", "{$productId}-{$i}", "{$productId}_gallery{$i}", "{$productId}-gallery-{$i}"] as $base) {
                foreach ($exts as $ext) {
                    $path = "{$importDir}/{$base}.{$ext}";
                    if (is_file($path)) {
                        $found = $path;
                        break 2;
                    }
                }
            }
            if ($found === null && is_dir($folder)) {
                foreach ([(string) $i, sprintf('%02d', $i), "gallery{$i}", "gallery_{$i}"] as $base) {
                    foreach ($exts as $ext) {
                        $path = "{$folder}/{$base}.{$ext}";
                        if (is_file($path)) {
                            $found = $path;
                            break 2;
                        }
                    }
                }
            }
            if ($found !== null) {
                $gallery[] = $found;
            }
        }

        // Allow import with main only — duplicate main into missing gallery slots if needed
        while (count($gallery) < 3) {
            $gallery[] = $main;
        }

        return [
            'main' => $main,
            'gallery' => array_slice($gallery, 0, 3),
        ];
    }

    /**
     * Map manually prepared images onto a product (same DB/path rules as AI generate).
     *
     * @return array{product_id: string, product_image: string, gallery: array<int, string>, dry_run: bool, source_files: array<string, mixed>}
     */
    public function importAndMap(object $product, string $importDir, bool $dryRun = false): array
    {
        $files = $this->resolveImportFiles($product->ProductID, $importDir);
        if ($files === null) {
            throw new RuntimeException(
                "No images found for {$product->ProductID}. Expected {$product->ProductID}.jpg (+ _1/_2/_3) in {$importDir}"
            );
        }

        if ($dryRun) {
            return [
                'product_id' => $product->ProductID,
                'product_name' => $product->ProductName,
                'source_files' => $files,
                'dry_run' => true,
            ];
        }

        $generated = [
            'main' => [
                'prompt' => 'manual-import',
                'bytes' => $this->toSquareJpegUnder1Mb((string) file_get_contents($files['main'])),
            ],
        ];
        foreach ([1, 2, 3] as $i) {
            $path = $files['gallery'][$i - 1];
            $generated['gallery_' . $i] = [
                'prompt' => 'manual-import',
                'bytes' => $this->toSquareJpegUnder1Mb((string) file_get_contents($path)),
            ];
        }

        $saved = $this->persist($product->ProductID, $generated);
        $saved['product_name'] = $product->ProductName;
        $saved['source_files'] = $files;

        return $saved;
    }

    /**
     * Re-attach existing files on disk to DB when ProductImage was cleared but uploads remain.
     *
     * @return array{product_id: string, product_image: string, gallery: array<int, string>}
     */
    public function relinkFromDisk(string $productId, bool $dryRun = false): array
    {
        $existing = DB::table('tbl_products')->where('ProductID', $productId)->first();
        if (!$existing) {
            throw new RuntimeException("Product not found: {$productId}");
        }

        $relativeDir = "uploads/master/product/products/{$productId}/";
        $relativeGalleryDir = $relativeDir . 'gallery/';
        $absDir = $this->absolutePath($relativeDir);
        $absGalleryDir = $this->absolutePath($relativeGalleryDir);

        if (!is_dir($absDir)) {
            throw new RuntimeException("No product folder on disk: {$absDir}");
        }

        $mainAbs = $this->newestPrimaryImage($absDir);
        if ($mainAbs === null) {
            throw new RuntimeException("No main image found in {$absDir}");
        }
        $mainRelative = $relativeDir . basename($mainAbs);

        $galleryAbs = [];
        if (is_dir($absGalleryDir)) {
            $galleryAbs = $this->newestGalleryImages($absGalleryDir, 3);
        }
        while (count($galleryAbs) < 3) {
            $galleryAbs[] = $mainAbs; // pad with main if fewer than 3 gallery files
        }
        $galleryAbs = array_slice($galleryAbs, 0, 3);
        $galleryRelative = array_map(
            fn (string $abs) => $relativeGalleryDir . basename($abs),
            $galleryAbs
        );

        if ($dryRun) {
            return [
                'product_id' => $productId,
                'product_image' => $mainRelative,
                'gallery' => $galleryRelative,
                'dry_run' => true,
            ];
        }

        $previousCwd = getcwd();
        chdir($this->webRoot());
        try {
            DB::beginTransaction();
            $actor = (string) ($existing->UpdatedBy ?? $existing->CreatedBy ?? '');

            $resized = $this->safeImageResize($mainRelative, $relativeDir);
            $updated = DB::table('tbl_products')->where('ProductID', $productId)->update([
                'ProductImage' => $mainRelative,
                'Images' => serialize($resized),
                'UpdatedOn' => date('Y-m-d H:i:s'),
                'UpdatedBy' => $actor,
            ]);
            if ($updated < 1) {
                throw new RuntimeException("DB update affected 0 rows for {$productId}");
            }

            DB::table('tbl_products_gallery')->where('ProductID', $productId)->delete();
            $stamp = date('YmdHis');
            $savedGallery = [];
            foreach ($galleryRelative as $i => $gRelative) {
                // If we padded with main file that lives outside gallery/, copy into gallery path name already set
                $gAbs = $this->absolutePath($gRelative);
                if (!is_file($gAbs) && is_file($galleryAbs[$i])) {
                    if (!is_dir(dirname($gAbs))) {
                        mkdir(dirname($gAbs), 0777, true);
                    }
                    copy($galleryAbs[$i], $gAbs);
                }
                $gResized = $this->safeImageResize($gRelative, $relativeGalleryDir);
                $slno = DocNum::getDocNum(docTypes::ProductGallery->value, '', helper::getCurrentFY());
                if ($slno === '' || $slno === null) {
                    throw new RuntimeException('DocNum for Product-Gallery is empty. Check tbl_docnum.');
                }
                $ok = DB::table('tbl_products_gallery')->insert([
                    'SLNO' => $slno,
                    'ProductID' => $productId,
                    'ImgID' => 'RL' . helper::RandomString(12),
                    'gImage' => $gRelative,
                    'Images' => serialize($gResized),
                    'CreatedBy' => $actor,
                    'CreatedOn' => date('Y-m-d H:i:s'),
                ]);
                if (!$ok) {
                    throw new RuntimeException("Failed inserting gallery row for {$productId}");
                }
                DocNum::updateDocNum(docTypes::ProductGallery->value);
                $savedGallery[] = $gRelative;
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            if ($previousCwd) {
                chdir($previousCwd);
            }
        }

        $check = DB::table('tbl_products')->where('ProductID', $productId)->value('ProductImage');
        if (trim((string) $check) === '' || $check !== $mainRelative) {
            throw new RuntimeException('Relink failed: ProductImage still empty in DB after update.');
        }

        return [
            'product_id' => $productId,
            'product_image' => $mainRelative,
            'gallery' => $savedGallery,
            'dry_run' => false,
        ];
    }

    /** Newest non-resized main jpg/png in a product folder. */
    private function newestPrimaryImage(string $absDir): ?string
    {
        $candidates = [];
        foreach (glob($absDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [] as $path) {
            $base = basename($path);
            // Skip ImageResize variants: name_10.jpg / name_25.jpg etc.
            if (preg_match('/_(10|25|50|75|100)\.(jpe?g|png)$/i', $base)) {
                continue;
            }
            $candidates[] = $path;
        }
        if ($candidates === []) {
            return null;
        }
        usort($candidates, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return $candidates[0];
    }

    /**
     * Newest up to $limit non-resized gallery images.
     *
     * @return array<int, string>
     */
    private function newestGalleryImages(string $absGalleryDir, int $limit): array
    {
        $byStamp = [];
        foreach (glob($absGalleryDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [] as $path) {
            $base = basename($path);
            if (preg_match('/_(10|25|50|75|100)\.(jpe?g|png)$/i', $base)) {
                continue;
            }
            // Prefer files from the same batch (shared YmdHis in name)
            if (preg_match('/_(\d{14})_(\d+)\./', $base, $m)) {
                $byStamp[$m[1]][(int) $m[2]] = $path;
            } else {
                $byStamp['misc'][] = $path;
            }
        }
        if ($byStamp === []) {
            return [];
        }
        krsort($byStamp); // newest stamp first
        $first = reset($byStamp);
        if (!is_array($first)) {
            return [];
        }
        ksort($first);
        $paths = array_values($first);

        return array_slice($paths, 0, $limit);
    }
}
