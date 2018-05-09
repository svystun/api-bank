<?php namespace App\Http\Controllers;

use App\User;
use App\Mail\VerifyMail;
use App\Jobs\NewCustomer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\{Mail, Validator};
use App\Http\Resources\User as UserResource;

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
     * @return \App\Http\Resources\User
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

        return new UserResource($this->respondWithToken($token));
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
     * @return \App\Http\Resources\User
     */
    public function refresh()
    {
        return new UserResource($this->respondWithToken(JWTAuth::refresh()));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return \stdClass
     */
    protected function respondWithToken($token = '')
    {
        $data = new \stdClass();
        $data->access_token = $token;
        return $data;
    }
}
