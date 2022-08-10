<?php

namespace App\Http\Controllers;

use App\Casts\LoanStatus;
use App\Models\Loan;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    //
    public function approve(Loan $loan)
    {
        if($loan->status === LoanStatus::PENDING) {
            $loan->status = LoanStatus::APPROVED;
            $loan->save();
            $message = "Approved successfully.";
            return response(compact('message'), 200);
        }
        return response('No change', 304);
    }
}
