<?php

namespace App\Models;

use App\Casts\LoanStatus;
use App\Services\Repayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'terms',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => LoanStatus::class,
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments() {
        return $this->hasMany(LoanPayment::class, 'loan_id');
    }

    public function recieved() {
        return $this->payments()->sum('amount');
    }

    public function repayments() {
        return Repayment::generate($this);
    }
}
