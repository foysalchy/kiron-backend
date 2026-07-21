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
        Schema::table('note_templates', function (Blueprint $table) {
            $table->tinyInteger('status')->default(Status::Active->value)->change();
        });
    }

    public function down(): void
    {
        Schema::table('note_templates', function (Blueprint $table) {
            $table->tinyInteger('status')->default(Status::Active->value)->change();
        });
    }
};
