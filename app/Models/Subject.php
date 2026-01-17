<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function level() {
        return $this->belongsTo(Level::class);
    }
}
