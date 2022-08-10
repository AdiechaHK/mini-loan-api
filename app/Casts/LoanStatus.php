<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LoanStatus implements CastsAttributes
{
    const PENDING = "pending";
    const APPROVED = "approved";
    const PAID = "paid";

    private $values = [self::PENDING, self::APPROVED, self::PAID];

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return Arr::get($this->values, $value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $indx = array_search(Str::lower($value), $this->values);
        if ($indx === false) {
            throw new InvalidArgumentException('The given value for loan status is not valid.');
        }
        return $indx;
    }
}
