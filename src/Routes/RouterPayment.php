<?php

namespace Habib\Payment\Routes;


use Closure;

class RouterPayment
{
    /**
     * @param array $options
     * @return Closure
     */
    public function paymentRoutes(array $options = [])
    {
        $options = array_merge($options, config('payment.route_options', []));
        return function () use ($options) {
            $this->group($options, function () {
                $this->post('ipn/notify', 'PayPalController@postNotify');
//                $this->get('payment', 'PayPalController@paypal')->name('paypal.payment');
                $this->get('payment', 'PayPalController@payment')->name('paypal.payment');
                $this->get('payment/success', 'PayPalController@success')->name('payment.success');
                $this->get('payment/cancel', 'PayPalController@canceled')->name('payment.cancel');
                $this->get('payment/{method}/callback', 'PayPalController@callback')->name('payment.callback');
                $this->post('cart/add', 'CartController@addCart')->name('add.cart');
                $this->get('cart/index', 'CartController@index')->name('cart.index');
                $this->post('cart/edit/{id}', 'CartController@edit')->name('cart.edit');
                $this->get('products', 'CartController@products')->name('products');
            });

        };
    }
}
