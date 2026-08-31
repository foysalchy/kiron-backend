<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('location')->nullable();
            $table->string('machine_name')->nullable();
            $table->decimal('capacity_per_day', 15, 2)->default(0);
            $table->decimal('hourly_cost', 15, 2)->default(0);
            $table->decimal('operating_hours_per_day', 8, 2)->default(8);
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('production_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->integer('sequence')->default(1);
            $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->onDelete('set null');
            $table->integer('estimated_duration_minutes')->default(0);
            $table->string('assigned_team_or_person')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_stages');
        Schema::dropIfExists('work_centers');
    }
};
