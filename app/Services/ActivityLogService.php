<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public static function log(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function created(Model $model): ActivityLog
    {
        return self::log('CREATE', $model, null, $model->toArray());
    }

    public static function updated(Model $model, array $oldValues): ActivityLog
    {
        $changed = array_intersect_key($model->toArray(), $model->getChanges());

        return self::log('UPDATE', $model, $oldValues, $changed);
    }

    public static function deleted(Model $model): ActivityLog
    {
        return self::log('DELETE', $model, $model->toArray());
    }

    public static function viewed(Model $model): ActivityLog
    {
        return self::log('VIEW', $model);
    }
}
