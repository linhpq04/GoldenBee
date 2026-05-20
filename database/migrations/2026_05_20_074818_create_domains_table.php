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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('hosting_id')->nullable()->constrained('hostings')->nullOnDelete();
            $table->string('provider_name', 50)->nullable();
            $table->string('domain_name', 50)->unique();
            $table->enum('status', ['Hoạt động', 'Ngừng hoạt động', 'Hết hạn', 'Đang chuyển'])->default('Hoạt động');
            $table->decimal('purchase_price', 15, 0)->nullable();
            $table->decimal('service_fee', 15, 0)->nullable();
            $table->decimal('selling_price', 15, 0)->nullable();
            $table->date('registered_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->date('last_renewed_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->smallInteger('remind_days')->default(30);
            $table->string('servername_1', 255)->nullable();
            $table->string('servername_2', 255)->nullable();
            $table->string('servername_3', 255)->nullable();
            $table->string('servername_4', 255)->nullable();
            $table->string('login_username', 50)->nullable();
            $table->string('login_password', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
