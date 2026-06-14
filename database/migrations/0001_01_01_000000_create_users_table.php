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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('position', 50)->nullable();
            $table->enum('status', ['Hoạt động', 'Không hoạt động', 'Đình chỉ'])->default('Hoạt động');
            $table->string('id_number', 12)->nullable();
            $table->date('id_issued_date')->nullable();
            $table->string('id_issued_place', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->string('address', 255)->nullable();
            $table->string('emergency_contact', 255)->nullable();
            $table->decimal('base_salary', 15, 0)->nullable();
            $table->decimal('hourly_rate', 10, 0)->nullable();
            $table->string('tax_code', 13)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_branch', 50)->nullable();
            $table->string('bank_account_number', 20)->nullable();
            $table->string('bank_account_name', 50)->nullable();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
