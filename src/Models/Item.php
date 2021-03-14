<?php

namespace Habib\Payment\Models;

use Habib\Payment\Traits\ItemsModelTraits;
use Habib\Payment\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use UuidTrait, SoftDeletes,ItemsModelTraits;
}
