<?php
namespace App\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use DB;
use Helpers\SecurityHelper;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Lang;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.

        $tokenExists = DB::table('password_resets')
            ->where('token', $request['token'])
            ->first();
        
        if ($tokenExists && !filter_var($tokenExists->username, FILTER_VALIDATE_EMAIL)) {
            $codeExists = DB::table('password_resets')
                ->where('token', $request['token'])
                ->where('reset_code', $request['reset_code'])
                ->first();

            if (!$codeExists) {
                return response()->json(['error' => 'wrong code'], 400);
            }
        }else if ($tokenExists) {
            $request->request->add(['username'=> $tokenExists->username]);
        }

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            });

        return $response == Password::PASSWORD_RESET
        ? $this->sendResetResponse($response)
        : $this->sendResetFailedResponse($request, $response);
    }

    public function codeValid(Request $request)
    {

        $tokenExists = DB::table('password_resets')
            ->where('token', $request['token'])
            ->where('created_at','>',Carbon::now()->subHours(config('auth.passwords.users.expire')/60))
            ->first();

        if ($tokenExists) {
            $codeExists = DB::table('password_resets')
                ->where('token', $request['token'])
                ->where('reset_code', $request['reset_code'])
                ->first();

            if (!$codeExists) {
                return response()->json(['error' => 'This Code is invalid'], 400);
            }
            else{
                return response()->json(['message' => 'true'], 200); 
            }
        }
        else{
            return response()->json(['error' => 'This Code is invalid'], 400);
        }
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'username', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => SecurityHelper::getHashedPassword($password),
        ])->save();
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return \Illuminate\Http\Response
     */
    protected function sendResetResponse($response)
    {
        return \Response::json(['message' => ['message'=>Lang::get($response,[],'en')
        ,'message_ar'=>Lang::get($response,[],'ar')]], 200);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {

        return \Response::json(['error' => ['message'=>Lang::get($response,[],'en')
        ,'message_ar'=>Lang::get($response,[],'ar')]], 400);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

}
