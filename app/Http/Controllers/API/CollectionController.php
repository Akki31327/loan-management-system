<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionRequest;
use App\Models\Collection;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST COLLECTIONS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Collection::with(['loan', 'collector']);

        /*
        |--------------------------------------------------------------------------
        | PAYMENT MODE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->payment_mode) {

            $query->where('payment_mode', $request->payment_mode);
        }

        /*
        |--------------------------------------------------------------------------
        | DATE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->date) {

            $query->whereDate('collected_at', $request->date);
        }

        $collections = $query->latest()->paginate(10);

        return response()->json([

            'status' => true,

            'message' => 'Collection list fetched successfully',

            'data' => $collections

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD COLLECTION
    |--------------------------------------------------------------------------
    */

    public function store(StoreCollectionRequest $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | FIND LOAN
            |--------------------------------------------------------------------------
            */

            $loan = Loan::find($request->loan_id);

            if (!$loan) {

                return response()->json([

                    'status' => false,

                    'message' => 'Loan not found'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDATE PENDING AMOUNT
            |--------------------------------------------------------------------------
            */

            if ($request->amount_paid > $loan->pending_amount) {

                return response()->json([

                    'status' => false,

                    'message' => 'Amount exceeds pending amount'

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE COLLECTION ENTRY
            |--------------------------------------------------------------------------
            */

            $collection = Collection::create([

                'loan_id' => $loan->id,

                'collected_by' => auth()->id(),

                'amount_paid' => $request->amount_paid,

                'payment_mode' => $request->payment_mode,

                'location' => $request->location,

                'remarks' => $request->remarks,

                'collected_at' => now()
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE LOAN TOTALS
            |--------------------------------------------------------------------------
            */

            $loan->total_paid += $request->amount_paid;

            $loan->pending_amount -= $request->amount_paid;

            /*
            |--------------------------------------------------------------------------
            | CLOSE LOAN IF FULLY PAID
            |--------------------------------------------------------------------------
            */

            if ($loan->pending_amount <= 0) {

                $loan->status = 'closed';

                $loan->pending_amount = 0;
            }

            $loan->save();

            DB::commit();

            return response()->json([

                'status' => true,

                'message' => 'Collection added successfully',

                'data' => $collection

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,

                'message' => 'Something went wrong',

                'error' => $e->getMessage()

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | COLLECTION DETAILS
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $collection = Collection::with([
            'loan',
            'collector'
        ])->find($id);

        if (!$collection) {

            return response()->json([

                'status' => false,

                'message' => 'Collection not found'

            ], 404);
        }

        return response()->json([

            'status' => true,

            'data' => $collection

        ], 200);
    }
}