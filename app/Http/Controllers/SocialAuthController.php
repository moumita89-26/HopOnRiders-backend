<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Session;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        //return Socialite::driver($provider)->redirect();
        $consumerKey = env('TWITTER_CONSUMER_KEY');
        $consumerSecret = env('TWITTER_CONSUMER_SECRET');
        $connection = new TwitterOAuth($consumerKey, $consumerSecret);
        $requestToken = $connection->oauth('oauth/request_token', [
            'oauth_callback' => env('TWITTER_CALLBACK_URL'),
        ]);
        Session::put('oauth_token', $requestToken['oauth_token']);
        Session::put('oauth_token_secret', $requestToken['oauth_token_secret']);
        $url = $connection->url('oauth/authorize', [
            'oauth_token' => $requestToken['oauth_token'],
        ]);

        return redirect($url);
    }

    public function handleProviderCallback($provider)
    {

        $consumerKey = env('TWITTER_CONSUMER_KEY');
        $consumerSecret = env('TWITTER_CONSUMER_SECRET');

        $connection = new TwitterOAuth(
            $consumerKey,
            $consumerSecret,
            Session::get('oauth_token'),
            Session::get('oauth_token_secret')
        );

        $accessToken = $connection->oauth("oauth/access_token", [
            "oauth_verifier" => request('oauth_verifier')
        ]);
        Session::put('access_token', $accessToken);
        $user = $connection->get("account/verify_credentials", ['include_email' => 'true']);

        // Check for errors in user retrieval
        if ($connection->getLastHttpCode() == 200) {
            // Successfully retrieved user data
            dd($user); // Inspect the user object here
        } else {
            // Handle error retrieving user data
            dd($connection->getLastBody());
        }
        die();
        try {
            $user = Socialite::driver($provider)->user();
            // Access token and secret
            $accessToken = $user->token;
            $accessTokenSecret = $user->tokenSecret;

            // You can now store the user details and tokens in your database

            // Redirect to your frontend or send a response
            return redirect('/home')->with('success', 'Logged in successfully!');
        } catch (\Exception $e) {
            dd($e);
            return redirect('/')->with('error', 'Failed to login: ' . $e->getMessage());
        }
    }

    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback()
    {
        $facebookUser = Socialite::driver('facebook')->user();

        $user = User::where('facebook_id', $facebookUser->getId())->first();

        if (!$user) {
            $user = User::create([
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'facebook_id' => $facebookUser->getId(),
                // Add other fields as necessary
            ]);
        }

        // Create a token or session for the user
        // You might want to return a JWT token here
        return response()->json($user);
    }
}
