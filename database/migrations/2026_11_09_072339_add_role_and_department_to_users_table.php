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
     // up()
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['admin', 'staff'])->default('staff')->after('email');
        $table->foreignId('department_id')->nullable()->after('role')
            ->constrained()->nullOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
