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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
           $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('movement_number')->unique(); // Auto generated
            $table->date('movement_date');
            
            // Source & Destination
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0);
            
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
