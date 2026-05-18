<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST LOANS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Loan::query();

        /*
        |--------------------------------------------------------------------------
        | SEARCH FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where('loan_no', 'LIKE', '%' . $request->search . '%')

                  ->orWhere('customer_name', 'LIKE', '%' . $request->search . '%')

                  ->orWhere('mobile', 'LIKE', '%' . $request->search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->status) {

            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $loans = $query->latest()->paginate(10);

        return response()->json([

            'status' => true,

            'message' => 'Loan list fetched successfully',

            'data' => $loans

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE LOAN
    |--------------------------------------------------------------------------
    */

    public function store(StoreLoanRequest $request)
    {
        $loan = Loan::create([

            'loan_no' => $request->loan_no,

            'customer_name' => $request->customer_name,

            'mobile' => $request->mobile,

            'address' => $request->address,

            'loan_amount' => $request->loan_amount,

            'emi_amount' => $request->emi_amount,

            'total_paid' => 0,

            'pending_amount' => $request->loan_amount,

            'status' => 'active',

            'created_by' => auth()->id()
        ]);

        return response()->json([

            'status' => true,

            'message' => 'Loan created successfully',

            'data' => $loan

        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | LOAN DETAILS
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $loan = Loan::find($id);

        if (!$loan) {

            return response()->json([

                'status' => false,

                'message' => 'Loan not found'

            ], 404);
        }

        return response()->json([

            'status' => true,

            'data' => $loan

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LOAN
    |--------------------------------------------------------------------------
    */

    public function update(UpdateLoanRequest $request, $id)
    {
        $loan = Loan::find($id);

        if (!$loan) {

            return response()->json([

                'status' => false,

                'message' => 'Loan not found'

            ], 404);
        }

        $loan->update([

            'loan_no' => $request->loan_no,

            'customer_name' => $request->customer_name,

            'mobile' => $request->mobile,

            'address' => $request->address,

            'loan_amount' => $request->loan_amount,

            'emi_amount' => $request->emi_amount,
        ]);

        return response()->json([

            'status' => true,

            'message' => 'Loan updated successfully',

            'data' => $loan

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE LOAN
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $loan = Loan::find($id);

        if (!$loan) {

            return response()->json([

                'status' => false,

                'message' => 'Loan not found'

            ], 404);
        }

        $loan->delete();

        return response()->json([

            'status' => true,

            'message' => 'Loan deleted successfully'

        ], 200);
    }
}