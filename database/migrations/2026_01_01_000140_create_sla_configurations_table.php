<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('days'); // hours | days
            $table->unsignedInteger('value')->default(3); // cantidad de unidades para vencer
            $table->string('applies_to')->default('global'); // global | request_type | priority
            $table->foreignId('request_type_id')->nullable()->constrained('request_types')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_configurations');
    }
};