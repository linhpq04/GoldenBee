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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('salary_type', 50);
            $table->date('payment_date');
            $table->enum('status', ['Chờ duyệt', 'Đã duyệt', 'Đã thanh toán', 'Từ chối'])->default('Chờ duyệt');
            $table->string('period', 50)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->decimal('work_hours', 6, 2)->default(0);
            $table->decimal('hourly_rate', 10, 0)->default(0);
            $table->smallInteger('work_days')->default(0);
            $table->decimal('base_salary', 15, 0)->default(0);
            $table->decimal('bonus', 15, 0)->default(0);
            $table->decimal('allowance', 15, 0)->default(0);
            $table->decimal('deduction', 15, 0)->default(0);
            $table->decimal('advance', 15, 0)->default(0);
            $table->decimal('net_salary', 15, 0)->default(0);
            $table->enum('payment_method', ['Tiền mặt', 'Chuyển khoản', 'Khác'])->default('Chuyển khoản');
            $table->string('transaction_code', 255)->nullable();
            $table->text('detail_description')->nullable();
            $table->text('note')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
