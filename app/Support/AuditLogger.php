<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    public function log(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $before = null,
        ?array $after = null,
        array $properties = [],
    ): void {
        $request = request();

        AuditLog::query()->create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subject ? $this->resolveSubjectLabel($subject) : null,
            'description' => $description,
            'before' => $this->sanitize($before),
            'after' => $this->sanitize($after),
            'properties' => $this->sanitize($properties),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
            'created_at' => now(),
        ]);
    }

    protected function resolveSubjectLabel(Model $subject): string
    {
        $attributes = $subject->getAttributes();

        foreach (['name', 'unit_name', 'invoice_number', 'booking_code', 'code', 'label', 'guest_name', 'username', 'email'] as $key) {
            if (! empty($attributes[$key])) {
                return (string) $attributes[$key];
            }
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }

    protected function sanitize(mixed $payload): mixed
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof Model) {
            return $this->sanitize($payload->getAttributes());
        }

        if (! is_array($payload)) {
            return $payload;
        }

        $payload = Arr::except($payload, [
            'password',
            'remember_token',
            'current_password',
            'password_confirmation',
        ]);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sanitize($value);
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $payload[$key] = $value->format('Y-m-d H:i:s');
                continue;
            }

            if (is_string($value) && Str::contains(Str::lower($key), 'password')) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }
}
