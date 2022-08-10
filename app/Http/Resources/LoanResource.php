<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "started_on" => $this->started_on,
            "amount" => $this->amount,
            "terms" => $this->terms,
            "status" => $this->status,
            "payment_recieved" => $this->recieved(),
            "repayments" => $this->repayments()
        ];
    }
}
