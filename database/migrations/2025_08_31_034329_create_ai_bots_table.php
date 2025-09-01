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
        Schema::create('ai_bots', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('business_id')->unique();
            $t->boolean('enabled')->default(false);
            $t->string('n8n_workflow_id')->nullable();
            $t->string('n8n_webhook_path')->nullable();   // ai/{businessId}/{secret}
            $t->string('n8n_webhook_secret')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_bots');
    }
};
