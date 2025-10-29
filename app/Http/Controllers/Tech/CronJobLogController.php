<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\CronJobLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CronJobLogController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = [
            CronJobLog::STATUS_STARTED,
            CronJobLog::STATUS_COMPLETED,
            CronJobLog::STATUS_FAILED,
        ];

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'job_key' => ['nullable', 'string'],
            'status' => ['nullable', 'in:' . implode(',', $statuses)],
        ]);

        $filters = [
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'job_key' => $validated['job_key'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $query = CronJobLog::query();

        if ($filters['start_date']) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($filters['end_date']) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        if ($filters['job_key']) {
            $query->where('job_key', $filters['job_key']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $logs = $query
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $jobKeys = CronJobLog::query()
            ->select('job_key')
            ->distinct()
            ->orderBy('job_key')
            ->pluck('job_key');

        $aggregated = CronJobLog::query()
            ->select('job_key')
            ->selectRaw('MAX(created_at) as last_run_at')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_successes", [CronJobLog::STATUS_COMPLETED])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_failures", [CronJobLog::STATUS_FAILED])
            ->groupBy('job_key')
            ->orderBy('job_key')
            ->get();

        $latestLogIds = CronJobLog::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('job_key')
            ->pluck('id');

        $latestLogs = CronJobLog::query()
            ->whereIn('id', $latestLogIds)
            ->get()
            ->keyBy('job_key');

        $stats = $aggregated->map(function ($row) use ($latestLogs) {
            $latest = $latestLogs->get($row->job_key);

            return [
                'job_key' => $row->job_key,
                'last_run_at' => $row->last_run_at ? Carbon::parse($row->last_run_at) : null,
                'total_successes' => (int) $row->total_successes,
                'total_failures' => (int) $row->total_failures,
                'last_status' => $latest?->status,
                'last_message' => $latest?->message,
            ];
        })->values();

        $availableStatuses = [
            CronJobLog::STATUS_STARTED => 'Pornit',
            CronJobLog::STATUS_COMPLETED => 'Finalizat',
            CronJobLog::STATUS_FAILED => 'Eșuat',
        ];

        $filtersActive = collect($filters)->filter(function ($value) {
            return filled($value);
        })->isNotEmpty();

        return view('tech.cron-logs.index', [
            'logs' => $logs,
            'jobKeys' => $jobKeys,
            'availableStatuses' => $availableStatuses,
            'filters' => $filters,
            'filtersActive' => $filtersActive,
            'stats' => $stats,
        ]);
    }

    public function show(CronJobLog $cronJobLog): View
    {
        return view('tech.cron-logs.show', [
            'log' => $cronJobLog,
        ]);
    }
}
