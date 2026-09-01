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
        // 1. Projects table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->string('project_type')->default('other')->index(); // real_estate, construction, software, website, marketing, consultancy, service, internal, other
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable()->index();
            $table->string('priority')->default('medium')->index(); // low, medium, high, urgent
            $table->string('status')->default('planning')->index(); // draft, planning, active, on_hold, completed, cancelled, archived
            $table->unsignedBigInteger('project_manager_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('billing_type')->default('fixed_price'); // fixed_price, hourly, milestone_based, task_based, recurring, non_billable
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('currency', 10)->default('BDT');
            $table->json('tags')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Project Phases
        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->string('status')->default('pending')->index(); // pending, in_progress, completed, on_hold, cancelled
            $table->decimal('progress', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Project Milestones
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->index(); // pending, in_progress, completed, cancelled
            $table->decimal('progress', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Project Members
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('role')->default('member'); // project_manager, lead, developer, designer, engineer, worker, member
            $table->string('cost_type')->default('hourly'); // hourly, daily, monthly, fixed
            $table->decimal('cost_rate', 15, 2)->default(0);
            $table->decimal('billable_rate', 15, 2)->default(0);
            $table->date('assigned_date')->nullable();
            $table->date('release_date')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 5. Project Tasks
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->unsignedBigInteger('parent_task_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo')->index(); // todo, in_progress, review, completed, blocked, cancelled
            $table->string('priority')->default('medium')->index(); // low, medium, high, urgent
            $table->unsignedBigInteger('assigned_to')->nullable()->index(); // employee_id
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->decimal('estimated_hours', 10, 2)->default(0);
            $table->decimal('actual_hours', 10, 2)->default(0);
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->boolean('is_completed')->default(false)->index();
            $table->timestamp('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Project Task Dependencies
        Schema::create('project_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->string('dependency_type')->default('finish_to_start'); // finish_to_start, start_to_start
            $table->timestamps();
        });

        // 7. Project Time Entries (Time tracking)
        Schema::create('project_time_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->decimal('duration_hours', 8, 2)->default(0);
            $table->boolean('is_billable')->default(true);
            $table->decimal('cost_rate', 15, 2)->default(0);
            $table->decimal('billable_rate', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_billable', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Project Labor Costs
        Schema::create('project_labor_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('worker_name')->nullable();
            $table->string('role')->nullable();
            $table->string('cost_type')->default('hourly'); // hourly, daily, monthly, fixed
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('Hours'); // Hours, Days, Months, Units
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('date')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('time_entry_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Project Materials
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variation_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->string('item_name')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->string('unit', 50)->default('Pcs');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('date')->index();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('stock_movement_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Project Equipment Costs
        Schema::create('project_equipment_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('equipment_name');
            $table->string('usage_type')->default('daily'); // hourly, daily, fixed
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('Days');
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('date')->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 11. Project Expenses
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('category')->default('Miscellaneous'); // Travel, Transport, Hotel, Software, Office, Consultant, Miscellaneous
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->date('date')->index();
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 12. Project Overheads
        Schema::create('project_overheads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('calculation_type')->default('fixed'); // fixed, percentage
            $table->decimal('rate_or_percentage', 10, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 13. Project Budgets
        Schema::create('project_budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->string('category'); // labor, materials, equipment, expenses, overhead, other
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('revised_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 14. Project Revenues
        Schema::create('project_revenues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, invoiced, paid, partially_paid
            $table->date('date')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 15. Project Documents
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('category')->default('general'); // contract, requirement, design, report, general
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 16. Project Discussions
        Schema::create('project_discussions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->json('mentions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 17. Project Activities
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action'); // created, updated, status_changed, member_added, task_created, etc.
            $table->text('description');
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        // 18. Project Templates
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->string('project_type')->default('other');
            $table->text('description')->nullable();
            $table->json('structure_json'); // phases, milestones, tasks
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 19. Project Settings
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('project_prefix')->default('PRJ-');
            $table->string('task_prefix')->default('TSK-');
            $table->json('custom_statuses')->nullable();
            $table->json('default_budgets')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_settings');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('project_activities');
        Schema::dropIfExists('project_discussions');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('project_revenues');
        Schema::dropIfExists('project_budgets');
        Schema::dropIfExists('project_overheads');
        Schema::dropIfExists('project_expenses');
        Schema::dropIfExists('project_equipment_costs');
        Schema::dropIfExists('project_materials');
        Schema::dropIfExists('project_labor_costs');
        Schema::dropIfExists('project_time_entries');
        Schema::dropIfExists('project_task_dependencies');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_phases');
        Schema::dropIfExists('projects');
    }
};
