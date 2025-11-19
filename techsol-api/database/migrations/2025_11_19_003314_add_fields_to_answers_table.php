<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->after('answer_content');
            $table->timestamp('answered_at')->nullable()->after('score');
            $table->integer('time_spent')->nullable()->comment('Tempo em segundos')->after('answered_at');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['is_correct', 'answered_at', 'time_spent']);
        });
    }
};