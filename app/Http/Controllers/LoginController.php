<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTime;

class LoginController extends Controller
{
    /**
     * Handle user login via email/password or Google token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'nullable|string',
            'google_token' => 'nullable|string',
        ]);

        $email = $request->email;
        $password = $request->password;
        $googleToken = $request->google_token;

        // Fetch user from DB
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 401);
        }

        $isAuthenticated = false;

        // Google login
        if ($googleToken && $user->login_type === 'google') {
            $isAuthenticated = $this->verifyGoogleToken($googleToken, $user->email);
            if ($isAuthenticated) {
                DB::table('users')->where('id', $user->id)
                    ->update(['google_token' => $googleToken]);
            }
        }
        // Password login
        else if ($password) {
            $isAuthenticated = Hash::check($password, $user->password);
        }

        if (!$isAuthenticated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Remove sensitive info
        $userArray = (array) $user;
        unset($userArray['password'], $userArray['google_token']);

        // Generate token
        $accessToken = $this->generateAccessToken($user->id);
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        // Log login
        DB::table('user_logins')->insert([
            'user_id' => $user->id,
            'login_time' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $userArray,
            'access_token' => $accessToken,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string',
            'photo' => 'nullable|string',
            'password' => 'nullable|string',
            'telephone' => 'nullable|string',
            'login_type' => 'nullable|string|in:local,google,facebook',
            'google_token' => 'nullable|string',
        ]);

        $data = $request->only([
            'first_name', 'last_name', 'email', 'username',
            'photo', 'password', 'telephone', 'login_type', 'google_token'
        ]);

        // Hash password if local login
        if (($data['login_type'] ?? 'local') === 'local' && empty($data['password'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password is required for local login'
            ], 400);
        }

        if (($data['login_type'] ?? '') === 'google' && empty($data['google_token'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Google token is required for Google login'
            ], 400);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['date_added'] = now();
        $data['date_edited'] = now();

        DB::table('users')->insert($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully'
        ], 201);
    }

    /**
     * Regenerate access token for a user
     */
    public function regenerateToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $newToken = $this->generateAccessToken($request->user_id);

        return response()->json([
            'access_token' => $newToken
        ]);
    }

    /**
     * Generate access token and store in DB
     */
    private function generateAccessToken($userId)
    {
        $token = bin2hex(random_bytes(16));
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        DB::table('tokens')->insert([
            'user_id' => $userId,
            'access_token' => $token,
            'expires_at' => $expiresAt
        ]);

        return $token;
    }

    /**
     * Verify Google token via Firebase
     */
    private function verifyGoogleToken($token, $email)
    {
        try {
            $publicKeysUrl = config('firebase.keys_url');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $publicKeysUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $keys = curl_exec($ch);
            curl_close($ch);
            $publicKeys = json_decode($keys, true);

            $header = json_decode(base64_decode(explode('.', $token)[0]), true);
            if (!isset($header['kid']) || !isset($publicKeys[$header['kid']])) {
                return false;
            }

            $payload = JWT::decode($token, new Key($publicKeys[$header['kid']], 'RS256'));

            return isset($payload->email, $payload->email_verified) &&
                $payload->email === $email &&
                $payload->email_verified === true;
        } catch (\Exception $e) {
            \Log::error('Firebase verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
