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
        Schema::create('account_balance_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('operation', 30);
            $table->string('transaction_type', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('impact_amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'abm_user_created_idx');
            $table->index(['account_id', 'created_at'], 'abm_account_created_idx');
            $table->index(['financial_transaction_id', 'operation'], 'abm_transaction_operation_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_balance_movements');
    }
};
