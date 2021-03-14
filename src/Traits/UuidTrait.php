<?php

namespace Habib\Payment\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait UuidTrait
{

    protected static function bootUuidTrait()
    {
        static::creating(function (Model $model) {
            $model->{self::uuidField()} = $model->{self::uuidField()} ?? Uuid::uuid4()->toString();
        });
    }

    protected static function uuidField()
    {
        return 'id';
    }

    public function initializeUuidTrait(): void
    {
        if (config('payment.uuid',true)) {
            $this->incrementing = false;
            $this->keyType = 'string';
            $this->attributes[self::uuidField()] = Uuid::uuid4()->toString();
        }
    }

}
