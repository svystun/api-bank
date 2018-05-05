<?php namespace App\Http\Controllers;

use App\User;
use App\Mail\VerifyMail;
use App\Jobs\NewCustomer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\{JWTAuth, JWTFactory};
use Illuminate\Support\Facades\{Mail, Validator};

/**
 * Class ApiAuthController
 * @package App\Http\Controllers
 */
class ApiAuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required',
            'password'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'activation_code' => md5(microtime()),
            'password' => bcrypt($request->get('password')),
        ]);

        Mail::to($user)->send(new VerifyMail($user));

        return response()->json([
            'activation_url' => route('activate', ['code' => $user->activation_code])
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if (! $token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout
     */
    public function logout()
    {
        JWTAuth::invalidate();
        return response([
            'status' => 'success',
            'message' => 'Logged out Successfully.'
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|alpha_num|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $message = 'Sorry, your code cannot be identified.';
        if ($user = User::where('activation_code', $request->input('code'))->first()) {
            $message = "Your code is already verified. You can do login.";
            if (! $user->status) {
                $user->status = 1;
                $user->save();
                $message = "Your code has been verified. You can do login.";
                dispatch(new NewCustomer($user));
            }
        }

        return response([
            'status' => 'success',
            'message' => $message
        ], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token = '')
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTFactory::getTTL() * 60
        ]);
    }
}
