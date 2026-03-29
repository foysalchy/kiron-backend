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
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Basic,Regular,Advance');
            $table->string('sub_title')->nullable();
            $table->decimal('monthly_regular_price', 10, 2)->default(0);
            $table->decimal('monthly_discount_price', 10, 2)->default(0);
            $table->decimal('yearly_regular_price', 10, 2)->default(0);
            $table->decimal('yearly_discount_price', 10, 2)->default(0);
            $table->integer('order_limitation')->default(0);
            $table->integer('user_limitation')->default(0);
            $table->json('features')->nullable();
            $table->integer('free_trial')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->tinyInteger('status')->default(Status::Inactive->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
