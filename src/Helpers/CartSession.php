<?php

namespace Habib\Payment\Helpers;

use Habib\Payment\CartInterface;
use Habib\Payment\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class CartSession implements CartInterface
{
    protected $carts = [];

    /**
     * CartSession constructor.
     */
    public function __construct()
    {
        $this->carts = session('cart');
    }

    /**
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public function getCarts()
    {
        return collect($this->carts);
    }

    /**
     * @param string $id
     * @param int $qty
     * @param array $options
     * @return array|Cart
     */
    public function edit(string $id, int $qty = 1, array $options = [])
    {
        $this->setCart($this->getCarts()->map(function ($cart) use ($qty, $id, $options) {
            if (($cart['id'] == $id)) {
                $cart['qty'] = $qty > 0 ? $qty : $cart['qty'];
                foreach ($options as $key => $option) {
                    $cart['options'][$key] = $option;
                }
            }
            return $cart;
        }));
        return $this->findInCart(['id' => $id]);
    }

    /**
     * @param $carts
     * @return array|Collection|Cart[]
     */
    public function setCart($carts)
    {
        if ($carts instanceof Collection) {
            $carts = $carts->except('cartable')->toArray();
        }
        session()->put('cart', $carts);
        return $carts;
    }

    /**
     * @return array|Collection|Cart[]
     */
    public function all()
    {
        return $this->getCarts()->map(function ($cart) {
            $cart['cartable'] = $cart['cartable_type']::find($cart['cartable_id']);
            return $cart;
        })->filter(fn($cart) => $cart['cartable']);
    }

    /**
     * @param array $validated
     * @return array|Cart
     */
    public function findInCart(array $validated)
    {
        $cart = $this->all();
        if (isset($validated['id'])) {
            $cart = $cart->where('id', $validated['id']);
        }
        if (isset($validated['cartable_type'])) {
            $cart = $cart->where('cartable_type', $validated['cartable_type']);
        }
        if (isset($validated['cartable_id'])) {
            $cart = $cart->where('cartable_id', $validated['cartable_id']);
        }
        return $cart->first();
    }

    /**
     * @param $validated
     * @return array|Cart[]|Collection
     */
    public function delete($validated)
    {
        $cart = $this->findInCart($validated);
        return $this->setCart($this->getCarts()->whereNotIn('id', [$cart['id']]));
    }

    /**
     * @param Model $model
     * @param int $qty
     * @param array|null $options
     * @return array|bool|Cart
     */
    public function addModelToCart(Model $model, int $qty = 1, array $options = [])
    {
        $validated = ["cartable_id" => $model->id, "cartable_type" => $model->getMorphClass(), "qty" => ($qty >= 1 ? $qty : 1), 'options' => $options,];
        return $this->add($validated);
    }

    /**
     * @param $validated
     * @return array|Cart[]|Collection
     */
    public function add($validated)
    {
        $cart = $this->all();
        $validated['id'] ??= Uuid::uuid4()->toString();
        if (!$this->findInCart($validated) || ($cart->count() === 0)) {
            $cart->push($validated);
        }
        return $this->setCart($cart);
    }
}