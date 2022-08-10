<?php

namespace App\Services;

use App\Casts\LoanStatus;
use App\Models\Loan;
use Carbon\Carbon;
use LDAP\Result;

class Repayment {
    public Carbon $due_date;
    public float $amount;
    public string $status;

    public static function generate(Loan $loan) {

        $pending = $loan->amount;
        $count = $loan->terms;
        $installment = round($loan->amount / $loan->terms, 2);
        $paid = $loan->recieved();
        $dt = new Carbon($loan->started_on);

        $result = [];

        while ($count > 0) {

            // Change before
            $dt = $dt->addDay(config('loan.repayment_frequency'));

            $rpay = new Repayment;
            $rpay->due_date = $dt->toDateString();
            $iamt = round($count === 1 ? $pending: $installment, 2);
            $rpay->amount = $iamt;
            $rpay->status = $paid > $iamt ? LoanStatus::PAID : LoanStatus::PENDING;

            // Change after
            $pending -= $iamt;
            $paid -= $iamt;
            $count--;

            $result[] = $rpay;
        }
        
        return $result;
    }
}