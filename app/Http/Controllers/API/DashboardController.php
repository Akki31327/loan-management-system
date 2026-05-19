<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary()
    {
        $user = auth()->user();

        
        // LOAN DATA (VISIBLE TO ALL)
       

        $totalLoans = Loan::count();

        $activeLoans = Loan::where(
            'status',
            'active'
        )->count();

        $closedLoans = Loan::where(
            'status',
            'closed'
        )->count();

        $totalPendingAmount =
            Loan::sum('pending_amount');

        
        // COLLECTION QUERY
        $collectionQuery = Collection::query();

        // If agent -> only own collections
        if ($user->role === 'field_agent') {

            $collectionQuery->where(
                'collected_by',
                $user->id
            );
        }

        
        // COLLECTION STATS
        $totalCollectedToday =
            (clone $collectionQuery)
            ->whereDate(
                'created_at',
                today()
            )
            ->sum('amount_paid');

        $totalCollection =
            (clone $collectionQuery)
            ->sum('amount_paid');


        // PAYMENT MODE
        $cashCollection =
            (clone $collectionQuery)
            ->where('payment_mode', 'cash')
            ->sum('amount_paid');

        $upiCollection =
            (clone $collectionQuery)
            ->where('payment_mode', 'upi')
            ->sum('amount_paid');

        $cardCollection =
            (clone $collectionQuery)
            ->where('payment_mode', 'card')
            ->sum('amount_paid');

       
        // COLLECTION TREND
        $collectionTrends =
            (clone $collectionQuery)
            ->select(

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

        
        // RECENT COLLECTIONS
        $recentCollections =
            (clone $collectionQuery)
            ->with('loan')
            ->latest()
            ->take(5)
            ->get();

        
        // BEST COLLECTION TIME
        $bestHour =
            (clone $collectionQuery)
            ->select(

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

        
        return response()->json([

            'status' => true,

            'data' => [

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

                'best_collection_time' =>
                    $bestCollectionTime,
            ]

        ], 200);
    }
}