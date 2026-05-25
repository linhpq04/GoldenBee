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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 13)->unique();
            $table->enum('invoice_type', ['Chi phí', 'Doanh thu']);
            $table->enum('status', ['Bản nháp', 'Đã gửi', 'Đã thanh toán', 'Quá hạn', 'Đã hủy'])->default('Bản nháp');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['Tiền mặt', 'Chuyển khoản', 'Khác'])->nullable();
            $table->string('provider_name', 50)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('invoice_email', 50)->nullable();
            $table->string('service_type', 50)->nullable();
            $table->text('invoice_content')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
