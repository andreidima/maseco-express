<?php

namespace App\Services\Valabilitati;

use App\Models\Masini\Masina;
use App\Models\ValabilitateCursa;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValabilitatiReportService
{
    /**
     * @return array{drivers: array<int, string>, vehicles: array<int|string, string>}
     */
    public function filterOptions(): array
    {
        $drivers = Masina::query()
            ->selectRaw('DISTINCT TRIM(descriere) AS driver')
            ->whereNotNull('descriere')
            ->whereRaw("TRIM(descriere) <> ''")
            ->orderBy('driver')
            ->pluck('driver')
            ->all();

        $vehicles = Masina::query()
            ->orderBy('numar_inmatriculare')
            ->pluck('numar_inmatriculare', 'id')
            ->map(fn (string $value) => trim($value))
            ->all();

        return [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ];
    }

    /**
     * @param  array{driver?: string|null, vehicle?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     * @return array{
     *     summary: array<string, mixed>,
     *     drivers: Collection<int, array<string, mixed>>,
     *     vehicles: Collection<int, array<string, mixed>>,
     *     dates: Collection<int, array<string, mixed>>,
     *     charts: array<string, array<string, array<int, string>|array<int, int>>>
     * }
     */
    public function buildReport(array $filters): array
    {
        $baseQuery = $this->applyFilters($filters);

        $totalTrips = (clone $baseQuery)->count();

        $uniqueDriverCount = (clone $baseQuery)
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(TRIM(m.descriere), ''), '__missing')) AS aggregate")
            ->value('aggregate') ?? 0;

        $uniqueVehicleCount = (clone $baseQuery)
            ->selectRaw('COUNT(DISTINCT m.id) AS aggregate')
            ->value('aggregate') ?? 0;

        $drivers = $this->driversBreakdown(clone $baseQuery);
        $vehicles = $this->vehiclesBreakdown(clone $baseQuery);
        $dates = $this->datesBreakdown(clone $baseQuery);

        return [
            'summary' => [
                'total_trips' => (int) $totalTrips,
                'unique_drivers' => (int) $uniqueDriverCount,
                'unique_vehicles' => (int) $uniqueVehicleCount,
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ],
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'dates' => $dates,
            'charts' => [
                'drivers' => [
                    'labels' => $drivers->pluck('driver_name')->all(),
                    'data' => $drivers->pluck('trip_count')->all(),
                ],
                'vehicles' => [
                    'labels' => $vehicles->pluck('vehicle_label')->all(),
                    'data' => $vehicles->pluck('trip_count')->all(),
                ],
                'dates' => [
                    'labels' => $dates->pluck('trip_date')->all(),
                    'data' => $dates->pluck('trip_count')->all(),
                ],
            ],
        ];
    }

    /**
     * @param  array{driver?: string|null, vehicle?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     */
    public function export(array $filters, string $format = 'csv'): StreamedResponse
    {
        $format = strtolower($format);

        if (! in_array($format, ['csv', 'xlsx'], true)) {
            $format = 'csv';
        }

        $rows = $this->detailedRows($filters);

        $headers = [
            'ID',
            'Referință',
            'Șofer',
            'Mașină',
            'Plecare la',
            'Sosire la',
            'Ora (locală)',
            'Localitate plecare',
            'Localitate sosire',
            'Țara descărcare',
            'Km bord',
            'Ultima cursă',
            'Observații',
        ];

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->fromArray($headers, null, 'A1');

        $worksheet->fromArray($rows->map(fn (array $row) => array_values($row))->all(), null, 'A2', true);

        $timestamp = CarbonImmutable::now()->format('Ymd_His');

        if ($format === 'xlsx') {
            $writer = new XlsxWriter($spreadsheet);
            $filename = "valabilitati_curse_{$timestamp}.xlsx";
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } else {
            $writer = new CsvWriter($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setSheetIndex(0);
            $filename = "valabilitati_curse_{$timestamp}.csv";
            $contentType = 'text/csv';
        }

        return response()->streamDownload(static function () use ($writer): void {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * @param  array{driver?: string|null, vehicle?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     */
    private function applyFilters(array $filters): Builder
    {
        $query = ValabilitateCursa::query()
            ->from('valabilitati_curse as vc')
            ->join('valabilitati as v', 'v.id', '=', 'vc.valabilitate_id')
            ->leftJoin('masini as m', 'm.id', '=', 'v.masina_id');

        $driverFilter = $filters['driver'] ?? null;
        if ($driverFilter === '__missing') {
            $query->where(function (Builder $subQuery): void {
                $subQuery->whereNull('m.descriere')->orWhereRaw("TRIM(m.descriere) = ''");
            });
        } elseif (is_string($driverFilter) && $driverFilter !== '') {
            $query->whereRaw('TRIM(m.descriere) = ?', [$driverFilter]);
        }

        $vehicleFilter = $filters['vehicle'] ?? null;
        if ($vehicleFilter === '__missing') {
            $query->whereNull('m.id');
        } elseif (is_string($vehicleFilter) && $vehicleFilter !== '') {
            $query->where('m.id', (int) $vehicleFilter);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('vc.plecare_la', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('vc.plecare_la', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function driversBreakdown(Builder $query): Collection
    {
        return $query
            ->selectRaw("COALESCE(NULLIF(TRIM(m.descriere), ''), 'Fără șofer asociat') AS driver_name")
            ->selectRaw('COUNT(*) AS trip_count')
            ->selectRaw('COUNT(DISTINCT m.id) AS vehicle_count')
            ->selectRaw('MIN(vc.plecare_la) AS first_trip_at')
            ->selectRaw('MAX(vc.plecare_la) AS last_trip_at')
            ->groupBy('driver_name')
            ->orderByDesc('trip_count')
            ->get()
            ->map(fn ($row): array => [
                'driver_name' => (string) $row->driver_name,
                'trip_count' => (int) $row->trip_count,
                'vehicle_count' => (int) $row->vehicle_count,
                'first_trip_at' => $this->formatDateTime($row->first_trip_at ?? null),
                'last_trip_at' => $this->formatDateTime($row->last_trip_at ?? null),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function vehiclesBreakdown(Builder $query): Collection
    {
        return $query
            ->selectRaw("COALESCE(NULLIF(TRIM(m.numar_inmatriculare), ''), 'Fără mașină asociată') AS vehicle_label")
            ->selectRaw("COALESCE(NULLIF(TRIM(m.descriere), ''), 'Fără șofer asociat') AS driver_placeholder")
            ->selectRaw('COUNT(*) AS trip_count')
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(TRIM(m.descriere), ''), '__missing')) AS driver_count")
            ->selectRaw('MIN(vc.plecare_la) AS first_trip_at')
            ->selectRaw('MAX(vc.plecare_la) AS last_trip_at')
            ->groupBy('vehicle_label')
            ->orderByDesc('trip_count')
            ->get()
            ->map(fn ($row): array => [
                'vehicle_label' => (string) $row->vehicle_label,
                'trip_count' => (int) $row->trip_count,
                'driver_count' => (int) $row->driver_count,
                'first_trip_at' => $this->formatDateTime($row->first_trip_at ?? null),
                'last_trip_at' => $this->formatDateTime($row->last_trip_at ?? null),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function datesBreakdown(Builder $query): Collection
    {
        return $query
            ->selectRaw("COALESCE(DATE(vc.plecare_la), 'Fără dată') AS trip_date")
            ->selectRaw('COUNT(*) AS trip_count')
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(TRIM(m.descriere), ''), '__missing')) AS driver_count")
            ->selectRaw('COUNT(DISTINCT m.id) AS vehicle_count')
            ->groupBy('trip_date')
            ->orderBy('trip_date')
            ->get()
            ->map(fn ($row): array => [
                'trip_date' => (string) $row->trip_date,
                'trip_count' => (int) $row->trip_count,
                'driver_count' => (int) $row->driver_count,
                'vehicle_count' => (int) $row->vehicle_count,
            ]);
    }

    /**
     * @param  array{driver?: string|null, vehicle?: string|null, date_from?: string|null, date_to?: string|null}  $filters
     * @return Collection<int, array<string, string|int|null>>
     */
    private function detailedRows(array $filters): Collection
    {
        $query = $this->applyFilters($filters)
            ->select([
                'vc.id',
                'v.referinta as reference',
                'vc.localitate_plecare',
                'vc.localitate_sosire',
                'vc.descarcare_tara',
                'vc.plecare_la',
                'vc.sosire_la',
                'vc.ora',
                'vc.km_bord',
                'vc.observatii',
                'vc.ultima_cursa',
                'm.numar_inmatriculare',
                'm.descriere as driver_name',
            ])
            ->orderBy('vc.plecare_la')
            ->orderBy('vc.created_at');

        return $query->get()->map(function ($row): array {
            $driverName = trim((string) ($row->driver_name ?? ''));
            $vehicleLabel = trim((string) ($row->numar_inmatriculare ?? ''));

            return [
                'id' => (int) $row->id,
                'reference' => $row->reference ?? '',
                'driver' => $driverName !== '' ? $driverName : 'Fără șofer asociat',
                'vehicle' => $vehicleLabel !== '' ? $vehicleLabel : 'Fără mașină asociată',
                'plecare_la' => $this->formatDateTime($row->plecare_la ?? null),
                'sosire_la' => $this->formatDateTime($row->sosire_la ?? null),
                'ora' => $this->formatTime($row->ora ?? null),
                'localitate_plecare' => $row->localitate_plecare ?? '',
                'localitate_sosire' => $row->localitate_sosire ?? '',
                'descarcare_tara' => $row->descarcare_tara ?? '',
                'km_bord' => $row->km_bord !== null ? (int) $row->km_bord : null,
                'ultima_cursa' => $row->ultima_cursa ? 'Da' : 'Nu',
                'observatii' => $row->observatii ?? '',
            ];
        });
    }

    private function formatDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function formatTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
