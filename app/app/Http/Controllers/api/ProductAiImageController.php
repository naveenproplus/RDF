<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\GeminiProductImageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductAiImageController extends Controller
{
    /**
     * List products with no image (for design team).
     * Open URL — no auth.
     * GET/POST /api/tools/missing-product-images
     * Query/body: limit=0 (0 = all), format=json|csv
     */
    public function missing(Request $req, GeminiProductImageService $service)
    {
        $limit = (int) $req->input('limit', 0);
        $format = strtolower((string) $req->input('format', 'json'));
        $products = $service->eligibleProducts($limit > 0 ? $limit : null);

        $rows = $products->map(function ($p) {
            $id = (string) $p->ProductID;

            return [
                'ProductID' => $id,
                'ProductName' => (string) $p->ProductName,
                'Category' => (string) ($p->PCName ?? ''),
                'SubCategory' => (string) ($p->PSCName ?? ''),
                'MainFile' => $id . '.jpg',
                'Gallery1' => $id . '_1.jpg',
                'Gallery2' => $id . '_2.jpg',
                'Gallery3' => $id . '_3.jpg',
            ];
        })->values();

        if ($format === 'csv') {
            return $this->csvDownload($rows->all(), 'missing-product-images.csv');
        }

        return response()->json([
            'status' => true,
            'message' => 'Products with no image',
            'count' => $rows->count(),
            'upload_folder' => 'uploads/product-image-import/',
            'naming_note' => 'Rename images using MainFile / Gallery1 / Gallery2 / Gallery3, upload into uploads/product-image-import/, then run: php artisan products:import-images',
            'data' => $rows,
        ]);
    }

    public function eligible(Request $req, GeminiProductImageService $service)
    {
        if (!$this->authorized($req)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $limit = (int) ($req->input('limit', 50));
        $products = $service->eligibleProducts($limit > 0 ? $limit : null);

        return response()->json([
            'status' => true,
            'message' => 'Eligible products',
            'count' => $products->count(),
            'data' => $products->map(function ($p) {
                $id = (string) $p->ProductID;

                return [
                    'ProductID' => $id,
                    'ProductName' => $p->ProductName,
                    'PCName' => $p->PCName,
                    'PSCName' => $p->PSCName,
                    'ProductImage' => $p->ProductImage,
                    'MainFile' => $id . '.jpg',
                    'Gallery1' => $id . '_1.jpg',
                    'Gallery2' => $id . '_2.jpg',
                    'Gallery3' => $id . '_3.jpg',
                ];
            })->values(),
        ]);
    }

    public function generate(Request $req, GeminiProductImageService $service)
    {
        if (!$this->authorized($req)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $provider = strtolower((string) config('app.AI_IMAGE_PROVIDER', 'openai'));
        $hasKey = $provider === 'gemini'
            ? (string) config('app.GEMINI_API_KEY') !== ''
            : (string) config('app.OPENAI_API_KEY') !== '';
        if (!$hasKey) {
            return response()->json([
                'status' => false,
                'message' => $provider === 'gemini' ? 'GEMINI_API_KEY not configured' : 'OPENAI_API_KEY not configured',
            ], 500);
        }

        $productId = trim((string) $req->input('ProductID', ''));
        $limit = (int) $req->input('limit', 1);
        $dryRun = filter_var($req->input('dry_run', false), FILTER_VALIDATE_BOOLEAN);

        if ($productId === '' && $limit < 1) {
            return response()->json(['status' => false, 'message' => 'Provide ProductID or limit >= 1'], 422);
        }

        if ($productId === '') {
            $limit = min(max($limit, 1), 20);
        }

        $products = $service->eligibleProducts($productId ? null : $limit, $productId ?: null);
        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No eligible products (need Active + empty ProductImage)',
            ], 404);
        }

        $results = [];
        $errors = [];

        foreach ($products as $product) {
            try {
                $results[] = $service->generateAndMap($product, $dryRun);
            } catch (Throwable $e) {
                $errors[] = [
                    'ProductID' => $product->ProductID,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => count($errors) === 0,
            'message' => count($errors) === 0 ? 'Images generated and mapped' : 'Completed with some failures',
            'success_count' => count($results),
            'error_count' => count($errors),
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    private function authorized(Request $req): bool
    {
        $expected = (string) (config('app.AI_IMAGE_TOOL_TOKEN') ?: config('app.GEMINI_IMAGE_TOOL_TOKEN'));
        if ($expected === '') {
            return false;
        }

        $token = (string) (
            $req->header('X-AI-Image-Token')
            ?: $req->input('tool_token', '')
            ?: $req->input('token', '')
        );

        return $token !== '' && hash_equals($expected, $token);
    }

    /**
     * @param array<int, array<string, string>> $rows
     */
    private function csvDownload(array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ProductID', 'ProductName', 'Category', 'SubCategory', 'MainFile', 'Gallery1', 'Gallery2', 'Gallery3']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['ProductID'],
                    $row['ProductName'],
                    $row['Category'],
                    $row['SubCategory'],
                    $row['MainFile'],
                    $row['Gallery1'],
                    $row['Gallery2'],
                    $row['Gallery3'],
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
