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

        $statiiPeco = StatiePeco::
            when($searchNumarStatie, function ($query, $searchNumarStatie) {
                $query->where('numar_statie', 'like', '%' . $searchNumarStatie . '%');
            }, function ($query) {
                $query->where('id', 0); // return nothing if there is no search by station number
            })
            ->simplePaginate(25);

        return view('statiiPeco.index', compact('statiiPeco', 'searchNumarStatie'));
    }

    public function excelImport(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'fisier_excel' => 'required|mimes:xls,xlsx'
        ]);

        // Load the Excel file
        $file = $request->file('fisier_excel');
        $spreadsheet = IOFactory::load($file->path());

        // Access the first sheet and read rows
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip the first row if it contains headers
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // Skip the first row
            }

            // Insert into database (adjust fields to your table structure)
            User::create([
                'name' => $row[0], // First column
                'email' => $row[1], // Second column
                'password' => bcrypt($row[2]), // Third column
            ]);
        }

        return redirect()->back()->with('success', 'Excel file imported successfully.');
    }
}
