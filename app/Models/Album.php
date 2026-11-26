<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['album_name','release_date', 'long', 'description'];

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}
