<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Valabilitati\ValabilitatiReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValabilitatiReportController extends Controller
{
    public function index(Request $request, ValabilitatiReportService $service)
    {
        $filters = $this->resolveFilters($request);
        $options = $service->filterOptions();
        $report = $service->buildReport($filters);

        return view('admin.valabilitati.report', [
            'filters' => $filters,
            'options' => $options,
            'report' => $report,
        ]);
    }

    public function export(Request $request, ValabilitatiReportService $service, string $format): StreamedResponse
    {
        $filters = $this->resolveFilters($request);

        return $service->export($filters, $format);
    }

    /**
     * @return array{driver: string|null, vehicle: string|null, date_from: string|null, date_to: string|null}
     */
    private function resolveFilters(Request $request): array
    {
        $driver = $request->input('driver');
        if (is_string($driver)) {
            $driver = trim($driver);
        }

        $vehicle = $request->input('vehicle');
        if (is_string($vehicle)) {
            $vehicle = trim($vehicle);
        }

        $dateFrom = $this->sanitizeDate($request->input('date_from'));
        $dateTo = $this->sanitizeDate($request->input('date_to'));

        if ($dateFrom === null && $dateTo === null) {
            $today = CarbonImmutable::now();
            $dateTo = $today->format('Y-m-d');
            $dateFrom = $today->subDays(30)->format('Y-m-d');
        }

        if ($dateFrom !== null && $dateTo !== null && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [
            'driver' => $driver !== '' ? $driver : null,
            'vehicle' => $vehicle !== '' ? $vehicle : null,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function sanitizeDate(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
