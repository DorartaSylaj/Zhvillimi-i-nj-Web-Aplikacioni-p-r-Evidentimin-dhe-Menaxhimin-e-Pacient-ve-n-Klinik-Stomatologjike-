<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Regjistrimi i përdoruesit të ri
    public function register(Request $request)
    {
        // Validimi i të dhënave hyrëse
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Krijo përdoruesin
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        // Krijo token për përdoruesin e sapo regjistruar
        $token = $user->createToken('patientapptoken')->plainTextToken;

        // Kthe përgjigjen JSON
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // Login-i i përdoruesit ekzistues
    public function login(Request $request)
    {
        // Validimi i të dhënave hyrëse
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Gjej përdoruesin me email-in e dhënë
        $user = User::where('email', $fields['email'])->first();

        // Kontrollo nëse përdoruesi ekziston dhe password-i është i saktë
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Krijo token të ri për sesionin e login-it
        $token = $user->createToken('patientapptoken')->plainTextToken;

        // Kthe përgjigjen JSON
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // Logout - fshij tokenin aktiv të përdoruesit
    public function logout(Request $request)
    {
        // Fshij të gjithë tokenat e përdoruesit që bën logout
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
