<?php

namespace Habib\Payment\Helpers;

use Exception;
use Habib\Payment\CartInterface;
use Habib\Payment\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class CartDataBase implements CartInterface
{
    protected $user;

    /**
     * CartDataBase constructor.
     */
    public function __construct()
    {
        $this->user = $this->checkAllAuth();
        $this->addCartToLoginUser();
    }

    /**
     * @param $validated
     * @return array|Cart|Collection
     */
    public function add($validated)
    {
        return $this->user->carts()->firstOrCreate(
            collect($validated)->only(['cartable_id','cartable_type'])->toArray(),
            collect($validated)->except(['cartable','cartable_id','cartable_type'])->toArray()
        ) ;
    }

    /**
     * @return array|Cart[]|Collection
     */
    public function all()
    {
        return cache()->remember('carts.'.$this->user->getKey(),now()->addSeconds(10),fn()=>$this->user->carts()->with(['cartable'])->get());
    }

    /**
     * @param array $validated
     * @return array|Cart|null
     */
    public function findInCart(array $validated)
    {
        return $this->user->carts()->loader()->when($validated['id'] ?? null, function ($q) use ($validated) {
            $q->where('id', $validated['id']);
        })->when($validated['cartable_type'] ?? null, function ($q) use ($validated) {
            $q->where('cartable_type', $validated['cartable_type']);
        })->when($validated['cartable_id'] ?? null, function ($q) use ($validated) {
            $q->where('cartable_id', $validated['cartable_id']);
        })->where('owner_id',$this->user->id)->where('owner_type',$this->user->getMorphClass())->first();
    }

    /**
     * @param string $id
     * @param int $qty
     * @param array $options
     * @return array|Cart
     */
    public function edit(string $id, int $qty = 1, array $options = [])
    {
        $cart = $this->findInCart(['id' => $id]);
        if ($cart) {
            $cart['qty'] = $qty > 0 ? $qty : $cart['qty'];
            foreach ($options as $key => $option) {
                $cart['options'][$key] = $option;
            }
            $cart->save();
        }
        return $cart;
    }

    /**
     * @param $validated
     * @return array|bool|Cart[]|Collection
     */
    public function delete($validated)
    {
        try {
            $this->findInCart($validated)->delete();
            return $this->all();
        } catch (Exception $e) {

        }
        return false;
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
        return  $this->add($validated);
    }


    /**
     * @return $this
     */
    public function addCartToLoginUser(): self
    {
        if (!$this->user) {
            return $this;
        }
        $cartSession = new CartSession();
        $cart = $cartSession->getCarts();

        if ($cart->count() > 0) {
                $cart->map(function ($cart) {
                    $this->user->carts()->firstOrCreate(
                        collect($cart)->only(['cartable_id','cartable_type'])->toArray(),
                        collect($cart)->except(['cartable','cartable_id','cartable_type'])->toArray()
                    ) ;
                });

                $cartSession->setCart([]);
            }

        return $this;
    }

    public function checkAllAuth()
    {
        foreach (config('auth.guards') as $guard => $array) {
            if (auth($guard)->check()) {
               return auth($guard)->user();
            }
        }
        return  optional('');
    }
}