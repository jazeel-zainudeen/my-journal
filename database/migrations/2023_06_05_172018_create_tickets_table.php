<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->string('customer_name');
            $table->timestamp('departure_date')->nullable();
            $table->timestamp('return_date')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('reference_id');
            $table->decimal('cost')->default(0.0);
            $table->decimal('total')->default(0.0);
            $table->decimal('profit')->default(0.0);
            $table->decimal('extra_charges')->default(0.0);
            $table->decimal('collection_amount')->default(0.0);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('refunded_at')->nullable();

            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('reference_id')->references('id')->on('references');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
