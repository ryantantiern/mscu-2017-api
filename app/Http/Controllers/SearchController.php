<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class SearchController extends Controller
{
    public function search(Request $request, $query) 
    {
    	// Query can be int or firstname lastname
	    // Return users that match phone or firstname lastname
	    // Filter duplicates
	    if (empty($query)) {
	    	return ['message' => 'Not a valid query'];
	    }

    	$queryParams = explode(" ", $query); 

       	if (sizeof($queryParams) == 1 && is_numeric($queryParams[0])) {
       		// Search by phone
       		return $this->searchByPhone($queryParams[0], $request->user()->id);
       	}

   		// Search by name
   		return $this->searchByName($queryParams, $request->user()->id);
    

    }
    /**
     * [searchByPhone search users by phone]
     * @param  [Int] $numeric := phone number
     * @param  [Int] $user_id := current uid
     * @return [Collection]  Users that match $numeric  
     */
    private function searchByPhone($numeric, $user_id)
    {
    	return User::select('id', 'firstname', 'lastname')
    		->where([
    			['phone', 'LIKE', "%{$numeric}%"],
    			['id', '!=', $user_id]
    		])
    		->orderBy('lastname', 'desc')
    		->get();
    }

    /**
     * [searchByName search for users by first or lastname or both]
     * @param  [Array String] $name := firstname or lastname or Both
     * @param  [Int] $user_id := current uid
     * @return [Collection]  Users that match $name
     */
    
    private function searchByName($name, $user_id) 
    {
  		// Strings after position 1 are not considered
  		// Since name matching is case sensitive - TODO: store names at lowercase and query as lowercase
  		// Solutions ref :http://stackoverflow.com/questions/7005302/postgresql-how-to-make-case-insensitive-query
  		$fullname = [$name[0], $name[0]];
  		if (sizeof($name) > 1) {
  			$fullname[1] = $name[1];
  		}


    	return User::select('id', 'firstname', 'lastname')
    		->where('firstname', 'LIKE', "%{$fullname[0]}%")
    		->orWhere('lastname', 'LIKE', "%{$fullname[0]}%")
    		->orWhere('firstname', 'LIKE', "%{$fullname[1]}%")
    		->orWhere('lastname', 'LIKE', "%{$fullname[1]}%")
    		->groupBy('id')
    		->having('id', '!=', $user_id)
    		->orderBy('lastname', 'desc')
    		->get();
    }
}
