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
        Schema::table('operational_units', function (Blueprint $table) {
            // Adicionando as novas colunas após a coluna 'name' para manter organização
            $table->string('code', 50)->nullable()->after('name');
            $table->string('city')->nullable()->after('code');
            $table->integer('days_submission')->nullable()->default(0)->after('city');
            
            // Decimal(5,2) permite valores como 100.00 ou 99.99
            $table->decimal('approval_percentage', 5, 2)->nullable()->default(0)->after('days_submission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operational_units', function (Blueprint $table) {
            $table->dropColumn(['code', 'city', 'days_submission', 'approval_percentage']);
        });
    }
};