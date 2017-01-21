<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class FriendController extends Controller
{
    public function friends(Request $request)
    {
        $response = ['status' => 'response', 'response' => ''];
        $user = $request->user();
        if ($user->friends()->isEmpty()){ $response['response'] = 'You have no friends';}
        else {$response['response'] = $user->friends()->toArray(); $response['count'] = $user->friends()->count();}
        return $response;
    }

    public function add(Request $request, $user_id)
    {
    	$user = $request->user();
    	$response = ['error' => 'Internal Server Error'];
  		try {
  			$friend = User::find($user_id);
  			if ($user->hasAFriendRequest($friend)) { 
  				$response = ['status' => 'fail', 'fail' => 'Friend request was already sent to user id: ' . $friend->id]; 
  			}
  			else if ($user->id == $friend->id) {
  				$response = ['status' => 'fail', 'fail' => 'Cannot add self']; 
  			}
  			else { 
  				$user->addFriend($friend);
  				$response = ['status' => 'success'];
  			}
  		} 
  		catch (Exception $e) { $response = ['status' => 'error', 'error' => $e]; } 
  		finally {return $response;}
    }

    public function delete(Request $request, $user_id)
    {
      $user = $request->user();
      $response = ['status' => 'success'];
      if (empty($user->friends()->where('id', $user_id)->first())){
          $response = ['status' => 'fail', 'fail' => 'User is not your friend'];
      }
      else {
          $user->friends()->where('id', $user_id)->first()->pivot->delete();
      }
      return $response;
    }

    public function accept(Request $request, $user_id)
    {
        $response = ['status' => 'success'];
        $user = $request->user();
        $friend = User::find($user_id);

        if ($user->friendRequestsReceivedPending()->where('id', $user_id)->isEmpty()){
            $response = ['status' => 'response', 'response' => 'No friend request received from user: ' . $user_id];
        }
        else {
            $user->acceptFriendRequest($friend);        
        }
        
        return $response;
    }

    public function decline(Request $request, $user_id)
    {
        $response = ['status' => 'success'];
        $user = $request->user();
        $friend = User::find($user_id);

        if ($user->friendRequestsReceivedPending()->where('id', $user_id)->isEmpty()){
            $response = ['status' => 'fail', 'fail' => 'No friend request received from user: ' . $user_id];
        }

        else if ($user->isFriendsWith($friend)){
            $response = ['status' => 'fail', 'fail' => 'Already friends with user: ' . $user_id];
        }

        else { $user->declineFriendRequest($friend);}

        return $response;
    }

    public function received(Request $request)
    {
        $user = $request->user();
        if ($user->friendRequestsReceivedPending()->isEmpty()){
            return ['status' => 'response', 'response' => 'No friend requests received'];
        }

        return $user->friendRequestsReceivedPending();
    }

    public function sent(Request $request)
    {
      $user = $request->user();
      if ($user->friendRequestsSentPending()->isEmpty()){
          return ['status' => 'response', 'response' => 'No friend requests sent'];
      }

      return $user->friendRequestsSentPending();
    }

}
