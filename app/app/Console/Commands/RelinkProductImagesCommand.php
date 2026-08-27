<?php

namespace App\Console\Commands;

use App\Services\GeminiProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RelinkProductImagesCommand extends Command
{
    protected $signature = 'products:relink-images
                            {product : ProductID}
                            {--dry-run : Show which files would be linked; do not write DB}';

    protected $description = 'Re-link existing product image files on disk into DB when ProductImage was cleared';

    public function handle(GeminiProductImageService $service): int
    {
        $productId = (string) $this->argument('product');
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Relinking {$productId} from disk...");
        $this->info('DB: ' . DB::connection()->getDatabaseName());

        try {
            $result = $service->relinkFromDisk($productId, $dryRun);
            $this->info('  main: ' . $result['product_image']);
            foreach ($result['gallery'] as $i => $g) {
                $this->info('  gallery_' . ($i + 1) . ': ' . $g);
            }

            if (!$dryRun) {
                $dbPath = DB::table('tbl_products')->where('ProductID', $productId)->value('ProductImage');
                $galleryCount = DB::table('tbl_products_gallery')->where('ProductID', $productId)->count();
                $this->line('  DB ProductImage: ' . ($dbPath ?: '(empty)'));
                $this->line('  DB gallery rows: ' . $galleryCount);
                if (trim((string) $dbPath) === '' || $galleryCount < 1) {
                    throw new \RuntimeException('Relink reported success but DB still empty');
                }
                $this->newLine();
                $this->info('Done. Next: php artisan products:verify-image ' . $productId);
                $this->warn('Do NOT click Save in admin until the cover image is visible.');
            }
        } catch (Throwable $e) {
            $this->error('FAILED: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
