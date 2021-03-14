<?php

namespace Habib\Payment\Helpers;

use Habib\Payment\Facades\Cart;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PaymentCheckout implements PaymentCheckoutInterface
{
    public $items = [];
    public $invoice_id;
    public $invoice_description;
    public $return_url;
    public $cancel_url;
    public $total = 0;
    public $tax = 0;
    public $shipping = 0;
    public $shipping_discount = 0;
    public $precision = 2;

    /**
     * PaymentCheckout constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->invoice_id = $invoice_id = Uuid::uuid4()->toString();
        $this->invoice_description = 'main.invoice_id';
        $this->tax = config('payment.tax',0);
        $this->shipping = config('payment.shipping',0);
        $this->setShippingDiscount(Cart::getShippingDiscount());
        foreach ($options as $key => $option) {
            $this->{$key} = $option;
        }

    }

    /**
     * @param array $item
     * @return $this
     */
    public function pushItem(array $item): self
    {
        $price = round((float)$item['price'], $this->precision);
        $this->addItem([
            "price" => $price >= 1 ? $price : 100000,
            "qty" => (int)$item['qty'] ?? 1,
            "name" => (string)$item['name'] ?? (string)$item['title'],
            "desc" => (string)$item['desc'] ?? (string)$item['description'] ?? '',
            "class" => (string)$item['class'],
        ]);
        return $this;
    }

    /**
     * @param array $item
     * @return $this
     */
    public function addItem(array $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @param float $discount
     * @param bool $percentage
     * @return $this
     */
    public function AddShippingDiscount(float $discount, bool $percentage = true)
    {
        $this->setShippingDiscount($percentage ? round(($discount / 100) * $this->getTotal(), 2) : $discount);
        return $this;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return round($this->getSubTotal() + $this->getTax() +$this->getShipping(), $this->precision);
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        return round((float)collect($this->items)->sum(function ($item) {
            return $item['price'] * $item['qty'];
        }), $this->precision);
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal(float $total = null): self
    {
        $this->total = (float)($total ?? $this->getRealTotal());
        return $this;
    }

    /**
     * @param array|null $order
     * @return array
     */
    public function getCheckoutOrder(array $order = null): array
    {
        return [
            "items" => $order['items'] ?? (array)$this->getFilteredItems(),
            "invoice_id" => $order['invoice_id'] ?? (string)$this->getInvoiceId(),
            "invoice_description" => $order['invoice_description'] ?? (string)$this->getInvoiceDescription(),
            "return_url" => $order['return_url'] ?? (string)$this->getReturnUrl(),
            "cancel_url" => $order['cancel_url'] ?? (string)$this->getCancelUrl(),
            "shipping_discount" => $order['shipping_discount'] ?? floatval($this->getShippingDiscount()),
            "total" => $order['total'] ?? floatval($this->getTotal()),
            "tax" => $order['tax'] ?? floatval($this->getTax()),
            "subtotal" => $order['total'] ?? floatval($this->getSubTotal()),
            "shipping" => $order['total'] ?? floatval($this->getShipping()),
        ];
    }

    /**
     * @return array
     */
    public function getFilteredItems(): array
    {
        return collect($this->items)->map(function ($item) {
            unset($item['class']);
            return $item;
        })->toArray();
    }

    /**
     * @return string
     */
    public function getInvoiceId(): string
    {
        return (string)$this->invoice_id;
    }

    /**
     * @param $invoice_id
     * @return $this
     */
    public function setInvoiceId(string $invoice_id): self
    {
        $this->invoice_id = $invoice_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceDescription(): string
    {
        return (string)$this->invoice_description;
    }

    /**
     * @param $invoice_description
     * @return $this
     */
    public function setInvoiceDescription(string $invoice_description): self
    {
        $this->invoice_description = (string)$invoice_description;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return (string)$this->return_url;
    }

    /**
     * @param $return_url
     * @return $this
     */
    public function setReturnUrl(string $return_url): self
    {
        $this->return_url = (string)$return_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancelUrl(): string
    {
        return (string)$this->cancel_url;
    }

    /**
     * @param $cancel_url
     * @return $this
     */
    public function setCancelUrl(string $cancel_url): self
    {
        $this->cancel_url = (string)$cancel_url;
        return $this;
    }

    /**
     * @return float
     */
    public function getShippingDiscount(): float
    {
        return (float)$this->shipping_discount;
    }

    /**
     * @param float $shipping_discount
     * @return $this
     */
    public function setShippingDiscount(float $shipping_discount): self
    {
        $total = $this->getTotal();

        $this->shipping_discount = ($total >= (float)$shipping_discount) ? $shipping_discount :$total;
        return $this;
    }

    /**
     * @param array|null $array
     * @return array
     */
    public function transaction(array $array = null): array
    {
        return array_merge($array, [
            "cancel_url" => $this->getCancelUrl(),
            "return_url" => $this->getReturnUrl(),
            "total" => $this->getTotal(),
            "shipping_discount" => $this->getShippingDiscount(),
            "tax" => $this->getTax(),
            "shipping" => $this->getShipping()
        ]);
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     * @return $this
     */
    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return float
     */
    public function getShipping(): float
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     * @return $this
     */
    public function setShipping(float $shipping): self
    {
        $this->shipping = $shipping;
        return $this;

    }

    /**
     * @param Model $transaction
     * @return array
     */
    public function setTransactionItems(Model $transaction): array
    {
        return collect($this->getItems())->map(function ($item) use ($transaction) {
            return $transaction->items()->create($this->item($item));
        })->toArray();
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param array $item
     * @return array
     */
    public function item(array $item): array
    {
        return [
            "price" => $item['price'],
            "qty" => $item['qty'],
            "product_id" => $item['desc'],
            "product_type" => $item['class'],
            "description" => $item['desc'],
        ];
    }
}
