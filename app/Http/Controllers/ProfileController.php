<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Actualiza los campos validados
        $user->fill($request->validated());

        // Si el email cambió, se invalida la verificación
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Manejo de la imagen de perfil
        if ($request->hasFile('foto_perfil')) {
            $foto = $request->file('foto_perfil');
            $path = $foto->store('foto_perfil_usuarios', 'public');

            // Elimina la imagen anterior si existe
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $user->foto_perfil = $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}