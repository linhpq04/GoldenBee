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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 13)->unique();
            $table->string('name', 50);
            $table->string('company_name', 255)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('tax_code', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->enum('type', ['Cá nhân', 'Công ty'])->default('Cá nhân');
            $table->enum('status', ['Tiềm năng', 'Đang hoạt động', 'Ngừng hoạt động'])->default('Tiềm năng');
            $table->string('source', 50)->nullable();
            $table->string('source_detail', 255)->nullable();
            $table->string('industry', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
