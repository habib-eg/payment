<?php

return [
    'transaction_model' => '\Habib\Payment\Models\Transaction',
    'transactions_table_name' => 'transactions',
    'transaction_morph' => 'transferable',

    'item_model' => '\Habib\Payment\Models\Item',
    'items_table_name' => 'items',
    'items_morph' => 'product',

    'cart_model' => '\Habib\Payment\Models\Cart',
    'cart_morph' => 'cartable',
    'cart_table_name' => 'carts',



    'user_id' => 'user_id',
    'user_model' => '\App\Models\User',

    'uuid' => true,

    'route_options' => [
        "namespace"=>"\Habib\Payment\Http\Controllers",
        "prefix"=>"dashboard"
    ],

    'shipping' => 10,
    'tax' => 25,

];
