<?php

namespace App\Http\Controllers;

use App\Models\NamedLink;
use App\Models\Field;
use App\Models\Response;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function store(Request $request, $slug)
    {
        $request->validate([
            'fields.*' => 'nullable|file|mimes:jpg,png,pdf,docx|max:5120',
        ]);

        $link = NamedLink::where('slug', $slug)->firstOrFail();

        foreach ($request->input('fields', []) as $fieldId => $value) {
            $field = Field::findOrFail($fieldId);

            $resp = new Response();
            $resp->named_link_id = $link->id;
            $resp->field_id      = $fieldId;

            if (in_array($field->type, ['file', 'photo'])) {
                $path = $request->file("fields.$fieldId")
                                ->store('uploads', 'public');
                $resp->value = $path;
            } else {
                $resp->value = $value;
            }

            $resp->save();
        }

        return back()->with('success', 'Ответы сохранены');
    }
}
