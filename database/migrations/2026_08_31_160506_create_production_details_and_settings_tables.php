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
        Schema::dropIfExists('production_settings');
        Schema::dropIfExists('production_costs');
        Schema::dropIfExists('production_quality_checks');
        Schema::dropIfExists('production_wastages');
        Schema::dropIfExists('production_outputs');
        Schema::dropIfExists('production_consumptions');

        // Consumptions (Raw material deducted)
        Schema::create('production_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->string('unit')->default('PCS');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->dateTime('consumed_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id']);
        });

        // Outputs (Finished/Semi-finished goods added)
        Schema::create('production_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->string('unit')->default('PCS');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->dateTime('output_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id']);
        });

        // Wastages / Scrap
        Schema::create('production_wastages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->string('unit')->default('PCS');
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->enum('reason', [
                'cutting_waste',
                'damaged',
                'defective',
                'process_loss',
                'expired',
                'machine_error',
                'other'
            ])->default('process_loss');
            $table->date('date')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'production_order_id', 'reason'], 'pw_comp_order_reason_idx');
        });

        // Quality Checks
        Schema::create('production_quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->decimal('inspected_quantity', 15, 2)->default(0);
            $table->decimal('passed_quantity', 15, 2)->default(0);
            $table->decimal('failed_quantity', 15, 2)->default(0);
            $table->decimal('defective_quantity', 15, 2)->default(0);
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('inspection_date')->useCurrent();
            $table->enum('status', ['pending', 'passed', 'failed', 'partially_passed'])->default('pending');
            $table->json('defect_details')->nullable();
            $table->foreignId('defective_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'production_order_id', 'status'], 'pqc_comp_order_status_idx');
        });

        // Production Costs Breakdown
        Schema::create('production_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->decimal('raw_material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('machine_cost', 15, 2)->default(0);
            $table->decimal('electricity_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('packaging_cost', 15, 2)->default(0);
            $table->decimal('wastage_cost', 15, 2)->default(0);
            $table->decimal('other_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('produced_quantity', 15, 2)->default(0);
            $table->decimal('cost_per_unit', 15, 4)->default(0);
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('cost_variance', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['company_id', 'production_order_id']);
        });

        // Production Settings
        Schema::create('production_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('order_prefix')->default('PO');
            $table->string('bom_prefix')->default('BOM');
            $table->string('plan_prefix')->default('PP');
            $table->boolean('auto_consume_on_start')->default(true);
            $table->boolean('require_qc_before_completion')->default(false);
            $table->boolean('allow_over_consumption')->default(false);
            $table->foreignId('default_raw_material_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('default_finished_goods_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->timestamps();

            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_settings');
        Schema::dropIfExists('production_costs');
        Schema::dropIfExists('production_quality_checks');
        Schema::dropIfExists('production_wastages');
        Schema::dropIfExists('production_outputs');
        Schema::dropIfExists('production_consumptions');
    }
};
