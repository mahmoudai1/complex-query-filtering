<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    public function languages()
    {
        return $this->belongsToMany(Language::class)->withTimestamps();
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class)->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_category')->withTimestamps();
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class)->withPivot('value')->withTimestamps();
    }
}
