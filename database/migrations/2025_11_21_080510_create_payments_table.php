<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            $table->string('method');          // Stripe, Cash, MPesa
            $table->string('reference')->nullable();  // Stripe / MPesa reference

            $table->integer('borrow_fee')->default(0);
            $table->integer('fine_per_day')->default(0);
            $table->integer('fine_days')->default(0);
            $table->integer('fine_total')->default(0);
            $table->integer('total')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
