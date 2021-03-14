<?php
namespace Habib\Payment;

use Habib\Payment\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CartInterface
{
    /**
     * @param $validated
     * @return array|Cart[]|Collection
     */
    public function add($validated);
    /**
     * @return array|Collection|Cart[]
     */
    public function all();
    /**
     * @param array $validated
     * @return array|Cart
     */
    public function findInCart(array $validated);
    /**
     * @param string $id
     * @param int $qty
     * @param array $options
     * @return array|Cart
     */
    public function edit(string $id, int $qty = 1,array $options = []);

    /**
     * @param $validated
     * @return array|Cart[]|Collection
     */
    public function delete($validated);

    /**
     * @param Model $model
     * @param int $qty
     * @param array|null $options
     * @return array|bool|Cart
     */
    public function addModelToCart(Model $model, int $qty = 1, array $options = []);
}