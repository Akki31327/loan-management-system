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

    // LIST COLLECTIONS
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Collection::with([
            'loan',
            'collector'
        ]);

        
        // ROLE FILTER
        if ($user->role === 'field_agent') {

            $query->where(
                'collected_by',
                $user->id
            );
        }

        
        // PAYMENT MODE FILTER
        if ($request->payment_mode) {

            $query->where(
                'payment_mode',
                $request->payment_mode
            );
        }

      
        // DATE FILTER
        if ($request->date) {

            $query->whereDate(
                'collected_at',
                $request->date
            );
        }

        $collections =
            $query->latest()->paginate(10);

        return response()->json([

            'status' => true,

            'message' =>
                'Collection list fetched successfully',

            'data' =>
                $collections->items(),

            'pagination' => [

                'current_page' =>
                    $collections->currentPage(),

                'last_page' =>
                    $collections->lastPage(),

                'per_page' =>
                    $collections->perPage(),

                'total' =>
                    $collections->total(),
            ]

        ], 200);
    }

    
    // ADD COLLECTION
    public function store(StoreCollectionRequest $request)
    {
        DB::beginTransaction();

        try {

           
            // FIND LOAN
            $loan = Loan::find($request->loan_id);

            if (!$loan) {

                return response()->json([

                    'status' => false,

                    'message' => 'Loan not found'

                ], 404);
            }

            
            // CHECK CLOSED LOAN
            if ($loan->status === 'closed') {

                return response()->json([

                    'status' => false,

                    'message' => 'Loan already closed'

                ], 422);
            }

            
            // VALIDATE PENDING AMOUNT
            if ($request->amount_paid > $loan->pending_amount) {

                return response()->json([

                    'status' => false,

                    'message' => 'Amount exceeds pending amount'

                ], 422);
            }

            // CREATE COLLECTION
            $collection = Collection::create([

                'loan_id' =>
                    $loan->id,

                'collected_by' =>
                    auth()->id(),

                'amount_paid' =>
                    $request->amount_paid,

                'payment_mode' =>
                    $request->payment_mode,

                'location' =>
                    $request->location,

                'remarks' =>
                    $request->remarks,

                'collected_at' =>
                    now()
            ]);

            
            // UPDATE LOAN
            $loan->total_paid +=
                $request->amount_paid;

            $loan->pending_amount -=
                $request->amount_paid;

            
            // CLOSE LOAN
            if ($loan->pending_amount <= 0) {

                $loan->status = 'closed';

                $loan->pending_amount = 0;
            }

            $loan->save();

            DB::commit();

            return response()->json([

                'status' => true,

                'message' =>
                    'Collection added successfully',

                'data' =>
                    $collection

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,

                'message' =>
                    'Something went wrong',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }

    
    // COLLECTION DETAILS
    public function show($id)
    {
        $user = auth()->user();

        $query = Collection::with([
            'loan',
            'collector'
        ]);

        
        // ROLE FILTER
        if ($user->role === 'field_agent') {

            $query->where(
                'collected_by',
                $user->id
            );
        }

        $collection = $query->find($id);

        if (!$collection) {

            return response()->json([

                'status' => false,

                'message' =>
                    'Collection not found'

            ], 404);
        }

        return response()->json([

            'status' => true,

            'data' => $collection

        ], 200);
    }

    
    // UPDATE COLLECTION
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = auth()->user();

            $query = Collection::query();

            
            // ROLE FILTER
            

            if ($user->role === 'field_agent') {

                $query->where(
                    'collected_by',
                    $user->id
                );
            }

            $collection = $query->find($id);

            if (!$collection) {

                return response()->json([

                    'status' => false,

                    'message' =>
                        'Collection not found'

                ], 404);
            }

            $loan = Loan::find(
                $collection->loan_id
            );

            if (!$loan) {

                return response()->json([

                    'status' => false,

                    'message' =>
                        'Loan not found'

                ], 404);
            }

            
            // REVERT OLD AMOUNT
            $loan->total_paid -=
                $collection->amount_paid;

            $loan->pending_amount +=
                $collection->amount_paid;

            
            // VALIDATE NEW AMOUNT
            if ($request->amount_paid > $loan->pending_amount) {

                return response()->json([

                    'status' => false,

                    'message' =>
                        'Amount exceeds pending amount'

                ], 422);
            }

            
            // UPDATE COLLECTION
            $collection->update([

                'amount_paid' =>
                    $request->amount_paid,

                'payment_mode' =>
                    $request->payment_mode,

                'location' =>
                    $request->location,

                'remarks' =>
                    $request->remarks,
            ]);

         
            // APPLY NEW AMOUNT
            $loan->total_paid +=
                $request->amount_paid;

            $loan->pending_amount -=
                $request->amount_paid;

            // LOAN STATUS
            if ($loan->pending_amount <= 0) {

                $loan->status = 'closed';

                $loan->pending_amount = 0;

            } else {

                $loan->status = 'active';
            }

            $loan->save();

            DB::commit();

            return response()->json([

                'status' => true,

                'message' =>
                    'Collection updated successfully',

                'data' =>
                    $collection

            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,

                'message' =>
                    'Something went wrong',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }

   
//delect collection
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $user = auth()->user();

            $query = Collection::query();

            if ($user->role === 'field_agent') {

                $query->where(
                    'collected_by',
                    $user->id
                );
            }

            $collection = $query->find($id);

            if (!$collection) {

                return response()->json([

                    'status' => false,

                    'message' =>
                        'Collection not found'

                ], 404);
            }

            $loan = Loan::find(
                $collection->loan_id
            );

            if ($loan) {

                $loan->total_paid -=
                    $collection->amount_paid;

                $loan->pending_amount +=
                    $collection->amount_paid;

                $loan->status = 'active';

                $loan->save();
            }

            $collection->delete();

            DB::commit();

            return response()->json([

                'status' => true,

                'message' =>
                    'Collection deleted successfully'

            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,

                'message' =>
                    'Something went wrong',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }
}