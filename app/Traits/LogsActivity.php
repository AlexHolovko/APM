
<?php
/*
namespace App\Traits;

use App\Models\AuditLog;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('create');
        });

        static::updated(function ($model) {
            $model->logActivity('update');
        });

        static::deleted(function ($model) {
            $model->logActivity('delete');
        });
    }

    protected function logActivity($action)
    {
        if (auth()->check()) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => [
                    'model' => get_class($this),
                    'id' => $this->id,
                    'data' => $this->getAttributes()
                ]
            ]);
        }
    }
    */

