<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    protected function auditLog(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $before = null,
        ?array $after = null,
        array $properties = [],
    ): void {
        app(\App\Support\AuditLogger::class)->log(
            module: $module,
            action: $action,
            description: $description,
            subject: $subject,
            before: $before,
            after: $after,
            properties: $properties,
        );
    }
}
