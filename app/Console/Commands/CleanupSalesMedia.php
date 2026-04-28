<?php

namespace App\Console\Commands;

use App\Models\SalesPage;
use App\Support\SalesPageMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupSalesMedia extends Command
{
    protected $signature = 'media:cleanup {--dry-run : Report orphan files without deleting them}';

    protected $description = 'Remove public sales media files that are not referenced by any sales page.';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $referencedPaths = [];

        SalesPage::query()
            ->cursor()
            ->each(function (SalesPage $salesPage) use (&$referencedPaths): void {
                foreach (SalesPageMedia::publicPathsFor($salesPage) as $path) {
                    $referencedPaths[$path] = true;
                }
            });

        $deleted = 0;

        foreach ($disk->files('sales-media') as $path) {
            if (isset($referencedPaths[$path])) {
                continue;
            }

            $deleted++;

            if (! $this->option('dry-run')) {
                $disk->delete($path);
            }
        }

        $action = $this->option('dry-run') ? 'Found' : 'Removed';
        $this->info("{$action} {$deleted} orphan sales media file(s).");

        return self::SUCCESS;
    }
}
