<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_cases', function (Blueprint $table) {
            $table->id();

            $table->string('internal_id')->nullable()->index();
            $table->string('case_number')->nullable()->unique()->index();

            $table->date('received_date')->nullable()->index();
            $table->time('received_time')->nullable();
            $table->dateTime('due_date')->nullable()->index();

            $table->string('requester')->nullable();
            $table->string('affected_user')->nullable()->index();
            $table->string('position')->nullable();

            $table->foreignId('request_type_id')->nullable()->constrained('request_types')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->string('permission')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('group_family_id')->nullable()->constrained('group_families')->nullOnDelete();

            $table->foreignId('priority_id')->nullable()->constrained('priorities')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('case_statuses')->nullOnDelete();
            $table->foreignId('analyst_id')->nullable()->constrained('users')->nullOnDelete()->index();

            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            $table->text('concept')->nullable();
            $table->text('result')->nullable();
            $table->text('observations')->nullable();
            $table->text('comments')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('taken_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cases');
    }
};