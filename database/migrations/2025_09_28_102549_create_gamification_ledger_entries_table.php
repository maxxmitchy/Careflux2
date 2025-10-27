<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamification_ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->comment('Pharmacist/Tech receiving points')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('task_definition_id')
                ->constrained()
                ->cascadeOnDelete();

            // Custom morphs with shorter index name
            $table->string('subjectable_type');
            $table->unsignedBigInteger('subjectable_id');
            $table->index(['subjectable_type', 'subjectable_id'], 'ledger_subject_idx');

            $table->integer('points_awarded');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_ledger_entries');
    }
};
