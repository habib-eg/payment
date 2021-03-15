<?php

namespace Habib\Payment\Helpers;

use App\Models\User;
use Closure;
use Habib\Payment\CartInterface;
use Habib\Payment\CartProductPriceInterface;
use Habib\Payment\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * Class CartSystem
 * @package Habib\Payment\Helpers
 */
class CartSystem
{
    /**
     * @var CartInterface|CartDataBase|CartSession
     */
    public CartInterface $cart;
    /**
     * @var float
     */
    public float $tax = 0;
    /**
     * @var float
     */
    public float $shipping = 0;
    /**
     * @var float
     */
    public float $shipping_discount = 0;

    /**
     * CartSystem constructor.
     */
    public function __construct()
    {
        $this->cart = auth()->check() ? new CartDataBase() :new CartSession();
        $this->setShipping((float)config('payment.shipping',0));
        $this->setTax((float)config('payment.tax',0));
    }


    /**
     * @param float $tax
     * @return self
     */
    public function setTax(float $tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * @param float $shipping
     * @return self
     */
    public function setShipping(float $shipping)
    {
        $this->shipping = $shipping;
        return $this;
    }


    /**
     * @param array|null $validated
     * @return bool
     */
    public function addToCart(array $validated = null): bool
    {
        $validated = $validated ?? request()->validate(["cartable_id" => ['required'], "qty" => ['sometimes:numeric:gte:1'], "cartable_type" => ['required'],'options'=>['sometimes','array']]);

        $model = $validated['cartable_type']::findOrFail($validated['cartable_id']);

        return !!$this->cart->addModelToCart($model, $validated['qty'] , $validated['options'] ?? []);

    }

    /**
     * @param Model $model
     * @param int $qty
     * @param array $options
     * @return bool
     */
    public function addModelToCart(Model $model, int $qty = 1,array $options =[]): bool
    {
        return !! $this->cart->addModelToCart($model,$qty,$options);
    }

    /**
     * @param array $toArray
     * @return mixed
     */
    public function findInCart(array $toArray):bool
    {
        return !! $this->getItemCart($toArray['cartable_type'] ?? $toArray[1], $toArray['cartable_id'] ?? $toArray[0])->first();
    }

    /**
     * @param string $cartable_type
     * @param $cartable_id
     * @return mixed
     */
    public function getItemCart(string $cartable_type, $cartable_id)
    {
        return $this->cart->findInCart(['cartable_type'=> $cartable_type,'cartable_id'=> $cartable_id]);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function deleteFromCart(string $id): bool
    {
        return !! $this->cart->delete(['id'=>$id]);
    }

    /**
     * @return float
     */
    public function total(): float
    {
        return round($this->getSubtotal() + $this->getShipping() + $this->getTax(), 2);
    }

    public function getSubtotal(Closure $function=null): float
    {

        return round(
            $this->all()->filter(fn($cart)=>$cart['qty']<=$cart['cartable']->getQty())->sum($function ?? fn($cart) => $cart['qty'] * (
                ($cart['cartable'] && $cart['cartable'] instanceof CartProductPriceInterface) ? $cart['cartable']->getPrice() : optional($cart['cartable'])->price)
        ), 2);
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->cart->all();
    }

    /**
     * @return float
     */
    public function getShipping(): float
    {
        return round($this->shipping,2);
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return round($this->tax,2);
    }

    /**
     * @param string $id
     * @param int $qty
     * @param array|null $options
     * @return array|bool|Cart
     */
    public function EditCart(string $id, int $qty,array $options=[])
    {
        return $this->cart->edit($id, $qty,$options);
    }
    /**
     * @param Model $model
     * @return bool
     */
    public function inCart(Model $model): bool
    {
        return !! $this->cart->findInCart(['cartable_type'=>$model->getMorphClass(),'cartable_id'=>$model->id]);
    }

    /**
     * @return float
     */
    public function getShippingDiscount(): float
    {
        return round($this->shipping_discount,2);
    }

    /**
     * @param float $shipping_discount
     * @return $this
     */
    public function setShippingDiscount(float $shipping_discount)
    {
        $this->shipping_discount = round($shipping_discount,2);
        return $this;
    }

    /**
     * @return int
     */
    public function getCartCount(): int
    {
        return $this->all()->count();
    }

    /**
     * @return float|int
     */
    public function getTotalAfterCoupon()
    {
        $total =$this->total() - $this->getShippingDiscount();
        return   round((($total >= 0) ? $total : 0),2) ;
    }

    public function cartFormatter(callable $callable)
    {
        return $callable($this,$this->cart);
    }

}
