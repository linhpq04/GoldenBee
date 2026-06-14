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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->string('code', 13)->unique();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->string('type', 50);
            $table->enum('status', ['Lên kế hoạch', 'Đang thực hiện', 'Tạm dừng', 'Hoàn thành', 'Đã hủy'])->default('Lên kế hoạch');
            $table->enum('priority', ['Thấp', 'Trung bình', 'Cao', 'Khẩn cấp'])->default('Trung bình');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 0);
            $table->decimal('budget', 15, 0)->nullable();
            $table->boolean('warranty_lifetime')->default(false);
            $table->smallInteger('warranty_months')->nullable();
            $table->date('warranty_expires_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
