<?php

namespace App\Observers;

use App\Models\Block;
use Illuminate\Support\Facades\Storage;

class BlockObserver
{
    public function deleting(Block $block): void
    {
        if (($block->data['type'] ?? null) === 'image') {
            $filename = $block->data['filename'];
            Storage::disk('public')->delete("images/{$filename}");
            Storage::disk('public')->delete("images/thumbs/{$filename}");
        }
    }
}
