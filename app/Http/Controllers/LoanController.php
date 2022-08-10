<?php

namespace App\Http\Controllers;

use App\Casts\LoanStatus;
use App\Http\Requests\LoanPaymentRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UpdateLoanRequest;
use App\Http\Resources\LoanResource;
use Illuminate\Http\Request;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{

    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Loan::class, 'loan');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $userId = auth()->id();
        return Loan::whereUserId($userId)->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLoanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLoanRequest $request)
    {
        // Creare loan with default fields
        $loan = $request->user()->loans()->create([
            "amount" => $request->amount,
            "terms" => $request->term
        ]);

        // Check and update started date if provided
        if($request->has('started_on')) {
            $loan->started_on = new Carbon($request->started_on);
            $loan->save();
        }
        $loan->refresh();

        return response(new LoanResource($loan), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        // Sending loan in specified resource format.
        return (new LoanResource($loan));
    }

    public function pay(LoanPaymentRequest $request, Loan $loan)
    {
        // Check if loan is approved or not !
        if ($loan->status !== LoanStatus::APPROVED) {
            return response(['message' => "Loan is in '$loan->status' state. unable to process the payment."], 422);
        }

        // Preparing variables
        $amount = $request->amount;
        $installment = $loan->amount / $loan->terms;
        $pending = $amount - $loan->recieved();
        $pending = $pending < 0 ? 0: $pending;
        $min = $pending < $installment ? $pending : $installment;

        // Check if amount is acceptable or not
        if ($amount < $min) {
            return response([
                "message" => "You have to pay atleast $min."
            ], 422);
        }

        $loan->payments()->create(compact('amount'));
        $loan->refresh();
        return response(new LoanResource($loan), 201);
    }
}
