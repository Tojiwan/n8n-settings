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
        Schema::create('workflow_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_id')->index();
            $table->string('node_id')->nullable();
            $table->string('workflow_name')->nullable();
            $table->longText('system_prompt');
            $table->timestamps();

            // Ensure no duplicate workflow_id + node_id
            $table->unique(['workflow_id', 'node_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_prompts');
    }
};
