<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD SUMMARY
    |--------------------------------------------------------------------------
    */

    public function summary()
{
    /*
    |--------------------------------------------------------------------------
    | BASIC STATS
    |--------------------------------------------------------------------------
    */

    $totalLoans = Loan::count();

    $activeLoans = Loan::where(
        'status',
        'active'
    )->count();

    $closedLoans = Loan::where(
        'status',
        'closed'
    )->count();

    $totalCollectedToday =
    Collection::whereDate(
        'created_at',
        today()
    )->sum('amount_paid');

    $totalPendingAmount =
        Loan::sum('pending_amount');

    $totalCollection =
        Collection::sum('amount_paid');

    /*
    |--------------------------------------------------------------------------
    | PAYMENT MODE COLLECTION
    |--------------------------------------------------------------------------
    */

    $cashCollection =
        Collection::where(
            'payment_mode',
            'cash'
        )->sum('amount_paid');

    $upiCollection =
        Collection::where(
            'payment_mode',
            'upi'
        )->sum('amount_paid');

    $cardCollection =
        Collection::where(
            'payment_mode',
            'card'
        )->sum('amount_paid');

    /*
    |--------------------------------------------------------------------------
    | COLLECTION TREND
    |--------------------------------------------------------------------------
    */

    $collectionTrends =
        Collection::select(

            DB::raw(
                'DATE(collected_at) as date'
            ),

            DB::raw(
                'SUM(amount_paid) as amount'
            )

        )
        ->groupBy(
            DB::raw(
                'DATE(collected_at)'
            )
        )
        ->orderBy('date')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | RECENT COLLECTIONS
    |--------------------------------------------------------------------------
    */

    $recentCollections =
        Collection::with('loan')
        ->latest()
        ->take(5)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | BEST COLLECTION TIME
    |--------------------------------------------------------------------------
    */

    $bestHour =
        Collection::select(

            DB::raw(
                'HOUR(collected_at) as hour'
            ),

            DB::raw(
                'COUNT(*) as total'
            )

        )
        ->groupBy(
            DB::raw(
                'HOUR(collected_at)'
            )
        )
        ->orderByDesc('total')
        ->first();

    $bestCollectionTime =
        $bestHour
        ? date(
            'h A',
            strtotime(
                $bestHour->hour . ':00'
            )
          )
          . ' - '
          . date(
            'h A',
            strtotime(
                ($bestHour->hour + 2)
                . ':00'
            )
          )
        : 'No Data';

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'status' => true,

        'data' => [

            /*
            |--------------------------------------------------------------------------
            | BASIC STATS
            |--------------------------------------------------------------------------
            */

            'total_loans' =>
                $totalLoans,

            'active_loans' =>
                $activeLoans,

            'closed_loans' =>
                $closedLoans,

            'total_collected_today' =>
                $totalCollectedToday,

            'total_pending_amount' =>
                $totalPendingAmount,

            'total_collection' =>
                $totalCollection,

            /*
            |--------------------------------------------------------------------------
            | CHART DATA
            |--------------------------------------------------------------------------
            */

            'cash_collection' =>
                $cashCollection,

            'upi_collection' =>
                $upiCollection,

            'card_collection' =>
                $cardCollection,

            'collection_trends' =>
                $collectionTrends,

            'recent_collections' =>
                $recentCollections,

            /*
            |--------------------------------------------------------------------------
            | ADVANCED FEATURE
            |--------------------------------------------------------------------------
            */

            'best_collection_time' =>
                $bestCollectionTime,
        ]

    ], 200);
}

    /*
    |--------------------------------------------------------------------------
    | COLLECTION BY PAYMENT MODE
    |--------------------------------------------------------------------------
    */

    public function paymentModeCollection()
    {
        $data = Collection::select(
                'payment_mode',
                DB::raw('SUM(amount_paid) as total')
            )
            ->groupBy('payment_mode')
            ->get();

        return response()->json([

            'status' => true,

            'data' => $data

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | MONTHLY COLLECTION TREND
    |--------------------------------------------------------------------------
    */

    public function monthlyTrend()
    {
        $data = Collection::select(
                DB::raw('MONTH(collected_at) as month'),
                DB::raw('SUM(amount_paid) as total')
            )
            ->whereYear('collected_at', date('Y'))
            ->groupBy(DB::raw('MONTH(collected_at)'))
            ->orderBy('month')
            ->get();

        return response()->json([

            'status' => true,

            'data' => $data

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | AGENT PERFORMANCE
    |--------------------------------------------------------------------------
    */

    public function agentPerformance()
    {
        $agents = User::select(
                'users.id',
                'users.name',
                DB::raw('COUNT(collections.id) as total_collections'),
                DB::raw('SUM(collections.amount_paid) as total_amount')
            )
            ->leftJoin(
                'collections',
                'collections.collected_by',
                '=',
                'users.id'
            )
            ->where('users.role', 'field_agent')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_amount')
            ->get();

        return response()->json([

            'status' => true,

            'data' => $agents

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | BEST COLLECTION TIME PREDICTION
    |--------------------------------------------------------------------------
    */

    public function bestTimeSlot()
    {
        $collections = Collection::select(
                DB::raw('HOUR(collected_at) as hour'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('HOUR(collected_at)'))
            ->orderByDesc('total')
            ->first();

        if (!$collections) {

            return response()->json([

                'status' => false,

                'message' => 'No collection data available'

            ], 404);
        }

        $startHour = $collections->hour;

        $endHour = $startHour + 2;

        $slot = date('h A', strtotime($startHour . ':00'))
              . ' - '
              . date('h A', strtotime($endHour . ':00'));

        return response()->json([

            'status' => true,

            'best_time_slot' => $slot,

            'total_collections' => $collections->total

        ], 200);
    }
}