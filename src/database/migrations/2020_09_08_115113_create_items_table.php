<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            if (config('payment.uuid',true)){
                $table->uuid('id')->primary();
                $table->uuid('transaction_id');
                $table->uuidMorphs(config('payment.items_morph','product'));
            }else{
                $table->id();
                $table->unsignedBigInteger('transaction_id');
                $table->morphs(config('payment.items_morph','product'));
            }
            $table->double('price',30,2);
            $table->unsignedTinyInteger('qty')->default(1);
            $table->text('description')->nullable();
            $table->text('options')->nullable();
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
        Schema::dropIfExists('items');
    }
}
