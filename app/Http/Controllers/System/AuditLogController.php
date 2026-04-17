<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $auditLogs = $this->filteredQuery($request)
            ->with('user.roles')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.audit-logs.index', [
            'title' => 'Audit Log',
            'auditLogs' => $auditLogs,
            'filters' => $request->only(['q', 'module', 'action', 'user_id', 'date_from', 'date_to']),
            'users' => User::query()->with('roles')->orderBy('name')->get(),
            'modules' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'AUDIT-LOG-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Waktu',
                'User',
                'Username',
                'Modul',
                'Aksi',
                'Aktivitas',
                'Target',
                'Tipe Target',
                'IP',
                'URL',
                'Sebelum',
                'Sesudah',
                'Properti',
            ]);

            $this->filteredQuery($request)
                ->with('user')
                ->latest('created_at')
                ->chunk(200, function ($logs) use ($handle): void {
                    foreach ($logs as $auditLog) {
                        fputcsv($handle, [
                            optional($auditLog->created_at)->format('Y-m-d H:i:s'),
                            $auditLog->user?->name ?? 'Sistem / Guest',
                            $auditLog->user?->username ?? '-',
                            $auditLog->module,
                            $auditLog->action,
                            $auditLog->description,
                            $auditLog->subject_label,
                            $auditLog->subject_type ? class_basename($auditLog->subject_type) : null,
                            $auditLog->ip_address,
                            $auditLog->url,
                            $this->stringifyPayload($auditLog->before),
                            $this->stringifyPayload($auditLog->after),
                            $this->stringifyPayload($auditLog->properties),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function filteredQuery(Request $request): Builder
    {
        return AuditLog::query()
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));

                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('description', 'like', "%{$keyword}%")
                        ->orWhere('subject_label', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($keyword): void {
                            $userQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('username', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('module'), fn (Builder $query): Builder => $query->where('module', $request->string('module')))
            ->when($request->filled('action'), fn (Builder $query): Builder => $query->where('action', $request->string('action')))
            ->when($request->filled('user_id'), fn (Builder $query): Builder => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $request->string('date_to')));
    }

    protected function stringifyPayload(mixed $payload): string
    {
        if (empty($payload)) {
            return '';
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }
}
