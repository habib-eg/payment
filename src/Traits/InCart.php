<?php


namespace Habib\Payment\Traits;

use App\Models\User;;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InCart
{
    /**
     * @return MorphMany
     */
    public function carts(): MorphMany
    {
        return $this->morphMany(
            config('payment.cart_model', '\Habib\Payment\Models\Cart'),
            config('payment.cart_morph', 'cartable')
        );
    }

    public function ownCarts()
    {
        return $this->carts()->where('owner_id' , auth()->check() ? auth()->id() : 0)->where('owner_type',auth()->check() ? auth()->user()->getMorphClass() : '');
    }

    /**
     * @return \Illuminate\Support\Optional|mixed
     */
    public function GetInCart()
    {
        return optional($this->ownCarts()->first());
    }
}
