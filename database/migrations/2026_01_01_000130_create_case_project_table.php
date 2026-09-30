<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_case_id')->constrained('daily_cases')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['daily_case_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_project');
    }
};