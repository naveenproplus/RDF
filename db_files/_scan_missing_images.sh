#!/bin/bash
cd ~/domains/app.royaldryfruits.biz/public_html/app || exit 1
php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$root = dirname(base_path());
echo "base=".base_path().PHP_EOL;
echo "webRoot=$root".PHP_EOL;
echo "open_basedir=".ini_get('open_basedir').PHP_EOL;
echo "docroot=".($_SERVER['DOCUMENT_ROOT'] ?? '').PHP_EOL;

$missing = 0; $ok = 0; $sample = [];
$rows = DB::table('tbl_products')->where('DFlag',0)->whereNotNull('ProductImage')->where('ProductImage','!=','')->get(['ProductID','ProductImage']);
foreach ($rows as $r) {
    $rel = ltrim(str_replace('\\','/',$r->ProductImage),'/');
    $abs = $root.'/'.$rel;
    $exists = @is_file($abs);
    if ($exists) { $ok++; }
    else {
        $missing++;
        if (count($sample) < 15) {
            $dir = dirname($abs);
            $alts = is_dir($dir) ? array_values(array_filter(scandir($dir) ?: [], fn($f) => !in_array($f,['.','..']) && is_file($dir.'/'.$f) && preg_match('/\.(jpe?g|png|webp|gif)$/i',$f))) : [];
            $sample[] = [
                'id' => $r->ProductID,
                'db' => $rel,
                'dir_exists' => is_dir($dir),
                'alts' => array_slice($alts, 0, 3),
            ];
        }
    }
}
echo "ok=$ok missing=$missing total=".$rows->count().PHP_EOL;
echo json_encode($sample, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
PHP
