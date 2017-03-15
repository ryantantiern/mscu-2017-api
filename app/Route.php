<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Route extends Model
{
    protected $table = 'routes';

    protected $fillable = ['user_id', 'body', 'title'];

    public function user() 
    {
    	return $this->belongsTo(User::class);
    }

}
