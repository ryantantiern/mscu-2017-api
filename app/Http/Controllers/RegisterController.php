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
            'firstname' => 'required|max:30',
            'lastname' => 'required|max:30',
            'dob' => 'nullable|date_format:d-m-Y',
            'phone' => 'required|unique:users|max:13',
    		'email' => 'required|email|unique:users|max:60',
            // 'username' => 'required|unique:users|max:60',
    		'password' => 'required|max:30',
    	]);
    
    	$user = User::create([
    		 'email' => $request->input('email'),
            // 'username' => $request->input('username'), 
    		'password' => bcrypt($request->input('password')),
            'phone' => $request->input('phone'),
            'dob' =>$request->input('dob'),
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
    	]);

    	$response = [
            'email' => $user->email, 
            'password' => $user->password, 
            'phone' => $user->phone,
            'dob' =>$user->dob,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        ];

        return $response;
    }
}
