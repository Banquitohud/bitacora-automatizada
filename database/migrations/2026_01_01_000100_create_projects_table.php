<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();

            $table->date('start_date')->nullable();
            $table->date('estimated_end_date')->nullable()->index();
            $table->date('actual_end_date')->nullable();

            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_status_id')->nullable()->constrained('project_statuses')->nullOnDelete()->index();
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->nullOnDelete();

            $table->unsignedTinyInteger('progress')->default(0);

            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};