<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Route;
use App\User;
use App\Waypoint;

class RouteController extends Controller
{
    public function routes(Request $request)
    {
        //Todo: response should not expose id of route within the table
        $user = $request->user();
        $routes = $user->routes()->get();
        $response = [
            'status' => 'succes', 
            'count' => $routes->count() , 
            'routes' => $routes->map(function($route) {
                return [
                    "id" => $route->id,
                    "title" =>  $route->title, 
                    "description" => $route->description,
                    "start_address" => $route->start_address,
                    "end_address" => $route->end_address,
                    "created_at" => $route->created_at,
                ];
            })
        ];
        return $response;
    }

    public function waypoints(Route $route) {
        return [$route->waypoints()->get()];
    }

    public function create(Request $request)
    {
    
        $payload = $request->all();
        $waypoints = $payload["waypoints"];

        // Error checking
        foreach ($waypoints as $i=>$wp) {
           if (!$wp["lat"] || !$wp["lon"]) {
                return ["error" => "Missing lat, lon value at waypoint #{$i}"];
           }
           else if (!is_numeric($wp["lat"]) || !is_numeric($wp["lon"])) {
                return ["error" => "Lat, Lon is not valid numeric coordinate(s) at waypoint #{$i}"];
           }
        }

        // Add position to waypoints
        $waypoints = collect($waypoints)->map(function($wp, $i) {
            $wp["position"] = $i;
            return new Waypoint($wp);
        });


        

        // Create Route
        $route = Route::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'), 
            'start_address' => $request->input('start_address'),
            'end_address' => $request->input('end_address'),
        ]);
        
        // Convert objects to Waypoint objects
       $route->waypoints()->saveMany($waypoints);
       
        return ['success'];
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
