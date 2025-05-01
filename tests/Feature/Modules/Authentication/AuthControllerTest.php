<?php

namespace Tests\Feature\Modules\Authentication;

use Tests\TestCase;
use App\Modules\Users\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un programa de prueba
        DB::table('programa')->insert([
            'nombre' => 'Programa de Prueba',
            'descripcion' => 'Descripción del programa de prueba',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_register_creates_new_user()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tipo' => 'estudiante',
            'programa_id' => 1
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('usuario', [
            'email' => $userData['email'],
            'tipo' => $userData['tipo']
        ]);
    }

    public function test_register_validates_required_fields()
    {
        // Act
        $response = $this->postJson('/api/auth/register', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'tipo']);
    }

    public function test_login_returns_token_for_valid_credentials()
    {
        // Arrange
        $user = UserModel::create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => Hash::make('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token'
                ]
            ]);
    }

    public function test_login_returns_error_for_invalid_credentials()
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_returns_authenticated_user()
    {
        // Arrange
        $user = UserModel::create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => Hash::make('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'nombre',
                    'email',
                    'tipo',
                    'programa_id'
                ]
            ]);
    }

    public function test_logout_invalidates_token()
    {
        // Arrange
        $user = UserModel::create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => Hash::make('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(200);

        // Verify token is invalidated
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me')
            ->assertStatus(401);
    }

    public function test_refresh_token_returns_new_token()
    {
        // Arrange
        $user = UserModel::create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => Hash::make('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/refresh');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token'
                ]
            ]);

        // Verify new token works
        $newToken = $response->json('data.token');
        $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/auth/me')
            ->assertStatus(200);
    }
} 