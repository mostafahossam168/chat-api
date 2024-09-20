<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgetPassword', 'resetPassword', 'changePassword']]);
    }

    public function login(Request $request)
    {
        $valiadtion = Validator::make(
            $request->all(),
            [
                'email' => "required|string|email|max:255",
                'password' => "required|string"
            ]
        );
        if ($valiadtion->fails()) {
            return errorResponse($valiadtion->errors(), 401);
        }
        $credentials = request(['email', 'password']);
        if ($token = auth()->attempt($credentials)) {
            $data = $this->respondWithToken($token);
            return successResponse($data, 'تم تسجيل الدخول بنجاح');
        }
        return errorResponse('البيانات غير صحيحه', 401);
    }
    public function register(Request $request)
    {
        $valiadtion = Validator::make(
            $request->all(),
            [
                'email' => "required|string|email|unique:users,email|max:255",
                'name' => "required|string|max:255",
                'password' => "required|string|min:5|max:255|confirmed",
            ]
        );
        if ($valiadtion->fails()) {
            return errorResponse($valiadtion->errors(), 401);
        }
        $data = $request->except('password');
        $data['password'] = bcrypt($request->password);

        $user = User::create($data);
        $user = auth()->login($user);
        $token = $this->respondWithToken($user);
        return successResponse($token, 'تم انشاء الحساب بنجاح');
    }


    public function profile()
    {
        return successResponse(auth()->user());
    }


    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $valiadtion = Validator::make(
            $request->all(),
            [
                'email' => "required|string|email|max:255|unique:users,email," . $user->id,
                'name' => "required|string|max:255",
                'password' => "nullable|string|min:5|max:255|confirmed",
            ]
        );
        if ($valiadtion->fails()) {
            return errorResponse($valiadtion->errors(), 401);
        }
        $data = $request->except('password');
        if ($request->has('password') && $request->password != null) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        return successResponse($user, 'تم تحديث البيانات بنجاح');
    }


    public function logout()
    {
        auth()->logout();
        return successResponse('', 'تم تسجيل الخروج بنجاح');
    }

    public function refresh()
    {
        return successResponse($this->respondWithToken(auth()->refresh()));
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 240
        ]);
    }
}