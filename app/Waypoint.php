<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Waypoint extends Model
{
	protected $fillable = [
		'route_id','lat', 'lon', 'position', 'description', 'tag', 'address',
	];

	public $timestamps = false;
	protected $primaryKey = null;
	public $incrementing = false;

    public function route() 
    {
    	return $this->belongsTo('App\Route');
    }
}
