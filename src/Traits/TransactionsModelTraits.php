<?php


namespace Habib\Payment\Traits;


use Habib\Payment\Models\Item;
use Habib\Payment\Models\Transaction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trait TransactionsModelTraits
 * @package Habib\Payment\Traits
 * @property-read Transaction
 */
trait TransactionsModelTraits
{
    public static function bootTransactionsModelTraits()
    {
        static::creating(function (self $transaction) {
            $transaction->transferable_id = $transaction->transferable_id ?? auth()->check() ? auth()->id() : null;
            $transaction->transferable_type = $transaction->transferable_type ?? auth()->check() ? get_class(auth()->user()) : null;
        });
    }

    public function initializeTransactionsModelTraits()
    {
        $this->setAppends(array_merge($this->append ?? [], [
            "status_text",
            "method_text"
        ]));

        $this->fillable = array_merge($this->fillable, [
            "id", "transferable_id", "transferable_type", "description", "method", "operation_type",
            "options", "total", "token", "status", "currency", "pay_url", "return_url", "cancel_url",
            "shipping_discount", "tax", "shipping", "contact", "coupon_id", "order_by"
        ]);

        $this->attributes = array_merge($this->attributes, [
            "currency" => self::CURRENCY,
            "method" => self::PAYMENT['PAYPAL']??1,
            "status" => self::STATUS['PENDING']??0,
            "operation_type" => self::OPERATION['BUYING']??0,
            "total" => 0,
            "order_by" =>self::ORDER_BY['WEBSITE']??0,
            "tax" => config('payment.tax', 0),
            "shipping" => config('payment.shipping', 0),
            "shipping_discount" => config('payment.shipping_discount', 0),
        ]);

        $this->casts = array_merge($this->casts, [
            "options" => "array",
            "contact" => "array",
            "total" => "float",
            "shipping" => "float",
            "tax" => "float",
            "shipping_discount" => "float",
        ]);

    }

    /**
     * @return MorphTo
     */
    public function transferable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
//        return $this->hasMany(Item::class)->withoutGlobalScopes([SoftDeletingScope::class]);
        return $this->hasMany(config('payment.item_model',Item::class), 'transaction_id');
    }


    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeSuccess(Builder $builder): Builder
    {
        $builder->where('status', config('payment.transaction_model', Transaction::class)::STATUS['SUCCESS']);
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopePending(Builder $builder): Builder
    {
        return $builder->where('status', config('payment.transaction_model', Transaction::class)::STATUS['PENDING']);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeExpired(Builder $builder): Builder
    {
        return $builder->where('status', config('payment.transaction_model', Transaction::class)::STATUS['EXPIRED']);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeCancel(Builder $builder): Builder
    {
        return $builder->where('status', config('payment.transaction_model', Transaction::class)::STATUS['CANCEL']);
    }

    /**
     * @return mixed
     */
    public function getSubTotal():float
    {
        return round($this->items->map(function ($item) {
            return $item->price * $item->qty;
        })->sum(),2);
    }

    /**
     * @return array|Application|Translator|string|null
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 1:
                return __('main.success');
                break;
            case 0:
                return __('main.pending');
                break;
            case 2:
                return __('main.cancel');
                break;
            case 3:
                return __('main.expired');
                break;
            case 4:
                return __('main.failure');
                break;
            default:
                return "";
                break;
        }
    }

    /**
     * @return array|Application|Translator|string|null
     */
    public function getMethodTextAttribute()
    {
        switch ($this->method) {
            case 1:
                return __('main.paypal');
                break;
            case 0:
                return __('main.cash_on_delivery');
                break;
            default:
                return "";
                break;
        }
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return self::STATUS['PENDING'] === 0;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return self::STATUS['SUCCESS'] === 1;
    }

    /**
     * @return bool
     */
    public function isCancel(): bool
    {
        return self::STATUS['CANCEL'] === 2;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return self::STATUS['EXPIRED'] === 3;
    }

    /**
     * @return bool
     */
    public function isFailure(): bool
    {
        return self::STATUS['FAILURE'] === 4;
    }


}
