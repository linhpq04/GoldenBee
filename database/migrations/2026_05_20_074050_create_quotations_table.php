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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('code', 13)->unique();
            $table->enum('status', ['Bản nháp', 'Chờ duyệt', 'Đã gửi', 'Chấp nhận', 'Từ chối'])->default('Bản nháp');
            $table->date('valid_until')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('tax_amount', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('service_type', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 15, 0)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('total', 15, 0)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
