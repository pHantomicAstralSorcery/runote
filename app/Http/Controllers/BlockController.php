<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Для Str::random() и Str::uuid()
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image; // Убедитесь, что Intervention Image установлен

class BlockController extends Controller
{
    /**
     * Store a newly created block.
     * Сохраняет новый блок на странице.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'type'    => 'required|string',
            'data'    => 'nullable|array',
        ]);

        $block = Block::create([
            'page_id' => $data['page_id'],
            'uuid'    => Str::uuid(), // Генерируем UUID для нового блока
            'type'    => $data['type'],
            'data'    => $data['data'] ?? null,
        ]);

        return response()->json($block);
    }

    /**
     * Update the specified block.
     * Обновляет существующий блок.
     */
    public function update(Request $request, Block $block)
    {
        $data = $request->validate([
            'data' => 'required|array',
        ]);

        $block->update(['data' => $data['data']]);
        return response()->json($block);
    }

    /**
     * Remove the specified block.
     * Удаляет блок.
     */
    public function destroy(Block $block)
    {
        $block->delete();
        return response()->noContent();
    }

    /**
     * Uploads an image to storage and returns its URLs.
     * Загружает изображение и возвращает его URL'ы.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120|mimes:jpeg,png,gif,webp', // Макс 5МБ
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = time().'_'.Str::random(6).'.'.$extension;

        // 1) Сохраняем оригинал
        Storage::disk('public')->putFileAs('images', $file, $filename);

        // 2) Убеждаемся, что папка для миниатюр есть
        Storage::disk('public')->makeDirectory('images/thumbs');

        // 3) Генерируем миниатюру
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        Image::read($file)
            ->cover(300, 200) // Обрезает и подгоняет изображение под 300x200
            ->save($tempPath);

        // 4) Загружаем миниатюру в Storage
        Storage::disk('public')->putFileAs('images/thumbs', new \Illuminate\Http\File($tempPath), $filename);

        // 5) Удаляем временный файл
        unlink($tempPath);

        // 6) Возвращаем URL’ы
        return response()->json([
            'url' => Storage::url("images/{$filename}"),
            'thumb_url' => Storage::url("images/thumbs/{$filename}"),
        ]);
    }

    /**
     * Reorder blocks on a page.
     * Изменяет порядок блоков на странице.
     */
    public function reorder(Request $request, Page $page)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $blockId) {
            Block::where('id', $blockId)
                 ->where('page_id', $page->id)
                 ->update(['order' => $index]);
        }
        return response()->noContent();
    }
}
