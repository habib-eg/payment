<?php

namespace Habib\Payment\Models;

use Habib\Payment\Traits\TransactionsModelTraits;
use Habib\Payment\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction
 * @package Habib\Payment\Models
 * @property-read TransactionsModelTraits
 */
class Transaction extends Model
{
    use SoftDeletes, UuidTrait,TransactionsModelTraits;

    /**
     * @var string
     */
    const CURRENCY = "USD";

    /**
     * @var array
     */
    const STATUS = [
        "PENDING" => 0,
        "SUCCESS" => 1,
        "CANCEL" => 2,
        "EXPIRED" => 3,
        "FAILURE" => 4,
    ];
    /**
     * @var array
     */
    const OPERATION = [
        "BUYING" => 0,
    ];
    /**
     * @var array
     */
    const PAYMENT = [
        "PAYPAL" => 1,
        "CASH_ON_DELIVERY"=>0,
    ];
    /**
     * @var array
     */
    const ORDER_BY = [
        "WEBSITE" =>0,
        "PHONE"=>1,
    ];

}
