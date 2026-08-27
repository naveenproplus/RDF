<?php

namespace App\Console\Commands;

use App\helper\helper;
use App\Services\GeminiProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyProductImageCommand extends Command
{
    protected $signature = 'products:verify-image {product : ProductID}';

    protected $description = 'Diagnose why a product image may not show in admin (DB path vs file on disk vs URL)';

    public function handle(GeminiProductImageService $service): int
    {
        $productId = (string) $this->argument('product');
        $row = DB::table('tbl_products')->where('ProductID', $productId)->first();
        if (!$row) {
            $this->error("Product not found: {$productId}");
            return self::FAILURE;
        }

        $webRoot = $service->webRoot();
        $rel = trim((string) ($row->ProductImage ?? ''));
        $abs = helper::productImageAbsolutePath($rel !== '' ? $rel : null);
        $existsRel = $rel !== '' && file_exists($rel);
        $existsAbs = $rel !== '' && helper::productImageFileExists($rel);
        $checked = helper::checkProductImageExists($rel !== '' ? $rel : null);

        $this->info("Product: {$productId} | {$row->ProductName}");
        $this->line('DB database:             ' . DB::connection()->getDatabaseName());
        $this->line('webRoot (uploads parent): ' . $webRoot);
        $this->line('getcwd:                  ' . (getcwd() ?: '(empty)'));
        $this->line('DB ProductImage:         ' . ($rel !== '' ? $rel : '(empty)'));
        $this->line('Absolute path:           ' . ($abs ?? '(n/a)'));
        $this->line('file_exists(relative):   ' . ($existsRel ? 'YES' : 'NO'));
        $this->line('file_exists(absolute):   ' . ($existsAbs ? 'YES' : 'NO'));
        $this->line('Admin will display as:   ' . $checked);
        $this->line('Browser URL:             ' . rtrim((string) config('app.url'), '/') . '/' . ltrim((string) $checked, '/'));

        $productDir = $webRoot . '/uploads/master/product/products/' . $productId;
        $this->newLine();
        $this->info('Files on disk in: ' . $productDir);
        if (is_dir($productDir)) {
            $files = glob($productDir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
            $galleryFiles = glob($productDir . '/gallery/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
            if (count($files) === 0 && count($galleryFiles) === 0) {
                $this->warn('  (folder exists but no image files)');
            }
            foreach ($files as $f) {
                $this->line('  ' . basename($f) . ' (' . filesize($f) . ' bytes)');
            }
            foreach ($galleryFiles as $f) {
                $this->line('  gallery/' . basename($f) . ' (' . filesize($f) . ' bytes)');
            }
        } else {
            $this->warn('  (product folder does not exist)');
        }

        $gallery = DB::table('tbl_products_gallery')->where('ProductID', $productId)->get();
        $this->newLine();
        $this->info('Gallery rows: ' . $gallery->count());
        foreach ($gallery as $g) {
            $gRel = (string) ($g->gImage ?? '');
            $gOk = helper::productImageFileExists($gRel);
            $this->line(($gOk ? '  OK  ' : '  MISS') . " {$gRel}");
        }

        if ($rel === '') {
            $this->newLine();
            $this->error('ProductImage is EMPTY in DB — admin cannot show an image.');
            $this->line('Re-import (do NOT click Save in admin until verify shows a path):');
            $this->line("  php artisan products:import-images --product={$productId} --all");
            $this->line('Then run this verify command again.');
        } elseif (!$existsAbs) {
            $this->newLine();
            $this->error('DB has a path but file is missing on disk.');
        } elseif ($existsAbs) {
            $this->newLine();
            $this->info('DB + file look OK. Open Browser URL above; hard-refresh admin (Ctrl+F5).');
        }

        return self::SUCCESS;
    }
}
