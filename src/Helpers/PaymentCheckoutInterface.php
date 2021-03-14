<?php

namespace Habib\Payment\Helpers;

use Illuminate\Database\Eloquent\Model;

interface PaymentCheckoutInterface
{
    /**
     * @param array $item
     * @return $this
     */
    public function pushItem(array $item): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param array $item
     * @return $this
     */
    public function addItem(array $item): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param float $discount
     * @param bool $percentage
     * @return $this
     */
    public function AddShippingDiscount(float $discount, bool $percentage = true);

    /**
     * @return float
     */
    public function getTotal(): float;

    /**
     * @return float
     */
    public function getSubTotal(): float;

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal(float $total = null): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param array|null $order
     * @return array
     */
    public function getCheckoutOrder(array $order = null): array;

    /**
     * @return array
     */
    public function getFilteredItems(): array;

    /**
     * @return string
     */
    public function getInvoiceId(): string;

    /**
     * @param $invoice_id
     * @return $this
     */
    public function setInvoiceId(string $invoice_id): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @return string
     */
    public function getInvoiceDescription(): string;

    /**
     * @param $invoice_description
     * @return $this
     */
    public function setInvoiceDescription(string $invoice_description): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @return string
     */
    public function getReturnUrl(): string;

    /**
     * @param $return_url
     * @return $this
     */
    public function setReturnUrl(string $return_url): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @return string
     */
    public function getCancelUrl(): string;

    /**
     * @param $cancel_url
     * @return $this
     */
    public function setCancelUrl(string $cancel_url): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @return float
     */
    public function getShippingDiscount(): float;

    /**
     * @param float $shipping_discount
     * @return $this
     */
    public function setShippingDiscount(float $shipping_discount): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param array|null $array
     * @return array
     */
    public function transaction(array $array = null): array;

    /**
     * @return float
     */
    public function getTax(): float;

    /**
     * @param float $tax
     * @return $this
     */
    public function setTax(float $tax): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @return float
     */
    public function getShipping(): float;

    /**
     * @param float $shipping
     * @return $this
     */
    public function setShipping(float $shipping): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param Model $transaction
     * @return array
     */
    public function setTransactionItems(Model $transaction): array;

    /**
     * @return array
     */
    public function getItems(): array;

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items): \Habib\Payment\Helpers\PaymentCheckout;

    /**
     * @param array $item
     * @return array
     */
    public function item(array $item): array;
}
