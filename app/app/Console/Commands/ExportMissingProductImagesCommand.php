<?php

namespace App\Console\Commands;

use App\Services\GeminiProductImageService;
use Illuminate\Console\Command;

class ExportMissingProductImagesCommand extends Command
{
    protected $signature = 'products:export-missing-images
                            {--path= : Output CSV path (default: storage/app/missing-product-images.csv)}
                            {--limit=0 : Limit rows (0 = all)}';

    protected $description = 'Export ProductID list that need images (for manual generation checklist)';

    public function handle(GeminiProductImageService $service): int
    {
        $limit = (int) $this->option('limit');
        $products = $service->eligibleProducts($limit > 0 ? $limit : null);

        $path = $this->option('path') ?: storage_path('app/missing-product-images.csv');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fh = fopen($path, 'w');
        fputcsv($fh, ['ProductID', 'ProductName', 'Category', 'SubCategory', 'MainFile', 'Gallery1', 'Gallery2', 'Gallery3']);

        foreach ($products as $p) {
            fputcsv($fh, [
                $p->ProductID,
                $p->ProductName,
                $p->PCName ?? '',
                $p->PSCName ?? '',
                $p->ProductID . '.jpg',
                $p->ProductID . '_1.jpg',
                $p->ProductID . '_2.jpg',
                $p->ProductID . '_3.jpg',
            ]);
        }
        fclose($fh);

        $this->info('Exported ' . $products->count() . ' products to:');
        $this->line($path);
        $this->newLine();
        $this->line('Next:');
        $this->line('1) Generate images manually (ChatGPT Go / Claude / camera)');
        $this->line('2) Rename using MainFile / Gallery columns');
        $this->line('3) Upload into uploads/product-image-import/');
        $this->line('4) Run: php artisan products:import-images');

        return self::SUCCESS;
    }
}
