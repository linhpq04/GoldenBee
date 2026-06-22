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
        Schema::create('hostings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->string('provider_name', 50)->nullable();
            $table->string('hosting_name', 50);
            $table->enum('hosting_type', ['VPS', 'Shared', 'Dedicated', 'Cloud', 'Khác'])->default('Shared');
            $table->enum('status', ['Hoạt động', 'Ngừng hoạt động', 'Hết hạn', 'Tạm dừng'])->default('Hoạt động');
            $table->string('primary_name', 50)->nullable();
            $table->text('server_config')->nullable();
            $table->decimal('purchase_price', 15, 0)->nullable();
            $table->decimal('service_fee', 15, 0)->nullable();
            $table->decimal('selling_price', 15, 0)->nullable();
            $table->enum('billing_cycle', ['Hàng tháng', 'Nửa năm', 'Hàng năm', 'Khác'])->default('Hàng năm');
            $table->date('setup_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->smallInteger('remind_days')->default(30);
            $table->string('cpanel_username', 50)->nullable();
            $table->text('cpanel_password')->nullable();
            $table->string('ftp_host', 50)->nullable();
            $table->string('ftp_username', 50)->nullable();
            $table->text('ftp_password')->nullable();
            $table->string('db_host', 50)->nullable();
            $table->string('db_name', 50)->nullable();
            $table->string('db_username', 50)->nullable();
            $table->text('db_password')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostings');
    }
};
