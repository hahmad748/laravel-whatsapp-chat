<?php

namespace DevsFort\LaravelWhatsappChat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastingAuthController extends Controller
{
    /**
     * Authenticate broadcasting channels
     */
    public function authenticate(Request $request)
    {
        // For now, we'll allow all authenticated users to access broadcasting
        // In a more complex setup, you might want to check specific permissions

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Return the user's socket ID and any additional data needed for broadcasting
        return response()->json([
            'user_id' => $user->id,
            'user_info' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type ?? 'user'
            ]
        ]);
    }
}
