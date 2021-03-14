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
        return $this->carts()->where(User::USER_ID ?? 'user_id' , auth()->check() ? auth()->id() : 0);
    }

    /**
     * @return \Illuminate\Support\Optional|mixed
     */
    public function GetInCart()
    {
        return optional($this->ownCarts()->first());
    }
}
