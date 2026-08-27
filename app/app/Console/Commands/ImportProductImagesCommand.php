<?php

namespace App\Console\Commands;

use App\Services\GeminiProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportProductImagesCommand extends Command
{
    protected $signature = 'products:import-images
                            {--dir= : Folder with manually generated images (default: ../uploads/product-image-import)}
                            {--limit=50 : Max products to process}
                            {--product= : Import a single ProductID}
                            {--dry-run : Show matched files only; do not write DB/files}
                            {--all : Also allow products that already have an image (overwrite)}';

    protected $description = 'Map manually generated product images (1 main + 3 gallery) into admin DB from a folder';

    public function handle(GeminiProductImageService $service): int
    {
        $dir = $this->option('dir') ?: $service->defaultImportDirectory();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
            $this->warn("Import folder created: {$dir}");
            $this->line('Upload images there, then run this command again.');
            $this->newLine();
            $this->line('Naming example:');
            $this->line('  P2526-0000021.jpg');
            $this->line('  P2526-0000021_1.jpg');
            $this->line('  P2526-0000021_2.jpg');
            $this->line('  P2526-0000021_3.jpg');
            return self::SUCCESS;
        }

        $productId = $this->option('product') ?: null;
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $onlyMissing = !$this->option('all');

        $products = $onlyMissing
            ? $service->eligibleProducts($productId ? null : $limit, $productId)
            : $this->loadProducts($productId, $limit);

        if ($products->isEmpty()) {
            $this->warn('No products to process.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Import dir: {$dir}");
        $this->info('DB: ' . DB::connection()->getDatabaseName());
        $this->info('Processing ' . $products->count() . ' product(s)...');

        $ok = 0;
        $skip = 0;
        $fail = 0;

        foreach ($products as $product) {
            $this->line("→ {$product->ProductID} | {$product->ProductName}");
            $matched = $service->resolveImportFiles($product->ProductID, $dir);
            if ($matched === null) {
                $skip++;
                $this->warn('  SKIP: no image files found for this ProductID');
                continue;
            }

            try {
                $result = $service->importAndMap($product, $dir, $dryRun);
                if ($dryRun) {
                    $this->info('  main: ' . $result['source_files']['main']);
                    foreach ($result['source_files']['gallery'] as $i => $g) {
                        $this->info('  gallery_' . ($i + 1) . ': ' . $g);
                    }
                } else {
                    $this->info("  mapped main: {$result['product_image']}");
                    foreach ($result['gallery'] as $path) {
                        $this->info("  mapped gallery: {$path}");
                    }
                    $abs = \App\helper\helper::productImageAbsolutePath($result['product_image']);
                    $onDisk = \App\helper\helper::productImageFileExists($result['product_image']);
                    $this->line('  disk: ' . ($onDisk ? 'OK' : 'MISSING') . ' → ' . $abs);
                    $dbPath = DB::table('tbl_products')
                        ->where('ProductID', $product->ProductID)
                        ->value('ProductImage');
                    $this->line('  DB ProductImage: ' . ($dbPath ?: '(empty)'));
                    if (trim((string) $dbPath) === '') {
                        throw new \RuntimeException('Files mapped but DB ProductImage is still empty');
                    }
                }
                $ok++;
            } catch (Throwable $e) {
                $fail++;
                $this->error('  FAILED: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. success={$ok} skipped={$skip} failed={$fail}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function loadProducts(?string $productId, int $limit)
    {
        $query = DB::table('tbl_products as P')
            ->leftJoin('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
            ->leftJoin('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
            ->where('P.DFlag', 0)
            ->where('P.ActiveStatus', 'Active')
            ->select('P.ProductID', 'P.ProductName', 'P.ProductImage', 'PC.PCName', 'PSC.PSCName')
            ->orderBy('P.ProductID');

        if ($productId) {
            $query->where('P.ProductID', $productId);
        }

        $rows = $query->get();
        if (!$productId && $limit > 0) {
            $rows = $rows->take($limit)->values();
        }

        return $rows;
    }
}
