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
    // Apaga a tabela antiga
    Schema::dropIfExists('user_role');
    
    // Recria a tabela pivô de forma simples
    Schema::create('user_role', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        
        // Define que a combinação de user_id e role_id deve ser única
        $table->primary(['user_id', 'role_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
