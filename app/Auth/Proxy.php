<?php

namespace App\Auth;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Response as IlluminateResponse;

class Proxy {
	/*
	public function attemptLogin(Request $request) {
		$credentials = $request->get('credentials');
		print_r($credentials);
		exit;
		return $this->proxy('password', $credentials);
	}*/
	
    public function attemptLogin($credentials)
    {
         return  $this->proxy('password', $credentials);
    }

    public function attemptRefresh()
    {
    	$crypt = app()->make('encrypter');
    	$request = app()->make('request');
    	
    	$refresh_token = $request->input('refresh_token');
    	$username  = $request->input('username');

    	return $this->proxy('refresh_token', [
    			'refresh_token' => $crypt->decrypt($refresh_token),
    			'username' => $username
    	]);
    	        
        //return $this->proxy('refresh_token', [
            //'refresh_token' => $crypt->decrypt($request->cookie('refreshToken'))
        //]);
    }

	private function proxy($grantType, array $data = [])
    {   
    	if ($grantType == 'password') {
	    	$member = DB::table('ut_member')
	    				->where('email', $data['username'])
	    				->orWhere('phone', $data['username'])    	
	    				->select('uid', 'username', 'password', 'salt', 'status')->first();
	    	if (empty($member)) {
	    		return response()->json([
		    				'message'     => '验证失败',
		    				'code' => IlluminateResponse::HTTP_UNAUTHORIZED
	    				],
	    				IlluminateResponse::HTTP_UNAUTHORIZED,
	    				$headers = []
	    		);
	    	}
	    	$memberpwd = md5(md5($data['password']).$member->salt);
	    	if ($memberpwd != $member->password) {
	    		return response()->json([    				
		    				'message'     => '验证失败',
		    				'code' => IlluminateResponse::HTTP_UNAUTHORIZED
	    				],
	    				IlluminateResponse::HTTP_UNAUTHORIZED,
	    				$headers = []
	    		);
	    	}    	
	    	$user = DB::table('users')
		    	->leftJoin('ut_member_users', 'ut_member_users.users_id', '=', 'users.id')
		    	->where('users.email', $data['username'])->select('ut_member_users.member_uid AS id', 'users.name', 'users.email')
		    	->first();    	
	    	if (empty($user)) {
	    		$hasher = app()->make('hash');
	    		$users_id = DB::table('users')->insertGetId([
	    			'name' => $member->username,
	    			'email' =>  $data['username'],
	    			'password' => $hasher->make($data['password'])
	    		]);
	    		//与真实用户表关联
	    		DB::table('ut_member_users')->insert([
	    				'users_id' => $users_id, 
	    				'member_uid' => $member->uid
	    		]);
	    		$user = (object)array('id' => $member->uid, 'name'=>$member->username, 'email'=> $data['username']);
	    	}
    	} else {
    		$user = DB::table('users')
    		->leftJoin('ut_member_users', 'ut_member_users.users_id', '=', 'users.id')
    		->where('users.email', $data['username'])->select('ut_member_users.member_uid AS id', 'users.name', 'users.email')
    		->first();
    	}
        try {
            $config = app()->make('config');
            $data = array_merge([
                'client_id'     => $config->get('secrets.client_id'),
                'client_secret' => $config->get('secrets.client_secret'),
                'grant_type'    => $grantType
            ], $data);
            
            $client = new Client();
            $guzzleResponse = $client->post(sprintf('%s/oauth/access-token', $config->get('app.url')), [
                'form_params' => $data
            ]);
        } catch(\GuzzleHttp\Exception\BadResponseException $e) {
           $guzzleResponse = $e->getResponse();
        }
        $response = json_decode($guzzleResponse->getBody());

        if (property_exists($response, "access_token")) {        	
            $cookie = app()->make('cookie');
            $crypt  = app()->make('encrypter');
            $encryptedToken = $crypt->encrypt($response->refresh_token);
            // Set the refresh token as an encrypted HttpOnly cookie
            $cookie->queue('refreshToken',
                $encryptedToken,
                604800, // expiration, should be moved to a config file
                null,
                null,
                false,
                true // HttpOnly
            );
            $response = [
            	'user'									=> $user,
                'accessToken'            		=> $response->access_token,
                'accessTokenExpiration'  => $response->expires_in,
                'refreshToken'					=> $encryptedToken
            ];
        }        
        $response = response()->json([
	        		'message'     => '验证成功',
	        		'data'	=> $response,
	        		'code' => IlluminateResponse::HTTP_OK
        		],
        		IlluminateResponse::HTTP_OK,
        		$headers = []
        );        
        //$response = response()->json($response);
        $response->setStatusCode($guzzleResponse->getStatusCode());
        $headers = $guzzleResponse->getHeaders();
        foreach($headers as $headerType => $headerValue) {
            $response->header($headerType, $headerValue);
        }
        return $response;
    }

}
