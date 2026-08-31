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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('order_number');
            $table->foreignId('production_plan_id')->nullable()->constrained('production_plans')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->foreignId('bill_of_material_id')->constrained('bills_of_materials')->onDelete('cascade');
            $table->foreignId('raw_material_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('finished_goods_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->onDelete('set null');
            $table->foreignId('current_stage_id')->nullable()->constrained('production_stages')->onDelete('set null');
            
            $table->decimal('planned_quantity', 15, 2)->default(0);
            $table->decimal('produced_quantity', 15, 2)->default(0);
            $table->decimal('rejected_quantity', 15, 2)->default(0);
            $table->string('unit')->default('PCS');
            
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'draft',
                'planned',
                'in_progress',
                'quality_check',
                'completed',
                'paused',
                'cancelled'
            ])->default('draft');

            $table->boolean('allow_partial_production')->default(false);
            $table->date('planned_start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->dateTime('actual_start_date')->nullable();
            $table->dateTime('actual_completion_date')->nullable();

            $table->decimal('estimated_total_cost', 15, 2)->default(0);
            $table->decimal('actual_total_cost', 15, 2)->default(0);

            $table->string('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status', 'created_at']);
        });

        Schema::create('production_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->decimal('required_quantity', 15, 4)->default(0);
            $table->decimal('consumed_quantity', 15, 4)->default(0);
            $table->decimal('wastage_quantity', 15, 4)->default(0);
            $table->string('unit')->default('PCS');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->enum('status', ['pending', 'partially_consumed', 'fully_consumed'])->default('pending');
            $table->timestamps();

            $table->index(['company_id', 'production_order_id']);
        });

        Schema::create('production_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('production_stage_id')->constrained('production_stages')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_stage_logs');
        Schema::dropIfExists('production_order_items');
        Schema::dropIfExists('production_orders');
    }
};
