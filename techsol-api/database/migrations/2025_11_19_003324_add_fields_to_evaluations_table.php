<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('scheduled_at');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->integer('duration')->nullable()->comment('Duração em minutos')->after('ends_at');
            $table->text('instructions')->nullable()->after('duration');
            $table->integer('total_points')->default(0)->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at', 'duration', 'instructions', 'total_points']);
        });
    }
};