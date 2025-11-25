<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // User who paid
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete(); // Loan associated
            $table->string('method'); // Payment method (Stripe, Cash, etc.)
            $table->string('reference')->unique(); // Unique payment reference
            $table->decimal('borrow_fee', 10, 2)->default(0); // Borrow fee
            $table->decimal('fine_per_day', 10, 2)->default(0); // Fine per overdue day
            $table->integer('fine_days')->default(0); // Number of overdue days
            $table->decimal('fine_total', 10, 2)->default(0); // Total fine
            $table->decimal('total', 10, 2)->default(0); // Total payment (borrow + fine)
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
