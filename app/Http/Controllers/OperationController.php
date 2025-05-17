<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OperationUndone;
use App\Notifications\OperationRedone;

class OperationController extends Controller
{
    /**
     * Групповой откат (batch undo)
     *
     * @param  Request  $request
     */
    public function batchUndo(Request $request)
    {
        $ids = $request->input('operation_ids', []);

        // Проверяем права на каждую операцию
        $operations = Operation::whereIn('id', $ids)->get();
        foreach ($operations as $op) {
            $this->authorize('undo', $op);
        }

        DB::transaction(function () use ($operations) {
            foreach ($operations as $op) {
                $this->applyUndo($op);
                $op->delete();
            }
        });

        return redirect()->back()->with('status', 'Групповой откат выполнен');
    }

    /**
     * Откат одной операции (undo)
     */
    public function undo(int $id)
    {
        $op = Operation::findOrFail($id);
        $this->authorize('undo', $op);

        DB::transaction(function () use ($op) {
            $modelClass = $op->auditable_type;
            $model = $modelClass::withTrashed()->findOrFail($op->auditable_id);

            // Применяем откат
            $this->applyUndo($op, $model);

            // Удаляем запись операции
            $op->delete();

            // Логируем и уведомляем автора модели
            Log::info("Operation {$op->id} was undone by user ".auth()->id());
            if (method_exists($model, 'author')) {
                Notification::send($model->author, new OperationUndone($op));
            }
        });

        return redirect()->back()->with('status', 'Операция успешно отменена');
    }

    /**
     * Повтор действия (redo)
     */
    public function redo(int $id)
    {
        $op = Operation::withTrashed()->findOrFail($id);
        $this->authorize('redo', $op);

        DB::transaction(function () use ($op) {
            $modelClass = $op->auditable_type;
            $model = $modelClass::withTrashed()->findOrFail($op->auditable_id);

            // Применяем повтор
            if ($op->operation_type === 'create') {
                if (method_exists($model, 'restore')) {
                    $model->restore();
                }
                $model->fill($op->new_values)->save();
            } elseif ($op->operation_type === 'delete') {
                $model->delete();
            } else {
                $model->fill($op->new_values)->save();
            }

            // Восстанавливаем запись операции, если удалена
            if ($op->trashed()) {
                $op->restore();
            }

            // Логируем и уведомляем
            Log::info("Operation {$op->id} was redone by user ".auth()->id());
            if (method_exists($model, 'author')) {
                Notification::send($model->author, new OperationRedone($op));
            }
        });

        return redirect()->back()->with('status', 'Действие повторено успешно');
    }

    /**
     * Вспомогательный метод: применяет undo к одной операции и модели
     */
    protected function applyUndo(Operation $op, $model = null)
    {
        $modelClass = $op->auditable_type;
        $model = $model ?: $modelClass::withTrashed()->findOrFail($op->auditable_id);

        if ($op->operation_type === 'create') {
            $model->delete();
        } elseif ($op->operation_type === 'delete') {
            if (method_exists($model, 'restore')) {
                $model->restore();
            }
        } else {
            if ($op->old_values) {
                $model->fill($op->old_values);
                $model->save();
            }
        }
    }

    // Оставляем только необходимые методы CRUD
    public function index()
    {
        $this->authorize('viewAny', Operation::class);
        $operations = Operation::latest()->paginate(20);
        return view('operations.index', compact('operations'));
    }

    public function show(Operation $operation)
    {
        $this->authorize('view', $operation);
        return view('operations.show', compact('operation'));
    }

    public function destroy(Operation $operation)
    {
        $this->authorize('delete', $operation);
        $operation->delete();
        return redirect()->route('operations.index')->with('status', 'Запись удалена');
    }

    // Отключаем ненужные методы
    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function edit(Operation $operation) { abort(404); }
    public function update(Request $request, Operation $operation) { abort(404); }
}
