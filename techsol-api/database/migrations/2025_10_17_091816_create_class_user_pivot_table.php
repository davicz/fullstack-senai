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
    Schema::create('class_user', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('school_class_id')->constrained('classes')->onDelete('cascade');
        
        // Define que a combinação de usuário e turma deve ser única
        $table->primary(['user_id', 'school_class_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_user_pivot');
    }
};
