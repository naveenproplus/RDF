<?php

namespace App\Console\Commands;

use App\Services\GeminiProductImageService;
use Illuminate\Console\Command;
use Throwable;

class GenerateProductAiImagesCommand extends Command
{
    protected $signature = 'products:generate-ai-images
                            {--limit=20 : Max products to process}
                            {--product= : Process a single ProductID}
                            {--dry-run : Show prompts only; do not call AI or write files}
                            {--sleep=2 : Seconds to wait between products}';

    protected $description = 'Generate AI main + 3 gallery images for products with empty ProductImage and map them in DB (OpenAI or Gemini)';

    public function handle(GeminiProductImageService $service): int
    {
        if (!$this->option('dry-run') && !$this->hasAiKeyConfigured()) {
            $provider = strtolower((string) config('app.AI_IMAGE_PROVIDER', 'openai'));
            $this->error($provider === 'gemini'
                ? 'Set GEMINI_API_KEY in .env first.'
                : 'Set OPENAI_API_KEY in .env first (OpenAI platform API key, not ChatGPT Go).');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $productId = $this->option('product') ?: null;
        $dryRun = (bool) $this->option('dry-run');
        $sleep = max(0, (int) $this->option('sleep'));

        $products = $service->eligibleProducts($productId ? null : $limit, $productId);
        if ($products->isEmpty()) {
            $this->warn('No eligible products found (Active, DFlag=0, empty ProductImage).');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . 'Processing ' . $products->count() . ' product(s)...');
        $ok = 0;
        $fail = 0;

        foreach ($products as $product) {
            $this->line("→ {$product->ProductID} | {$product->ProductName}");
            try {
                $result = $service->generateAndMap($product, $dryRun);
                if ($dryRun) {
                    foreach ($result['prompts'] as $slot => $prompt) {
                        $this->line("  [{$slot}] " . mb_substr($prompt, 0, 140) . '...');
                    }
                } else {
                    $this->info("  main: {$result['product_image']}");
                    foreach ($result['gallery'] as $path) {
                        $this->info("  gallery: {$path}");
                    }
                }
                $ok++;
            } catch (Throwable $e) {
                $fail++;
                $this->error('  FAILED: ' . $e->getMessage());
            }

            if (!$dryRun && $sleep > 0) {
                sleep($sleep);
            }
        }

        $this->newLine();
        $this->info("Done. success={$ok} failed={$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function hasAiKeyConfigured(): bool
    {
        $provider = strtolower((string) config('app.AI_IMAGE_PROVIDER', 'openai'));

        return $provider === 'gemini'
            ? (string) config('app.GEMINI_API_KEY') !== ''
            : (string) config('app.OPENAI_API_KEY') !== '';
    }
}
