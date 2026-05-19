<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    

    public function index(Request $request)
    {
        $query = Loan::query();

        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where('loan_no', 'LIKE', '%' . $request->search . '%')

                  ->orWhere('customer_name', 'LIKE', '%' . $request->search . '%')

                  ->orWhere('mobile', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->status) {

            $query->where('status', $request->status);
        }

        $perPage = $request->per_page ?? 10;

        $loans = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Loan list fetched successfully',
            'data' => $loans->items(),
            'pagination' => [
                'current_page' => $loans->currentPage(),
                'last_page' => $loans->lastPage(),
                'per_page' => $loans->perPage(),
                'total' => $loans->total(),
            ]
        ], 200);
    }


    // CREATE LOAN
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


    // LOAN DETAILS
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

    
    // UPDATE LOAN
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

   
    // DELETE LOAN
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

    public function allLoans()
    {
        $loans = Loan::where('status', 'active')
            ->select('id', 'loan_no', 'customer_name', 'pending_amount')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $loans
        ]);
    }
}