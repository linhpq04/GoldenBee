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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('title', 50);
            $table->string('type', 50);
            $table->enum('status', ['Bản nháp', 'Chờ ký', 'Đang hiệu lực', 'Hết hạn', 'Đã hủy'])->default('Bản nháp');
            $table->text('description')->nullable();
            $table->decimal('contract_value', 15, 0)->default(0);
            $table->boolean('has_vat')->default(false);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->date('signed_at')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
