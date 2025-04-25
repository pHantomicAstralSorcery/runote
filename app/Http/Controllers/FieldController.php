<?php

namespace App\Http\Controllers;

use App\Models\Workbook;
use App\Models\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function index(Workbook $workbook)
    {
        $fields = $workbook->fields;
        return view('workbooks.fields.index', compact('workbook', 'fields'));
    }

    public function create(Workbook $workbook)
    {
        return view('workbooks.fields.create', compact('workbook'));
    }

    public function store(Request $request, Workbook $workbook)
    {
$data = $request->validate([
  'label'            => 'required|string',
  'type'             => 'required|in:text,select,scale,file,photo',
  'options'          => 'nullable|json',
  'validation_rules' => 'nullable|json',
  'key'              => 'required|string|unique:fields,key,'.$field->id ?? 'NULL',
  'correct_answer'   => 'nullable|string',
]);


        $workbook->fields()->create($data);

        return redirect()->route('workbooks.fields.index', $workbook)
                         ->with('success', 'Поле добавлено');
    }

    public function edit(Workbook $workbook, Field $field)
    {
        return view('workbooks.fields.edit', compact('workbook', 'field'));
    }

    public function update(Request $request, Workbook $workbook, Field $field)
    {
$data = $request->validate([
  'label'            => 'required|string',
  'type'             => 'required|in:text,select,scale,file,photo',
  'options'          => 'nullable|json',
  'validation_rules' => 'nullable|json',
  'key'              => 'required|string|unique:fields,key,'.$field->id ?? 'NULL',
  'correct_answer'   => 'nullable|string',
]);


        $field->update($data);

        return redirect()->route('workbooks.fields.index', $workbook)
                         ->with('success', 'Поле обновлено');
    }

    public function destroy(Workbook $workbook, Field $field)
    {
        $field->delete();
        return redirect()->route('workbooks.fields.index', $workbook)
                         ->with('success', 'Поле удалено');
    }
}
