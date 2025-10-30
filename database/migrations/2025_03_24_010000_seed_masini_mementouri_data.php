<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NOTIFICATION_EMAIL = 'masecoexpres@gmail.com';

    public function up(): void
    {
        $documentDefinitions = $this->documentDefinitions();
        $timestamp = Carbon::now();

        foreach ($this->vehicles() as $vehicle) {
            $masinaId = DB::table('masini')
                ->where('numar_inmatriculare', $vehicle['numar'])
                ->value('id');

            if ($masinaId === null) {
                $masinaId = DB::table('masini')->insertGetId([
                    'numar_inmatriculare' => $vehicle['numar'],
                    'descriere' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            $memento = DB::table('masini_mementouri')
                ->where('masina_id', $masinaId)
                ->first();

            if ($memento === null) {
                DB::table('masini_mementouri')->insert([
                    'masina_id' => $masinaId,
                    'email_notificari' => self::NOTIFICATION_EMAIL,
                    'telefon_notificari' => null,
                    'observatii' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            } elseif ($memento->email_notificari !== self::NOTIFICATION_EMAIL) {
                DB::table('masini_mementouri')
                    ->where('id', $memento->id)
                    ->update([
                        'email_notificari' => self::NOTIFICATION_EMAIL,
                        'updated_at' => $timestamp,
                    ]);
            }

            foreach ($vehicle['documents'] as $label => $date) {
                if (!array_key_exists($label, $documentDefinitions)) {
                    continue;
                }

                $parsedDate = $this->normalizeDate($date);

                if ($parsedDate === null) {
                    continue;
                }

                $definition = $documentDefinitions[$label];
                $docQuery = DB::table('masini_documente')
                    ->where('masina_id', $masinaId)
                    ->where('document_type', $definition['document_type']);

                if ($definition['tara'] === null) {
                    $docQuery->whereNull('tara');
                } else {
                    $docQuery->where('tara', $definition['tara']);
                }

                $existingDocument = $docQuery->first();

                if ($existingDocument) {
                    if ($existingDocument->data_expirare !== $parsedDate) {
                        $docQuery->update([
                            'data_expirare' => $parsedDate,
                            'updated_at' => $timestamp,
                        ]);
                    }
                } else {
                    DB::table('masini_documente')->insert([
                        'masina_id' => $masinaId,
                        'document_type' => $definition['document_type'],
                        'tara' => $definition['tara'],
                        'data_expirare' => $parsedDate,
                        'email_notificare' => null,
                        'notificare_60_trimisa' => false,
                        'notificare_30_trimisa' => false,
                        'notificare_15_trimisa' => false,
                        'notificare_1_trimisa' => false,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // This migration seeds production data that should not be removed.
    }

    private function documentDefinitions(): array
    {
        return [
            'ITP' => ['document_type' => 'itp', 'tara' => null],
            'RCA' => ['document_type' => 'rca', 'tara' => null],
            'COPIE CONFORMA' => ['document_type' => 'copie_conforma', 'tara' => null],
            'ASIGURARE CMR' => ['document_type' => 'asigurare_cmr', 'tara' => null],
            'RO' => ['document_type' => 'vigneta', 'tara' => 'ro'],
            'HU' => ['document_type' => 'vigneta', 'tara' => 'hu'],
            'AT' => ['document_type' => 'vigneta', 'tara' => 'at'],
            'BRENNERO' => ['document_type' => 'vigneta', 'tara' => 'brennero'],
            'SK' => ['document_type' => 'vigneta', 'tara' => 'sk'],
            'CZ' => ['document_type' => 'vigneta', 'tara' => 'cz'],
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || strcasecmp($trimmed, 'nu') === 0) {
            return null;
        }

        return Carbon::createFromFormat('d.m.Y', $trimmed)->format('Y-m-d');
    }

    private function vehicles(): array
    {
        return [
            [
                'numar' => 'SV 16 RZX',
                'documents' => [
                    'COPIE CONFORMA' => '23.01.2026',
                ],
            ],
            [
                'numar' => 'SV 33 MAS',
                'documents' => [
                    'RCA' => '29.10.2025',
                ],
            ],
            [
                'numar' => 'SV 65 MAS',
                'documents' => [
                    'ITP' => '23.01.2026',
                    'RCA' => '03.11.2025',
                    'COPIE CONFORMA' => '28.01.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '30.01.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '27.04.2026',
                    'CZ' => '04.03.2026',
                ],
            ],
            [
                'numar' => 'SV 18 PBM',
                'documents' => [
                    'ITP' => '04.07.2026',
                    'RCA' => '05.06.2026',
                ],
            ],
            [
                'numar' => 'SV 17 JWM',
                'documents' => [
                    'ITP' => '14.02.2026',
                    'RCA' => '03.03.2026',
                    'COPIE CONFORMA' => '23.03.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '13.04.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '15.05.2026',
                    'CZ' => '20.05.2026',
                ],
            ],
            [
                'numar' => 'SV 18 KJB / SV 81 CPJ',
                'documents' => [],
            ],
            [
                'numar' => 'SV 21 MAS',
                'documents' => [
                    'ITP' => '17.01.2026',
                    'RCA' => '03.12.2025',
                    'COPIE CONFORMA' => '23.01.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '24.01.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '12.02.2026',
                    'SK' => '27.01.2026',
                    'CZ' => '27.01.2026',
                ],
            ],
            [
                'numar' => 'SV 15 MHV',
                'documents' => [
                    'ITP' => '27.12.2025',
                    'RCA' => '27.10.2026',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '21.12.2025',
                    'RO' => '03.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '12.05.2026',
                ],
            ],
            [
                'numar' => 'SV 01 MAS / SV 81 BPY',
                'documents' => [],
            ],
            [
                'numar' => 'SV 15 UHN',
                'documents' => [
                    'ITP' => '30.05.2026',
                    'RCA' => '20.02.2026',
                    'RO' => '31.01.2026',
                    'HU' => '31.01.2026',
                    'BRENNERO' => '21.05.2026',
                    'SK' => '04.03.2026',
                ],
            ],
            [
                'numar' => 'SV 15 XKL',
                'documents' => [
                    'ITP' => '13.01.2026',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '18.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '09.08.2026',
                    'CZ' => '12.11.2025',
                ],
            ],
            [
                'numar' => 'SV 16 GVO',
                'documents' => [
                    'ITP' => '30.05.2026',
                    'RCA' => '04.01.2026',
                    'COPIE CONFORMA' => '08.07.2026',
                    'ASIGURARE CMR' => '13.12.2025',
                    'RO' => '16.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '16.03.2026',
                    'SK' => '09.09.2026',
                    'CZ' => '27.08.2026',
                ],
            ],
            [
                'numar' => 'SV 17 RTO',
                'documents' => [
                    'ITP' => '30.05.2026',
                    'ASIGURARE CMR' => '12.05.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                ],
            ],
            [
                'numar' => 'SV 16 XPU',
                'documents' => [
                    'ITP' => '06.05.2026',
                    'RCA' => '01.07.2026',
                    'COPIE CONFORMA' => '11.07.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '14.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '17.12.2025',
                    'SK' => '22.09.2026',
                ],
            ],
            [
                'numar' => 'SV 18 GGT',
                'documents' => [
                    'ITP' => '08.10.2026',
                    'RCA' => '28.10.2025',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '07.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '22.05.2026',
                    'SK' => '21.05.2026',
                    'CZ' => '04.03.2026',
                ],
            ],
            [
                'numar' => 'SV 17 VMP',
                'documents' => [
                    'COPIE CONFORMA' => '16.10.2026',
                    'RO' => '21.10.2025',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '07.01.2026',
                    'CZ' => '09.01.2026',
                ],
            ],
            [
                'numar' => 'SV 18 DKW',
                'documents' => [
                    'ITP' => '22.02.2026',
                    'RCA' => '27.02.2026',
                    'COPIE CONFORMA' => '24.03.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '20.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '16.04.2026',
                    'SK' => '26.03.2026',
                    'CZ' => '26.03.2026',
                ],
            ],
            [
                'numar' => 'SV 18 EUF',
                'documents' => [
                    'ITP' => '26.03.2026',
                    'RCA' => '07.04.2026',
                    'COPIE CONFORMA' => '10.04.2026',
                    'ASIGURARE CMR' => '20.05.2026',
                    'RO' => '19.04.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '12.05.2026',
                    'CZ' => '20.05.2026',
                ],
            ],
            [
                'numar' => 'SV 18 HIV',
                'documents' => [
                    'ITP' => '15.05.2026',
                    'RCA' => '27.05.2026',
                    'COPIE CONFORMA' => '03.06.2026',
                    'ASIGURARE CMR' => '04.06.2026',
                    'RO' => '29.05.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '13.06.2026',
                    'CZ' => '12.06.2026',
                ],
            ],
            [
                'numar' => 'B 112 SFI',
                'documents' => [
                    'ITP' => '22.10.2026',
                    'RCA' => '28.10.2025',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '03.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '17.03.2026',
                    'SK' => '03.06.2026',
                    'CZ' => '04.03.2026',
                ],
            ],
            [
                'numar' => 'SV 16 YKF',
                'documents' => [
                    'ITP' => '30.05.2026',
                    'RCA' => '20.07.2026',
                    'COPIE CONFORMA' => '04.08.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '19.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '28.03.2026',
                    'SK' => '10.03.2026',
                ],
            ],
            [
                'numar' => 'SV 17 CNU',
                'documents' => [
                    'RCA' => '19.09.2026',
                    'COPIE CONFORMA' => '10.11.2025',
                    'RO' => '17.03.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '31.01.2026',
                    'SK' => '05.08.2026',
                    'CZ' => '18.07.2026',
                ],
            ],
            [
                'numar' => 'SV 15 MXC',
                'documents' => [
                    'ITP' => '27.12.2025',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '19.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '11.03.2026',
                    'SK' => '11.02.2026',
                    'CZ' => '13.08.2026',
                ],
            ],
            [
                'numar' => 'SV 16 PLP',
                'documents' => [
                    'ITP' => '12.10.2026',
                    'RCA' => '18.11.2025',
                    'COPIE CONFORMA' => '25.01.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '25.01.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'SK' => '02.07.2026',
                    'CZ' => '17.04.2026',
                ],
            ],
            [
                'numar' => 'SV 16 SXL',
                'documents' => [
                    'ITP' => '03.06.2026',
                    'RCA' => '24.02.2026',
                    'COPIE CONFORMA' => '24.02.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '11.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '18.05.2026',
                    'SK' => '16.04.2026',
                    'CZ' => '16.04.2026',
                ],
            ],
            [
                'numar' => 'SV 15 GKX',
                'documents' => [
                    'ITP' => '25.07.2026',
                    'RCA' => '27.04.2026',
                    'COPIE CONFORMA' => '05.05.2026',
                    'ASIGURARE CMR' => '31.12.2025',
                    'RO' => '20.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '06.05.2026',
                    'CZ' => '04.03.2026',
                ],
            ],
            [
                'numar' => 'SV 15 KUM',
                'documents' => [
                    'ITP' => '15.08.2026',
                    'RCA' => '24.02.2026',
                    'COPIE CONFORMA' => '05.05.2026',
                    'RO' => '16.02.2026',
                    'HU' => '31.01.2026',
                    'AT' => '31.01.2026',
                    'BRENNERO' => '20.01.2026',
                    'CZ' => '24.08.2026',
                ],
            ],
            [
                'numar' => 'SV 17 WZM',
                'documents' => [],
            ],
            [
                'numar' => 'SV 17 WZJ',
                'documents' => [],
            ],
            [
                'numar' => 'SV 15 LPB',
                'documents' => [],
            ],
            [
                'numar' => 'SV 16 VZR',
                'documents' => [],
            ],
            [
                'numar' => 'SV 35 MAS',
                'documents' => [],
            ],
            [
                'numar' => 'SV 17 KIR',
                'documents' => [],
            ],
            [
                'numar' => 'SV 17 HGN',
                'documents' => [],
            ],
        ];
    }
};
