<?php

namespace App\Http\Controllers;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraphController extends Controller
{
    private $api;
    public function __construct(Facebook $fb)
    {
        $this->middleware(function ($request, $next) use ($fb) {
            $fb->setDefaultAccessToken(Auth::user()->token);
            $this->api = $fb;
            return $next($request);
        });
    }
 
    public function retrieveUserProfile(){
        try {
 
            $params = "id,name";
 
            $user = $this->api->get('/me?fields='.$params)->getGraphUser();
 
            dd($user);
 
        } catch (FacebookSDKException $e) {
 			// dd($e);
        }
 
    }

    public function getPageAccessToken($page_id){
	    try {
	         // Get the \Facebook\GraphNodes\GraphUser object for the current user.
	         // If you provided a 'default_access_token', the '{access-token}' is optional.
	         $response = $this->api->get('/me/accounts', Auth::user()->token);
	    } catch(FacebookResponseException $e) {
	        // When Graph returns an error
	        echo 'Graph returned an error: ' . $e->getMessage();
	        exit;
	    } catch(FacebookSDKException $e) {
	        // When validation fails or other local issues
	        echo 'Facebook SDK returned an error: ' . $e->getMessage();
	        exit;
	    }
	 
	    try {
	        $pages = $response->getGraphEdge()->asArray();
	        foreach ($pages as $key) {
	            if ($key['id'] == $page_id) {
	                return $key['access_token'];
	            }
	        }
	    } catch (FacebookSDKException $e) {
	        dd($e); // handle exception
	    }
	}

	public function getPageInfo(){
        try {
 			
 			$page_id = '109319207558709';
            
            //for page info
            //$params = "id,name,link,about,category,can_post,has_added_app,tasks,engagement,rating_count";
            
            //for feeds page info
            //$params = "id,created_time,message,from,actions,admin_creator,icon,likes{id,name,profile_type,pic},can_reply_privately,shares,status_type,promotion_status";
            
            //for metric insights
            $metric ="page_impressions";
            
            //for page info
            //$user = $this->api->get('/me/accounts?fields='.$params);
 			
 			//for feeds page info
            // $page = $this->api->get('/'.$page_id.'/feed?fields='.$params, $this->getPageAccessToken($page_id));

 			$insight = $this->api->get('/'.$page_id.'/insights/'.$metric, $this->getPageAccessToken($page_id));
            
            // dd($page);
            dd($insight);
 
        } catch (FacebookSDKException $e) {
 			// dd($e);
        }
 
    }
}
