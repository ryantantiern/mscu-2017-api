<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Route;
use App\User;

class RouteController extends Controller
{
    public function routes(Request $request)
    {
        //Todo: response should not expose id of route within the table
        $user = $request->user();
        $routes = $user->routes()->get();
        $response = ['status' => 'response', 'response' => 'routes', 'count' => $routes->count() , 'routes' => $routes];
        return $response;
    }

    public function create(Request $request)
    {
    	$json_coordinates = $request->input('coordinates');
    	$array_coordinates = json_decode($json_coordinates);

    	if (!is_array($array_coordinates)) {
    		return ['status' => 'error', 'error' => 'Route is not an array'];
      	}

        // Error checking
      	foreach ($array_coordinates as $coord) {
      		if (!is_array($coord)) { return ['status' => 'error' , 'error' => 'Coordinates are not an array']; }
      		if (!count($coord) == 2) { return ['status' => 'error', 'error' => 'Not a a pair of coordinates']; }
			foreach ($coord as $val) {
				if (!is_numeric($val)) { return ['status' => 'error', 'error' => 'Coordinate is not numeric value']; }
  			}
      	}
	    
    	$route = Route::create([
    		'user_id' => $request->user()->id,
    		'body' => $json_coordinates
    	]);

        $response = ['status' => 'response', 'response' => 'route', 'route' => $route];
    	return $route;
    }

    public function delete(Request $request, $route_id)
    {
        $user = $request->user();
        $response = ['status' => 'success'];
        if (empty($user->routes()->where('id', $route_id)->first())){
            $response = ['status' => 'fail', 'fail' => 'Route does not exist'];
        }
        else {
            $user->routes()->where('id', $route_id)->first()->delete();
        }
        return $response;
    }

    public function share(Request $request, $user_id, $route_id)
    {
        $response = ['status' => 'success'];
        $user = $request->user();
        $friend = User::find($user_id);
        $route = $user->routes()->find($route_id);

        if (empty($friend))
            { $respons = ['status' => 'fail', 'fail' => 'Friend with id ' . $user_id . 'does not exist']; }
        else if (empty($route))
            { $response = ['status' => 'fail' , 'fail' => 'You do not own a route with id ' . $route_id]; }
        else if (!$user->isFriendsWith($friend)) 
            { $response = ['status' => 'fail', 'fail' => 'You are not friends with user: ' . $user_id];}
        else if ($friend->receivedRoutes()->wherePivot('route_id', $route->id)->get()->count())
        { $response = ['status' => 'fail', 'fail' => 'You have already shared route ' . $route_id . ' from user: ' . $user_id]; }
        else{ $user->shareRoute($friend, $route); }
        return $response;
    }

    public function received(Request $request)
    {
        $response = ['status' => 'response'];
        $user = $request->user();

        if ($user->receivedRoutes()->get()->isEmpty()){
            $response['response'] ='You have no routes pending'; 
        }
        else {
            $response['response'] = $user->receivedRoutes()->get();
        }
        return $response;

    }

    public function accept(Request $request, $route_id) 
    {
        $response = ['status' => 'response'];
        $user = $request->user();
        $route = Route::find($route_id) ?? NULL;

        if (empty($route)) { $response['response'] = 'Route does not exist';}
        else if ($user->receivedRoutes()->wherePivot('route_id', $route_id)->get()->isEmpty()) { 
            $response['response'] = 'You do not have access to this shared route.'; 
        }
        else {
            $user->accept($route);
            $response['status'] = 'success!';
        }
        return $response;
    }

    public function decline(Request $request, $route_id)
    {
        $response = ['status' => 'response'];
        $user = $request->user();
        $route = Route::find($route_id) ?? NULL;

        if (empty($route)) { $response['response'] = 'Route does not exist';}
        else if ($user->receivedRoutes()->wherePivot('route_id', $route_id)->get()->isEmpty()) { 
            $response['response'] = 'You do not have access to this shared route.'; 
        }
        else {
            $user->decline($route);
            $response['status'] = 'success!';
        }
        return $response;
    }


}
