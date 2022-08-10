<?php

namespace App\Observers;

use App\Casts\LoanStatus;
use App\Models\LoanPayment;
use Illuminate\Support\Facades\Log;

class LoanPaymentObserver
{
    /**
     * Handle the LoanPayment "created" event.
     *
     * @param  \App\Models\LoanPayment  $loanPayment
     * @return void
     */
    public function created(LoanPayment $loanPayment)
    {
        Log::info("Loan payment created [id:" . $loanPayment->id . "]");
        // collected required variables
        $loan = $loanPayment->loan;
        $recieved = $loan->recieved();
        $amount = $loan->amount;

        // check for loan is paied or not
        if($recieved >= $amount) {
            $loan->status = LoanStatus::PAID;
            $loan->save();
        }
    }

    /**
     * Handle the LoanPayment "updated" event.
     *
     * @param  \App\Models\LoanPayment  $loanPayment
     * @return void
     */
    public function updated(LoanPayment $loanPayment)
    {
        //
        Log::info("Loan payment updated [id:" . $loanPayment->id . "]");
    }

    /**
     * Handle the LoanPayment "deleted" event.
     *
     * @param  \App\Models\LoanPayment  $loanPayment
     * @return void
     */
    public function deleted(LoanPayment $loanPayment)
    {
        Log::info("Loan payment deleted [id:" . $loanPayment->id . "]");
        //
        // collected required variables
        $loan = $loanPayment->loan;
        $recieved = $loan->recieved();
        $amount = $loan->amount;

        // check for loan is paied or not
        if($loan->status != LoanStatus::APPROVED && $recieved < $amount) {
            $loan->status = LoanStatus::APPROVED;
            $loan->save();
        }
    }

    /**
     * Handle the LoanPayment "restored" event.
     *
     * @param  \App\Models\LoanPayment  $loanPayment
     * @return void
     */
    public function restored(LoanPayment $loanPayment)
    {
        //
        Log::info("Loan payment restore [id:" . $loanPayment->id . "]");
    }

    /**
     * Handle the LoanPayment "force deleted" event.
     *
     * @param  \App\Models\LoanPayment  $loanPayment
     * @return void
     */
    public function forceDeleted(LoanPayment $loanPayment)
    {
        //
        Log::info("Loan payment force deleted [id:" . $loanPayment->id . "]");
    }
}
