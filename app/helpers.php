<?php

use App\Models\Field;

function renderWorkbookWithFields($content, $fields)
{
    foreach ($fields as $field) {
        $html = '';

        switch ($field->type) {
            case 'text':
                $html = '<input type="text" name="fields['.$field->id.']" class="form-control mb-2">';
                break;

            case 'select':
                $options = json_decode($field->options ?? '[]', true);
                $html = '<select name="fields['.$field->id.']" class="form-select mb-2">';
                foreach ($options as $opt) {
                    $html .= "<option value=\"$opt\">$opt</option>";
                }
                $html .= '</select>';
                break;

            case 'scale':
                $scale = json_decode($field->options ?? '{}', true);
                $min = $scale['min'] ?? 0;
                $max = $scale['max'] ?? 10;
                $html = '<input type="range" name="fields['.$field->id.']" min="'.$min.'" max="'.$max.'" class="form-range mb-2">';
                break;

            case 'file':
            case 'photo':
                $html = '<input type="file" name="fields['.$field->id.']" class="form-control mb-2">';
                break;

            default:
                $html = '<em>Неизвестный тип поля</em>';
        }

        $content = str_replace('{{ field:' . $field->id . ' }}', $html, $content);
    }

    return $content;
}
