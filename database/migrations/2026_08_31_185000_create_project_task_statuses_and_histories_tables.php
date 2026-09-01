<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dynamic Task Statuses
        if (!Schema::hasTable('project_task_statuses')) {
            Schema::create('project_task_statuses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->string('color')->default('#13565e');
                $table->string('bg_color')->default('#e6f4f5');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_completed')->default(false);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'status']);
            });
        }

        // Task Status Transition Duration History
        if (!Schema::hasTable('project_task_status_histories')) {
            Schema::create('project_task_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('duration_seconds')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'task_id']);
            });
        }

        // Add Active Timer tracking columns to project_tasks if not present
        Schema::table('project_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('project_tasks', 'status_id')) {
                $table->foreignId('status_id')->nullable()->after('status')->constrained('project_task_statuses')->nullOnDelete();
            }
            if (!Schema::hasColumn('project_tasks', 'current_status_started_at')) {
                $table->timestamp('current_status_started_at')->nullable()->after('status_id');
            }
            if (!Schema::hasColumn('project_tasks', 'is_timer_running')) {
                $table->boolean('is_timer_running')->default(false)->after('current_status_started_at');
            }
            if (!Schema::hasColumn('project_tasks', 'timer_started_at')) {
                $table->timestamp('timer_started_at')->nullable()->after('is_timer_running');
            }
            if (!Schema::hasColumn('project_tasks', 'timer_user_id')) {
                $table->foreignId('timer_user_id')->nullable()->after('timer_started_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('project_tasks', 'timer_user_id')) {
                $table->dropForeign(['timer_user_id']);
                $table->dropColumn('timer_user_id');
            }
            if (Schema::hasColumn('project_tasks', 'status_id')) {
                $table->dropForeign(['status_id']);
                $table->dropColumn('status_id');
            }
            $table->dropColumn(['current_status_started_at', 'is_timer_running', 'timer_started_at']);
        });

        Schema::dropIfExists('project_task_status_histories');
        Schema::dropIfExists('project_task_statuses');
    }
};
