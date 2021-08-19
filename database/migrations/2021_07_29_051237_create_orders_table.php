<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->text('customer_name');
            $table->integer('driver_id')->nullable();
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_long', 10, 7);
            $table->decimal('destination_lat', 10, 7);
            $table->decimal('destination_long', 10, 7);
            $table->text('destination_address');
            $table->integer('fee')->default(0);
            $table->enum('status', ['process', 'done', 'canceled'])->default('process');
            $table->timestamps();

            // foreign key
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
