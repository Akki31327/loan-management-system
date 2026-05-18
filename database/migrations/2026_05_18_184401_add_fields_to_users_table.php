<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('mobile', 15)->nullable()->after('email');

            $table->enum('role', ['admin', 'field_agent'])
                  ->default('field_agent')
                  ->after('mobile');

            $table->boolean('status')
                  ->default(1)
                  ->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'mobile',
                'role',
                'status'
            ]);
        });
    }
};