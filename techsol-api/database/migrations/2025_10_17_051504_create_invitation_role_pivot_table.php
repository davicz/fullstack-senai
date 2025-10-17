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
    Schema::create('invitation_role', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invitation_id')->constrained()->onDelete('cascade');
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        
        // Colunas de "contexto"
        $table->foreignId('regional_department_id')->nullable()->constrained();
        $table->foreignId('operational_unit_id')->nullable()->constrained();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_role_pivot');
    }
};
