<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\User;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle($retorno = null)
    {

        return Socialite::driver('google')
        ->with(['redirect'=>'http://localhost:82/auth/google/callback'])
        ->scopes('https://www.googleapis.com/auth/drive.file')
        ->scopes('https://www.googleapis.com/auth/drive')
        ->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {

        try {
            $googleUser = Socialite::driver('google')->user();

            session(['googleUser'=>$googleUser]);


            if(session('retornar'))
            {
                return redirect(route(session('retornar')));
            }

            $finduser = User::where('google_id', $googleUser->id)->first();

            if($finduser){

                Auth::login($finduser);

                return redirect('/dashboard');

            }else{
                $finduserbyemail = User::where('email', $googleUser->email)->first();
                if ($finduserbyemail) {
                    $finduserbyemail->google_id = $googleUser->id;
                    $finduserbyemail->save();
                    Auth::login($finduserbyemail);
                    return redirect('/dashboard');
                }
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id'=> $googleUser->id,
                    'password' => encrypt(rand())
                ]);

                Auth::login($newUser);

                return redirect('/dashboard');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
