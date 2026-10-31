<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->unsignedBigInteger('amount');
            $table->date('transaction_date');
            $table->time('transaction_time')->default(DB::raw('(CURRENT_TIME)'));
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transaction_date', 'type'], 'idx_transactions_date_type');
            $table->index(['wallet_id', 'type'], 'idx_transactions_wallet_type');
            $table->index(['category_id', 'transaction_date'], 'idx_transactions_category_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
