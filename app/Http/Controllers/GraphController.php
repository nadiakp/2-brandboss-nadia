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
 
    public function getInfo(){
        try {
 
            $params_user = "id,name";
            
            $params_page_info = "accounts{id,name,link,about,category,can_post,has_added_app,rating_count}";

            $params_page_feed = "id,message,created_time,from,admin_creator,icon,shares,status_type,promotion_status,likes{id,name,profile_type,pic},actions,can_reply_privately";

            //for metric insights
            $metric ="page_impressions";

            $user = $this->api->get('/me?fields='.$params_user)->getGraphUser();

            $page_info = $this->api->get('/me?fields='.$params_page_info)->getGraphUser();

            // $page_id = '109319207558709';
            $page_id = $page_info['accounts']['0']['id'];  

            $page_feed = $this->api->get('/'.$page_id.'/feed?fields='.$params_page_feed, $this->getPageAccessToken($page_id))->getGraphEdge()->asArray();

            $insights = $this->api->get('/'.$page_id.'/insights/'.$metric, $this->getPageAccessToken($page_id))->getGraphEdge()->asArray();

            // dd($page);

            return response()->json([
                'user info' =>[
                    'user id' => $user['id'],
                    'user name' => $user['name'],
                ],
                'page info' =>[
                    'page id' => $page_info['accounts']['0']['id'],
                    'page name' => $page_info['accounts']['0']['name'],
                    'page link' => $page_info['accounts']['0']['link'],
                    'page about' => $page_info['accounts']['0']['about'],
                    'page category' => $page_info['accounts']['0']['category'],
                    'can post' => $page_info['accounts']['0']['can_post'],
                    'has added app' => $page_info['accounts']['0']['has_added_app'],
                    'rating_count' => $page_info['accounts']['0']['rating_count'],
                ],
                'page feeds' => $page_feed,
                'page insights' =>[
                    'page impressions per day' => $insights['0'],
                    'page impressions per week' => $insights['1'],
                    'page impressions per months' => $insights['2'],
                ],
            ], 200);
 
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

}
