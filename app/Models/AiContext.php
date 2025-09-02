<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContext extends Model
{
    protected $fillable = [
        'business_id', 
        'goal', 
        'context', 
        'guardrails', 
        'system'
    ];
}
