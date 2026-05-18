<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {

            $table->id();

            $table->string('loan_no')->unique();

            $table->string('customer_name');

            $table->string('mobile', 15);

            $table->text('address');

            $table->decimal('loan_amount', 12, 2);

            $table->decimal('emi_amount', 12, 2);

            $table->decimal('total_paid', 12, 2)
                  ->default(0);

            $table->decimal('pending_amount', 12, 2);

            $table->enum('status', ['active', 'closed'])
                  ->default('active');

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->index('loan_no');
            $table->index('mobile');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};