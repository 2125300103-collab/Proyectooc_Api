<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('inicio');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $datos = Validator::make($request->all(), [
            'usuario' => ['required', 'string', 'max:50', 'exists:administradores,usuario'],
            'contraseña' => ['required', 'string'],
        ])->validate();

        $administrador = Administrador::query()
            ->where('usuario', $datos['usuario'])
            ->first();

        if ($administrador && !$administrador->estado) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Esta cuenta se encuentra desactivada.',
                    'errors' => ['status' => ['Esta cuenta se encuentra desactivada.']],
                ], 422);
            }

            return back()
                ->withErrors(['status' => 'Esta cuenta se encuentra desactivada.'])
                ->withInput($request->only('usuario'));
        }

        if (
            $administrador
            && $administrador->estado
            && Hash::check($datos['contraseña'], $administrador->contraseña)
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'token_type' => 'Bearer',
                    'access_token' => $administrador->createToken('postman')->plainTextToken,
                ]);
            }

            Auth::guard('admin')->login($administrador);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos.',
                'errors' => ['error' => ['Usuario o contraseña incorrectos.']],
            ], 422);
        }

        return back()
            ->withErrors(['error' => 'Usuario o contraseña incorrectos.'])
            ->withInput($request->only('usuario'));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')
                ->withErrors(['google' => 'No se completó el inicio de sesión con Google.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')
                ->withErrors(['google' => 'La sesión de inicio con Google venció o cambió. Inténtalo de nuevo.']);
        }

        $email = $googleUser->getEmail();

        if (!$email || ($googleUser->getRaw()['email_verified'] ?? false) !== true) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google no proporcionó un correo verificado para esta cuenta.']);
        }

        $administrador = Administrador::query()->where('correo', $email)->first();

        if ($administrador && !$administrador->estado) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Esta cuenta administrativa se encuentra desactivada.']);
        }

        if ($administrador) {
            Auth::guard('admin')->login($administrador);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $cliente = Cliente::query()
            ->where('google_id', $googleUser->getId())
            ->first();

        if (!$cliente) {
            $cliente = Cliente::query()->where('correo', $email)->first();
        }

        if ($cliente && !$cliente->estado) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Esta cuenta de cliente se encuentra desactivada.']);
        }

        if ($cliente && $cliente->google_id && $cliente->google_id !== $googleUser->getId()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Este correo ya está asociado a otra cuenta de Google.']);
        }

        if (!$cliente) {
            $nombreCompleto = trim($googleUser->getName() ?: Str::before($email, '@'));
            $partesNombre = preg_split('/\s+/u', $nombreCompleto, 2) ?: [];

            $cliente = Cliente::query()->create([
                'nombres' => $partesNombre[0] ?? Str::before($email, '@'),
                'apellidos' => $partesNombre[1] ?? '',
                'correo' => $email,
                'google_id' => $googleUser->getId(),
                'contraseña' => Hash::make(Str::random(64)),
                'imagen' => $googleUser->getAvatar(),
                'estado' => true,
            ]);
        } elseif (!$cliente->google_id) {
            $cliente->update(['google_id' => $googleUser->getId()]);
        }

        Auth::guard('web')->login($cliente);
        $request->session()->regenerate();

        return redirect()->route('inicio');
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $administrador = $request->user();
            $token = $administrador instanceof Administrador
                ? $administrador->currentAccessToken()
                : null;

            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return response()->json(['message' => 'Sesión cerrada correctamente.']);
        }

        $usuario = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $usuario?->tokens()->delete();

        return redirect()->route('login');
    }
}
