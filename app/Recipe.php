<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'title', 'image_url' ,'time', 'people', 'ingredients', 'publisher', 'source_url', 'id'
    ];
}