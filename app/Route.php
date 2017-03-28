<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Route extends Model
{
    protected $table = 'routes';

    protected $fillable = ['user_id', 'body', 'title', 'description', 'start_address', 'end_address'];

    public function user() 
    {
    	return $this->belongsTo(User::class);
    }

}
