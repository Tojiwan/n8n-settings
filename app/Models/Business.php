<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'name-slug', 
        'email', 
        'phone'
    ];
    
    public function bot()
    {
        return $this->hasOne(AiBot::class, 'business_id');
    }
    public function context()
    {
        return $this->hasOne(AiContext::class, 'business_id');
    }
}
