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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 13)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('format', ['1-1', 'Nhóm', 'Trực tuyến', 'Tại lớp'])->default('Nhóm');
            $table->enum('status', ['Bản nháp', 'Đang mở', 'Hoàn thành', 'Đã hủy'])->default('Bản nháp');
            $table->smallInteger('max_student')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->decimal('price', 15, 0)->default(0);
            $table->timestamps();
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 0)->default(0);
            $table->date('enrolled_at');
            $table->enum('status', ['Đang học', 'Hoàn thành', 'Đã hủy', 'Tạm dừng'])->default('Đang học');
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('courses');
    }
};
