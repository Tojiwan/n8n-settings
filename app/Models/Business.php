<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    protected $fillable = [
        'name',
        'name-slug', 
        'email', 
        'mobile'
    ];

    // Automatically set slug when creating/updating
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($biz) {
            if (empty($biz->name_slug)) {
                $biz->name_slug = Str::slug($biz->name);
            }
        });
    }
    
    public function bot()
    {
        return $this->hasOne(AiBot::class, 'business_id');
    }
    public function context()
    {
        return $this->hasOne(AiContext::class, 'business_id');
    }
}
