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
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->default(350)->after('due_at');
            $table->decimal('fine', 8, 2)->default(0)->after('amount');
            $table->decimal('total', 8, 2)->default(0)->after('fine');
            $table->boolean('is_paid')->default(false)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['amount', 'fine', 'total', 'is_paid']);
        });
    }
};
