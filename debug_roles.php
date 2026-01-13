<?php
use Illuminate\Support\Facades\Auth;

// Assuming this is run in Tinker or a route closure
$user = \App\Models\User::find(Auth::id() ?? 1); // Fallback to ID 1 if not auth
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Roles: " . $user->getRoleNames() . "\n";
    echo "Has 'superadministrador'? " . ($user->hasRole('superadministrador') ? 'YES' : 'NO') . "\n";
    echo "Has 'Superadmin'? " . ($user->hasRole('Superadmin') ? 'YES' : 'NO') . "\n";
} else {
    echo "No user found.\n";
}
