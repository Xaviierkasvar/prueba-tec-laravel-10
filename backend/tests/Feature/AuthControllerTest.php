<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un usuario de prueba
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'username'
                ]
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Credenciales inválidas'
            ]);
    }

    public function test_login_validation_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password'])
            ->assertJson([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => [
                    'email' => ['El correo electrónico es obligatorio'],
                    'password' => ['La contraseña es obligatoria']
                ]
            ]);
    }

    public function test_user_can_logout()
    {
        $token = auth()->login($this->user);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sesión cerrada exitosamente'
            ]);
    }

    public function test_user_can_refresh_token()
    {
        $token = auth()->login($this->user);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);
    }

    public function test_user_can_get_profile()
    {
        $token = auth()->login($this->user);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/profile');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }

    public function test_user_can_register_successfully()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'access_token',
                'token_type',
                'expires_in'
            ]);

        // Verificar que el usuario se haya creado en la base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        // Crear un usuario existente
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonFragment([
                'errors' => [
                    'email' => ['El correo electrónico ya está registrado']
                ]
            ]);
    }

    public function test_user_cannot_register_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_password_confirmation_must_match()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'errors' => [
                    'password' => ['La confirmación de contraseña no coincide']
                ]
            ]);
    }

    public function test_registered_user_receives_jwt_token()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
        
        // Verificar que se devuelve un token de acceso
        $this->assertArrayHasKey('access_token', $response->json());
    }
}