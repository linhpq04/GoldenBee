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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('ip_address', 45)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('provider_name', 50)->nullable();
            $table->enum('status', ['Hoạt động', 'Ngừng hoạt động', 'Bảo trì'])->default('Hoạt động');
            $table->smallInteger('ram_gb')->nullable();
            $table->smallInteger('cpu_cores')->nullable();
            $table->integer('disk_gb')->nullable();
            $table->string('bandwidth', 100)->nullable();
            $table->string('os', 100)->nullable();
            $table->string('ssh_host', 255)->nullable();
            $table->smallInteger('ssh_port')->nullable();
            $table->string('ssh_username', 50)->nullable();
            $table->text('ssh_password')->nullable();
            $table->text('ssh_key')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
