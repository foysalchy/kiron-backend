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
        Schema::table('transaction_journals', function (Blueprint $table) {
            $table->string('voucher_type', 30)->default('journal')->after('reference_number'); // journal, payment, receipt, contra, sales, purchase
            $table->string('voucher_no', 50)->nullable()->after('voucher_type');
            $table->string('source_type', 50)->nullable()->after('voucher_no'); // order, purchase, payroll, project_revenue, production, manual
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->foreignId('party_id')->nullable()->after('source_id')->constrained('parties')->nullOnDelete();
            $table->text('narration')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_journals', function (Blueprint $table) {
            $table->dropForeign(['party_id']);
            $table->dropColumn([
                'voucher_type',
                'voucher_no',
                'source_type',
                'source_id',
                'party_id',
                'narration',
            ]);
        });
    }
};
