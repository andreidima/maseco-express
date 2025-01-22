<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KeyPerformanceIndicator;
use App\Models\KeyPerformanceIndicatorHistory;
use App\Models\Comanda;
use App\Models\User;

class KeyPerformanceIndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchMonth = $request->input('searchMonth', now()->month); // Default to current month
        $searchYear = $request->input('searchYear', now()->year);   // Default to current year

        $usersIDsForThisReport = [6, 7, 8, 12, 16, 17, 21, 23];

        // Query
        $usersWithKPIAndComanda = User::select(
                'users.id as user_id',
                'users.name as user_name',
                DB::raw("IFNULL(kpi.id, 0) as kpi_id"),
                DB::raw("IFNULL(kpi.observatii, '') as kpi_observatii"),
                DB::raw("IFNULL(kpi.data, NULL) as kpi_data"),
                DB::raw("SUM(CASE WHEN comenzi.data_creare >= '$searchYear-$searchMonth-01' AND comenzi.data_creare < DATE_ADD('$searchYear-$searchMonth-01', INTERVAL 1 MONTH) AND client_valoare_contract - transportator_valoare_contract > 0 THEN 1 ELSE 0 END) as greater_than_zero"),
                DB::raw("SUM(CASE WHEN comenzi.data_creare >= '$searchYear-$searchMonth-01' AND comenzi.data_creare < DATE_ADD('$searchYear-$searchMonth-01', INTERVAL 1 MONTH) AND client_valoare_contract - transportator_valoare_contract < 0 THEN 1 ELSE 0 END) as less_than_zero"),
                DB::raw("SUM(CASE WHEN comenzi.data_creare >= '$searchYear-$searchMonth-01' AND comenzi.data_creare < DATE_ADD('$searchYear-$searchMonth-01', INTERVAL 1 MONTH) AND client_valoare_contract - transportator_valoare_contract = 0 THEN 1 ELSE 0 END) as equal_to_zero")
            )
            ->leftJoin('key_performance_indicators as kpi', function ($join) use ($searchMonth, $searchYear) {
                $join->on('users.id', '=', 'kpi.user_id')
                    ->whereRaw("MONTH(kpi.data) = ?", [$searchMonth])
                    ->whereRaw("YEAR(kpi.data) = ?", [$searchYear]);
            })
            ->leftJoin('comenzi', 'users.id', '=', 'comenzi.user_id')
            ->whereIn('users.id', $usersIDsForThisReport)
            ->groupBy('users.id', 'users.name', 'kpi.id', 'kpi.observatii', 'kpi.data')
            ->orderBy('users.name')
        ->simplePaginate(25);

        return view('keyPerformanceIndicators.index', compact('usersWithKPIAndComanda', 'searchMonth', 'searchYear'));
    }

    public function updateObservatii(Request $request)
    {
        $request->validate([
            'kpi_id' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'observatii' => 'nullable|string|max:5000',
        ]);

        $kpi = KeyPerformanceIndicator::where('user_id', $request->user_id)
            ->whereMonth('data', $request->month)
            ->whereYear('data', $request->year)
            ->first();

        if (!$kpi) {
            $kpi = KeyPerformanceIndicator::create([
                'user_id' => $request->user_id,
                'data' => sprintf('%04d-%02d-01', $request->year, $request->month),
                'observatii' => $request->observatii,
            ]);

            // Log creation in the history table
            KeyPerformanceIndicatorHistory::create([
                'kpi_id' => $kpi->id,
                'user_id' => $request->user_id, // The user for whom the KPI belongs
                'performed_by_user_id' => auth()->id(), // The user making the change
                'observatii' => $request->observatii,
                'data' => $kpi->data,
                'action' => 'create',
            ]);
        } else {
            // Update the existing KPI
            $kpi->update(['observatii' => $request->observatii]);

            // Save the old value for historical reference
            KeyPerformanceIndicatorHistory::create([
                'kpi_id' => $kpi->id,
                'user_id' => $kpi->user_id, // The user for whom the KPI belongs
                'performed_by_user_id' => auth()->id(), // The user making the change
                'observatii' => $kpi->observatii,
                'data' => $kpi->data,
                'action' => 'update',
            ]);

        }

        return response()->json(['success' => true, 'message' => 'Observatii updated successfully.']);
    }
}
