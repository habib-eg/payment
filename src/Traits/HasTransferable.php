<?php


namespace Habib\Payment\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTransferable
{
    /**
     * @return MorphMany
     */
    public function transactions():MorphMany
    {
        return $this->morphMany(
            config('payment.transaction_model', '\Habib\Payment\Models\Transaction'),
            config('payment.transaction_morph', 'transferable')
        );
    }

    /**
     * @return HasManyThrough
     */
    public function items():HasManyThrough
    {
        return $this->hasManyThrough(
            config('payment.transaction_model', '\Habib\Payment\Models\Transaction'),
            config('payment.item_model', '\Habib\Payment\Models\Item')
        );
    }

}
