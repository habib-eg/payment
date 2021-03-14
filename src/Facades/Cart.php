<?php
namespace Habib\Payment\Facades;

use Habib\Payment\CartInterface;
use Habib\Payment\Helpers\CartDataBase;
use Habib\Payment\Helpers\CartSession;
use Habib\Payment\Helpers\CartSystem;
use Illuminate\Support\Facades\Facade;

/**
 * Class Cart
 * @package Habib\Payment\Facades
 * @mixin CartInterface|CartDataBase|CartSession|CartSystem
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
