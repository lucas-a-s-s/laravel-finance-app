<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->string('description', 160)->nullable();
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['account_id', 'transaction_date']);
            $table->index(['category_id', 'type', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
