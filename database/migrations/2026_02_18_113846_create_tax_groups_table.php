<?php

use App\Enums\Status;
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
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('total_rate', 8, 2)->default(0);
            $table->json('sub_tax');  //tax rates name
            $table->tinyInteger('status')->default(Status::Active->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_groups');
    }
};
