<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;

class OAuthController extends Controller
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    private $provider;
    private $google_options;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->client_id        = config('services.google.client_id');
        $this->client_secret    = config('services.google.client_secret');
        $this->redirect_uri     = route('token.success');
        $this->google_options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        $params = [
            'clientId'      => $this->client_id,
            'clientSecret'  => $this->client_secret,
            'redirectUri'   => $this->redirect_uri,
            'accessType'    => 'offline'
        ];

        // Create Google Provider
        $this->provider = new Google($params);
    }

    /**
     * Generate url to retreive token
     */
    public function doGenerateToken()
    {
        $redirect_uri = $this->provider->getAuthorizationUrl($this->google_options);
        return redirect($redirect_uri);
    }

    /**
     * Retreive Token
     */
    public function doSuccessToken(Request $request)
    {
        $code = $request->get('code');

        try {
            // Generate Token From Code
            $tokenObj = $this->provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $code
                ]
                );
                $token = $tokenObj->getToken();
                $refresh_token = $tokenObj->getRefreshToken();
                if( $refresh_token != null && !empty($refresh_token) ) {
                    session(['tokengoogle'=>$refresh_token]);
                    return redirect(route('google.dashboard'))->with('token', $refresh_token);
                } elseif ( $token != null && !empty($token) ) {
                    session(['tokengoogle'=>$token]);
                    return redirect(route('google.dashboard'))->with('token', $token);
                } else {
                    session(['tokengoogle'=>false]);
                    return redirect(route('google.dashboard'))->with('error', 'Não recebeu o token.');
                }
        } catch(IdentityProviderException $e) {
            return redirect(route('mail.home'))->with('error', 'Exception: ' . $e->getMessage());
        } catch(Exception $e) {
            return redirect(route('mail.home'))->with('error', 'Exception: ' . $e->getMessage());
        }
    }
}
