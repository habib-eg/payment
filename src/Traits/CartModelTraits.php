<?php
namespace Habib\Payment\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait CartModelTraits
{
    protected static function bootCartModelTraits()
    {
        static::creating(function (Model $model) {
            $user = auth()->user();
            $model->owner_id ??=  $user->id;
            $model->owner_type ??=  $user->getMorphClass();
        });
    }

    public function initializeCartModelTraits()
    {

        $this->fillable = array_merge($this->fillable, ["id", "qty", "cartable_type", "cartable_id","options","owner_id","owner_type"]);

        $this->casts = array_merge($this->casts, ["qty" => "integer","options"=>"array"]);

        $this->attributes = array_merge($this->attributes, [ "qty" => 1]);

    }

    /**
     * @return MorphTo
     */
    public function product(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }


    /**
     * @param Builder $builder
     * @param array $validated
     * @return Builder
     */
    public function scopeCartFilter(Builder $builder, array $validated): Builder
    {
        return $builder->where([
            ['cartable_type', $validated['cartable_type'] ?? $validated[1]],
            ['cartable_id', $validated['cartable_id'] ?? $validated[0]],
        ]);
    }
    /**
     * @return MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeOwn(Builder $builder):Builder
    {
        return $builder->when(auth()->check(), function ($q, $v) {
            return $q->where('owner_id',auth()->id())->where('owner_type',auth()->user()->getMorphClass());
        });
    }

    public function scopeLoader(Builder $builder)
    {
        return $builder;
    }
}
