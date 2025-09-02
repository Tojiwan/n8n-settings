<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiBot extends Model
{
    protected $fillable = [
        'business_id', 
        'enabled', 
        'n8n_workflow_id', 
        'n8n_webhook_path', 
        'n8n_webhook_secret'
    ];
    
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    public function context()
    {
        return $this->hasOne(AiContext::class, 'business_id', 'business_id');
    }
}
