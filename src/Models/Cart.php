<?php

namespace Habib\Payment\Models;

use Habib\Payment\Traits\CartModelTraits;
use Habib\Payment\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use UuidTrait, SoftDeletes,CartModelTraits;

}
