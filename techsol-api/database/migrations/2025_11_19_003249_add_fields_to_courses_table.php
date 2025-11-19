<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->integer('workload')->nullable()->comment('Carga horária em horas')->after('description');
            $table->enum('level', ['basico', 'tecnico', 'superior'])->nullable()->after('workload');
            $table->string('area')->nullable()->comment('Área do conhecimento')->after('level');
            $table->boolean('is_active')->default(true)->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'workload', 'level', 'area', 'is_active']);
        });
    }
};