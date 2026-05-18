<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {

            $table->id();

            $table->foreignId('loan_id')
                  ->constrained('loans')
                  ->cascadeOnDelete();

            $table->foreignId('collected_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('amount_paid', 12, 2);

            $table->enum('payment_mode', [
                'cash',
                'upi',
                'card'
            ]);

            $table->string('location')->nullable();

            $table->timestamp('collected_at');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('payment_mode');
            $table->index('collected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};