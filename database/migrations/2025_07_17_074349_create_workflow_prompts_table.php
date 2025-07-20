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
            $table->string('workflow_id')->index(); // n8n workflow ID
            $table->string('workflow_name')->nullable();
            $table->longText('system_prompt');
            $table->timestamps();
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
