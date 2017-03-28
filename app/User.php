<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Route;

use Illuminate\Support\Collection as Collection;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'email', 'password', 'phone', 'dob', 'firstname', 'lastname'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    /**
     *  Shared Routes User-User-Route Relationship Definitions
     */

    // Returns list of routes that User has received AND is still pending
    public function receivedRoutes()
    {
        return $this->belongsToMany(Route::class, 'shared_routes', 'receiver_id', 'route_id')
            ->wherePivot('accepted', false)
            ->withTimestamps();
    }

    // Returns list of routes User has shared AND is still pending
    public function sharedRoutes()
    {   
        return $this->belongsToMany(Route::class, 'shared_routes', 'sender_id', 'route_id')
            ->wherePivot('accepted', false)
            ->withTimestamps();
    }

    // Share route with a user
    public function shareRoute(User $user, Route $route)
    {
        $this->sharedRoutes()->attach($route->id, ['receiver_id' => $user->id]);
    }

    // Accept Route from user
    public function accept(Route $route)
    {   
        Route::create([
                'user_id' => $this->id,
                'body' => $route->body,
                'title' => $request->input('title'),
                'description' => $request->input('description'), 
                'start_address' => $request->input('start_address'),
                'end_address' => $request->input('end_address'),
        ]);
        $this->receivedRoutes()->wherePivot('route_id', $route->id)->first()->pivot->update([
            'accepted' => true
        ]);
    }

    // Decline Route from user
    public function decline(Route $route)
    {
        $this->receivedRoutes()->detach($route->id);
    }

    /**
     *  Friending User-User Relationship Definitions
     */

    // Returns list of users User has added as a friend
    private function friendsWith()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    // Returns list of users User has received friend requests from
    private function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id');
    }

    // Returns list of users User has added AND User has received friend requests from AND User has accepted
    public function friends()
    {
        return $this->friendsWith()->wherePivot('accepted', true)->get()->merge($this->friendOf()->wherePivot('accepted', true)->get());
    }

    public function addFriend(User $user)
    {
         $this->friendsWith()->attach($user->id);
    }

    // Returns true if there was a friend request made by user to User or User to user
    public function hasAFriendRequest(User $user)
    {
        return (bool) ($this->friendsWith()->get()->merge($this->friendOf()->get()))->where('id', $user->id)->count();
    }

    // Returns list of users User sent friend requests to AND friends has not accepted
    public function friendRequestsSentPending()
    {
        return $this->friendsWith()->wherePivot('accepted', false)->get();
    }

    // Returns list of users User has received friend requests to AND has not accepted
    public function friendRequestsReceivedPending()
    {
        return $this->friendOf()->wherePivot('accepted', false)->get();
    }

    public function acceptFriendRequest(User $user)
    {
        $this->friendRequestsReceivedPending()->where('id', $user->id)->first()->pivot->update([
            'accepted' => true,
            ]);
    }

    public function declineFriendRequest(User $user)
    {
        $this->friendOf()->detach($user->id);
    }



    public function isFriendsWith(User $user)
    {
        return (bool) $this->friends()->where('id', $user->id)->count();
    }




}
