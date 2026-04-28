<?php

namespace App\Observers;

use App\Models\SalesPage;
use App\Support\SalesPageMedia;

class SalesPageObserver
{
    public function deleting(SalesPage $salesPage): void
    {
        SalesPageMedia::deletePublicPaths(SalesPageMedia::publicPathsFor($salesPage));
    }

    public function updating(SalesPage $salesPage): void
    {
        $oldContent = $this->normalizeContent($salesPage->getOriginal('generated_content'));
        $newContent = $this->normalizeContent($salesPage->generated_content);

        $oldPaths = SalesPageMedia::publicPathsFor($salesPage, $oldContent);
        $newPaths = SalesPageMedia::publicPathsFor($salesPage, $newContent);
        $removedPaths = array_diff($oldPaths, $newPaths);

        SalesPageMedia::deletePublicPaths($removedPaths);
    }

    private function normalizeContent(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }

        if (is_string($content)) {
            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
