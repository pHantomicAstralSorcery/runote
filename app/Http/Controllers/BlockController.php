<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class BlockController extends Controller
{
    /**
     * Сохранение нового блока.
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
            'type'    => $data['type'],
            'data'    => $data['data'] ?? null,
        ]);

        return response()->json($block);
    }

    /**
     * Обновление существующего блока.
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
     * Удаление блока.
     */
    public function destroy(Block $block)
    {
        $block->delete();
        return response()->noContent();
    }

public function uploadImage(Request $request)
{
    $request->validate([
        'image' => 'required|image|max:5120|mimes:jpeg,png,gif,webp',
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
        ->cover(300, 200)
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
     * Изменение порядка блоков на странице.
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
