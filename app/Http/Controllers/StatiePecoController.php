<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StatiePeco;
use App\Models\StatiePecoIstoric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class StatiePecoController extends Controller
{
    public function index(Request $request)
    {
        $searchNumarStatie = $request->searchNumarStatie;
        $searchNume = $request->searchNume;

        $statiiPeco = StatiePeco::
            when($searchNumarStatie, function ($query, $searchNumarStatie) {
                $query->where('numar_statie', $searchNumarStatie );
            // }
            // , function ($query) {
            //     $query->where('id', 0); // return nothing if there is no search by station number
            })
            ->when($searchNume, function ($query, $searchNume) {
                $query->where('nume', $searchNume );
            })
            ->orderBy('nume');

        $totalCount = $statiiPeco->count();

        if ($request->action === "massDelete") {
            // Check if the user has the required permissions for mass deletion
            if (! $request->user()?->hasPermission('comenzi')) {
                abort(403, 'Unauthorized action.');
            }

            // Fetch the records as a Collection
            $statiiPecoRecords = $statiiPeco->get();

            // Prepare the data for the history table
            $historyData = $statiiPecoRecords->map(function ($statiePeco) {
                $data = $statiePeco->makeHidden(['created_at', 'updated_at'])->toArray();
                $data['operare_user_id'] = auth()->user()->id ?? null;
                $data['operare_descriere'] = 'Stergere';
                return $data;
            });

            // Chunk the data and insert in batches
            $historyData->chunk(1000)->each(function ($chunk) {
                StatiePecoIstoric::insert($chunk->toArray());
            });

            // Now delete the records
            $statiiPeco->delete();

            return back()->with('status', 'Au fost șterse ' . $totalCount . ' stații peco cu succes!');
        }

        $statiiPeco = $statiiPeco->simplePaginate(25);

        return view('statiiPeco.index', compact('statiiPeco', 'searchNumarStatie', 'searchNume', 'totalCount'));
    }

    public function excelImport(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'fisier_excel' => 'required|mimes:xls,xlsx'
        ]);

        // Load the Excel file
        $file = $request->file('fisier_excel');

        try {
            $spreadsheet = IOFactory::load($file->path());

            // Access the first sheet and read rows
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip the header row
            $header = array_shift($rows);


            $dataToInsert = [];
            $historyData = [];
            $userId = auth()->id(); // Retrieve authenticated user ID once


            // Fetch existing 'numar_statie' values from the database
            $existingNumarStaties = StatiePeco::pluck('numar_statie')->toArray();

            foreach ($rows as $index => $row) {
                if (!isset($row[0])) continue; // Skip empty rows

                $numarStatie = $row[0];

                // Skip if 'numar_statie' already exists in the database
                if (in_array($numarStatie, $existingNumarStaties)) {
                    continue;
                }

                $record = [
                    'numar_statie' => $numarStatie,
                    'nume' => $row[1],
                    'strada' => $row[2],
                    'cod_postal' => $row[3],
                    'localitate' => $row[4],
                    'coordonate' => $row[5],
                ];

                $dataToInsert[] = $record;

                // Prepare history data
                $historyData[] = array_merge($record, [
                    'operare_user_id' => $userId,
                    'operare_descriere' => 'Adaugare',
                ]);
            }

            // Insert data in chunks
            if (!empty($dataToInsert)) {
                DB::transaction(function () use ($dataToInsert, $historyData) {
                    // Insert into main table
                    foreach (array_chunk($dataToInsert, 1000) as $chunk) {
                        StatiePeco::insert($chunk);
                    }

                    // Insert into history table
                    foreach (array_chunk($historyData, 1000) as $chunk) {
                        StatiePecoIstoric::insert($chunk);
                    }
                });
            }

            return back()->with('success', 'Stațiile peco au fost importate cu success!');

        } catch (\Exception $e) {
            // Log::error('Excel Import Error: ' . $e->getMessage());
            // return back()->with('error', 'There was an error importing the Excel file.' . $e->getMessage());
            return back()->with('eroare', 'There was an error importing the Excel file.');
        }
    }
}
