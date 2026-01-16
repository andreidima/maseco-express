<?php

namespace App\Http\Controllers;

use App\Models\KpiWeeklyMark;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcasaWeeklyMarkController extends Controller
{
    public function upsert(Request $request)
    {
        $evaluatorUserIds = [7, 12, 26];
        $ratedUserIds = [16, 27];

        abort_unless(in_array(auth()->id(), $evaluatorUserIds, true), 403);

        $validated = $request->validate([
            'week_start' => ['required', 'date'],
            'rated_user_id' => ['required', 'integer', 'exists:users,id'],
            'mark' => ['nullable', 'integer', 'min:0', 'max:3'],
        ]);

        $currentWeekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        if ($weekStart->greaterThan($currentWeekStart)) {
            $weekStart = $currentWeekStart;
        }

        $ratedUserId = (int) $validated['rated_user_id'];
        abort_unless(in_array($ratedUserId, $ratedUserIds, true), 403);

        $ratedByUserId = (int) auth()->id();
        $mark = array_key_exists('mark', $validated) ? $validated['mark'] : null;
        $weekStartDate = $weekStart->toDateString();

        if ($mark === null) {
            KpiWeeklyMark::query()
                ->where('week_start_date', $weekStartDate)
                ->where('rated_user_id', $ratedUserId)
                ->where('rated_by_user_id', $ratedByUserId)
                ->delete();
        } else {
            KpiWeeklyMark::query()->updateOrCreate(
                [
                    'week_start_date' => $weekStartDate,
                    'rated_user_id' => $ratedUserId,
                    'rated_by_user_id' => $ratedByUserId,
                ],
                [
                    'mark' => $mark,
                ]
            );
        }

        $average = KpiWeeklyMark::query()
            ->where('week_start_date', $weekStartDate)
            ->where('rated_user_id', $ratedUserId)
            ->whereIn('rated_by_user_id', $evaluatorUserIds)
            ->whereNotNull('mark')
            ->avg('mark');

        return response()->json([
            'success' => true,
            'week_start' => $weekStartDate,
            'rated_user_id' => $ratedUserId,
            'rated_by_user_id' => $ratedByUserId,
            'mark' => $mark,
            'average' => $average === null ? null : (float) $average,
            'average_formatted' => $average === null ? '-' : number_format((float) $average, 1, '.', ''),
        ]);
    }
}

