<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('payment.cart_table_name','carts'), function (Blueprint $table) {
            if (config('payment.uuid',true)){
                $table->uuid('id')->primary();
                $table->uuidMorphs(config('payment.cart_morph','cartable'));
                $table->uuidMorphs('owner');
            }else{
                $table->id();
                $table->morphs(config('payment.cart_morph','cartable'));
                $table->morphs('owner');
            }
            $table->unsignedTinyInteger('qty')->default(1);
            $table->text('options')->default(json_encode([]));
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('payment.cart_table_name','carts'));
    }
}
