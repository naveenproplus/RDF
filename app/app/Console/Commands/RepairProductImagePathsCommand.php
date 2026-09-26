<?php

namespace App\Console\Commands;

use App\helper\helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairProductImagePathsCommand extends Command
{
    protected $signature = 'products:repair-image-paths
                            {--dry-run : Show changes without writing DB}
                            {--limit=0 : Limit number of products to process}';

    protected $description = 'Fix ProductImage / gallery paths that point to missing files (use folder fallback or clear)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $query = DB::table('tbl_products')
            ->where('DFlag', 0)
            ->whereNotNull('ProductImage')
            ->where('ProductImage', '!=', '')
            ->orderBy('ProductID');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $fixed = 0;
        $cleared = 0;
        $ok = 0;

        foreach ($query->get(['ProductID', 'ProductImage']) as $row) {
            $rel = ltrim(str_replace('\\', '/', (string) $row->ProductImage), '/');
            $abs = helper::productImageWebRoot() . '/' . $rel;
            if (@is_file($abs)) {
                $ok++;
                continue;
            }

            $resolved = helper::resolveProductImageRelative($rel);
            if ($resolved && $resolved !== $rel && @is_file(helper::productImageWebRoot() . '/' . $resolved)) {
                $this->line("FIX {$row->ProductID}: {$rel} -> {$resolved}");
                if (!$dry) {
                    DB::table('tbl_products')->where('ProductID', $row->ProductID)->update([
                        'ProductImage' => $resolved,
                    ]);
                }
                $fixed++;
                continue;
            }

            $this->warn("CLEAR {$row->ProductID}: missing {$rel}");
            if (!$dry) {
                DB::table('tbl_products')->where('ProductID', $row->ProductID)->update([
                    'ProductImage' => '',
                ]);
            }
            $cleared++;
        }

        // Gallery rows
        $gFixed = 0;
        $gCleared = 0;
        if (DB::getSchemaBuilder()->hasTable('tbl_products_gallery')) {
            $galleries = DB::table('tbl_products_gallery')->whereNotNull('gImage')->where('gImage', '!=', '')->get(['SLNo', 'ProductID', 'gImage']);
            foreach ($galleries as $g) {
                $rel = ltrim(str_replace('\\', '/', (string) $g->gImage), '/');
                if (@is_file(helper::productImageWebRoot() . '/' . $rel)) {
                    continue;
                }
                // Try same folder alternate (gallery/ subdir)
                $dir = dirname($rel);
                $alt = null;
                $absDir = helper::productImageWebRoot() . '/' . $dir;
                if (@is_dir($absDir)) {
                    foreach (@scandir($absDir) ?: [] as $file) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        if (!preg_match('/\.(jpe?g|png|webp|gif)$/i', $file)) {
                            continue;
                        }
                        if (preg_match('/_(10|25|50|75)\./i', $file)) {
                            continue;
                        }
                        if (@is_file($absDir . '/' . $file)) {
                            $alt = $dir . '/' . $file;
                            break;
                        }
                    }
                }
                if ($alt) {
                    $this->line("FIX gallery {$g->SLNo}: {$rel} -> {$alt}");
                    if (!$dry) {
                        DB::table('tbl_products_gallery')->where('SLNo', $g->SLNo)->update(['gImage' => $alt]);
                    }
                    $gFixed++;
                } else {
                    $this->warn("DELETE gallery {$g->SLNo}: missing {$rel}");
                    if (!$dry) {
                        DB::table('tbl_products_gallery')->where('SLNo', $g->SLNo)->delete();
                    }
                    $gCleared++;
                }
            }
        }

        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . "products ok={$ok} fixed={$fixed} cleared={$cleared}; gallery fixed={$gFixed} removed={$gCleared}");

        return self::SUCCESS;
    }
}
