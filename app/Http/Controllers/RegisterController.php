<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
    	$this->validate($request, [
    		'data.email' => 'required|email|unique:users,email|max:60',
    		'data.password' => 'required|max:30',
    	]);
    
    	$user = User::create([
    		'email' => $request->input('data.email'),
    		'password' => bcrypt($request->input('data.password')),
    	]);

    	$response = ['email' => $user->email, 'password' => $user->password, 'status' => 'all ok!'];

        return $response;
    }
}
