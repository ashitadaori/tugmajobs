<?php

namespace App\Traits;

use App\Models\AuditLog;

trait LogsAudit
{
    /**
     * Log when a model is created
     */
    public static function bootLogsAudit()
    {
        static::created(function ($model) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                AuditLog::logAction(
                    'created',
                    class_basename($model),
                    $model->id,
                    null,
                    $model->toArray()
                );
            }
        });

        static::updated(function ($model) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                AuditLog::logAction(
                    'updated',
                    class_basename($model),
                    $model->id,
                    $model->getOriginal(),
                    $model->getChanges()
                );
            }
        });

        static::deleted(function ($model) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                AuditLog::logAction(
                    'deleted',
                    class_basename($model),
                    $model->id,
                    $model->toArray(),
                    null
                );
            }
        });
    }
}
