<?php

namespace App\Observers;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditableObserver
{
    public function saving(Model $model)
    {
        $model->oldSnapshot = $model->getOriginal();
    }

    public function saved(Model $model)
    {
        $new = $model->getAttributes();
        $old = $model->oldSnapshot ?? [];

        $oldValues = Arr::except(array_diff_assoc($old, $new), ['updated_at']);
        $newValues = Arr::except(array_diff_assoc($new, $old), ['updated_at']);

        Operation::create([
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'operation_type' => $model->wasRecentlyCreated ? 'create' : 'update',
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'user_id'        => auth()->id(),
        ]);
    }
}
