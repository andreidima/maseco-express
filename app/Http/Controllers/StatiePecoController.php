<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StatiePeco;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            // dd($totalCount);
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

            // Skip the first row if it contains headers
            foreach ($rows as $index => $row) {
                // if ($index === 0) {
                //     continue; // Skip the first row
                // }

                // Insert into database (adjust fields to your table structure)
                StatiePeco::create([
                    'numar_statie' => $row[0], // First column
                    'nume' => $row[1], // Second column
                    'strada' => $row[2],
                    'cod_postal' => $row[3],
                    'localitate' => $row[4],
                    'coordonate' => $row[5],
                ]);
            }

            return back()->with('success', 'Stațiile peco au fost importate cu success!');

        } catch (\Exception $e) {
            // Log::error('Excel Import Error: ' . $e->getMessage());
            // return back()->with('error', 'There was an error importing the Excel file.' . $e->getMessage());
            return back()->with('eroare', 'There was an error importing the Excel file.');
        }
    }
}
