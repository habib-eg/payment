<?php


namespace Habib\Payment\Traits;

use Habib\Payment\Helpers\CartSystem;
use Habib\Payment\Helpers\PaymentCheckout;
use Habib\Payment\Helpers\PaymentCheckoutInterface;
use Habib\Payment\Http\Resources\CartResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCart
{
    /**
     * @param array $validated
     * @return bool
     */
    public function inCart(array $validated): bool
    {
        return !! $this->cartFilter($validated)->count() >0 ;
    }

    /**
     * @param array $validated
     * @return HasMany
     */
    public function cartFilter(array $validated)
    {
        return $this->carts()->where([
            ['cartable_type', $validated['cartable_type'] ?? $validated[1]],
            ['cartable_id', $validated['cartable_id'] ?? $validated[0]],
        ]);
    }
    /**
     * @return MorphMany
     */
    public function carts(): MorphMany
    {
        return $this->morphMany(
            config('payment.cart_model', '\Habib\Payment\Models\Cart'),
            'owner'
        );
    }

    /**
     * @return PaymentCheckout
     */
    public function prepareCartToCheckOut():PaymentCheckout
    {
        // Resources
        $paymentCheckout = new PaymentCheckout();
        $this->carts->map(function ($cart) use ($paymentCheckout) {
            return $paymentCheckout->pushItem(CartResource::toArray($cart));
        });

        return $paymentCheckout;

    }

    /**
     * @param $id
     * @param int $qty
     * @param array|null $options
     * @return bool
     */
    public function findAndUpdateQty($id,int $qty,array $options=null)
    {
        $cart =['qty' => $qty >= 1 ? $qty : 1];
        if ($options) {
            $cart['options']=$options;
        }
        return !! optional($this->carts()->updateOrCreate(['id'=> $id],$cart));
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function findAndDelete($id)
    {
        return !! optional($this->carts()->firstWhere('id', $id))->delete();
    }
}
