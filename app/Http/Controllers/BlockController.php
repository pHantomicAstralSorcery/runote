<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Block $block)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Block $block)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Block $block)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Block $block)
    {
        //
    }
    
    /**
     * Принимает изображение, сохраняет оригинал и генерирует превью.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        // 1. Валидация файла: обязательное изображение до 5 МБ, только JPEG, PNG, GIF или WebP :contentReference[oaicite:4]{index=4}
        $request->validate([
            'image' => 'required|image|max:5120|mimes:jpeg,png,gif,webp',
        ]);

        // 2. Генерация уникального имени и сохранение оригинала в storage/app/public/images :contentReference[oaicite:5]{index=5}
        $file     = $request->file('image');
        $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('public/images', $filename);

        // 3. Создание директории для миниатюр, если её нет
        Storage::makeDirectory('public/images/thumbs');

        // 4. Генерация миниатюры 300×200 и сохранение в storage/app/public/images/thumbs :contentReference[oaicite:6]{index=6}
        $thumbPath = storage_path('app/public/images/thumbs/' . $filename);
        Image::make($file)
             ->fit(300, 200)
             ->save($thumbPath);

        // 5. Возвращаем JSON с URL оригинала и превью (через симлинк public/storage) :contentReference[oaicite:7]{index=7}
        return response()->json([
            'url'       => Storage::url('images/' . $filename),
            'thumb_url' => Storage::url('images/thumbs/' . $filename),
        ]);
    }

    /**
     * Обновляет порядок блоков на странице.
     */
    public function reorder(Request $request, Page $page)
    {
        $order = $request->input('order', []);
        foreach ($order as $index => $blockId) {
            // Обновляем поле order в БД
            Block::where('id', $blockId)
                 ->where('page_id', $page->id)
                 ->update(['order' => $index]);
        }
        return response()->noContent();
    }
}
