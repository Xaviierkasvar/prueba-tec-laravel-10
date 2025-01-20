<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Permite a los usuarios registrarse proporcionando nombre, email y contraseña",
     *     operationId="register",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="access_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'El correo electrónico ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth()->login($user);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], 201);
    }
    
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Iniciar sesión y obtener un token JWT",
     *     description="Permite a los usuarios iniciar sesión proporcionando su email y contraseña.",
     *     operationId="login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Credenciales inválidas")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El formato del correo electrónico no es válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión",
     *     security={{"bearerAuth":{}}},
     *     description="Cierra la sesión del usuario actual invalidando su token JWT.",
     *     operationId="logout",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Actualizar el token JWT",
     *     security={{"bearerAuth":{}}},
     *     description="Permite a los usuarios actualizar su token JWT si el token actual es válido.",
     *     operationId="refresh",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Token actualizado exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No se pudo actualizar el token.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Token no puede ser actualizado")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token no puede ser actualizado'], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Obtener información del perfil del usuario",
     *     security={{"bearerAuth":{}}},
     *     description="Devuelve los datos del usuario autenticado.",
     *     operationId="profile",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil del usuario autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Responde con el token JWT.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth()->user();
        
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username
            ]
        ]);
    }
}
