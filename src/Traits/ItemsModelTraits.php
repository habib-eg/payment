<?php


namespace Habib\Payment\Traits;


use Habib\Payment\Models\Item;
use Habib\Payment\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait ItemsModelTraits
{
    public function initializeItemsModelTraits()
    {

        $this->fillable = array_merge($this->fillable, [ "price", "qty", "description", "options", "product_id", "product_type", "transaction_id", ]);

        $this->casts = array_merge($this->casts, [
            "options" => "array"
        ]);

    }
    /**
     * @return MorphTo
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
