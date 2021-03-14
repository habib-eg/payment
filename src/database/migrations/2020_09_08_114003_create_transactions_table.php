<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('payment.transactions_table_name','transactions'), function (Blueprint $table) {
            if (config('app.uuid',config('payment.uuid',true))){
                $table->uuid('id')->primary();
                $table->uuidMorphs(config('payment.transaction_morph','transferable'));
                $table->uuid('coupon_id')->nullable();
//                $table->nullableUuidMorphs('transferable');
            }else{
                $table->id();
                $table->morphs(config('payment.transaction_morph','transferable'));
                $table->unsignedBigInteger('coupon_id')->nullable();
//                $table->nullableMorphs('transferable');
            }

            $table->text('description')->nullable();
            $table->string('method')->nullable();
            $table->text('options')->nullable();

            $table->text('send')->nullable();
            $table->text('recipient')->nullable();

            $table->double('total',30,2,true)->nullable();
            $table->double('tax',30,2,true)->nullable();
            $table->double('shipping',30,2,true)->nullable();
            $table->unsignedtinyInteger('order_by')->default(0);
            $table->unsignedtinyInteger('status');
            $table->unsignedtinyInteger('operation_type');
            $table->string('currency')->default(config('paypal.currency','USD'));
            $table->text('contact')->nullable();
            $table->string('pay_url')->nullable();
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();
            $table->string('token')->nullable();
            $table->double('shipping_discount',30,2,true)->default(0);

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
        Schema::dropIfExists(config('payment.transactions_table_name','transactions'));
    }
}
