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
    Schema::table('invitations', function (Blueprint $table) {
        // Adiciona o CPF, que agora é coletado no início
        $table->string('cpf')->nullable()->after('email');
        
        // Altera o status para refletir o novo fluxo
        $table->string('status')->default('step_1_pending_profiles')->change(); // pending, step_2_pending_context, step_3_pending_confirmation, sent, completed, expired
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            //
        });
    }
};
