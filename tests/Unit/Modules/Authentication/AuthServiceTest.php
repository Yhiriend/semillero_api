<?php

namespace Tests\Unit\Modules\Authentication;

use Tests\TestCase;
use App\Modules\Authentication\Services\AuthService;
use App\Modules\Authentication\Repositories\AuthRepository;
use App\Modules\Users\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Mockery;
use Mockery\MockInterface;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;
    protected $authRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock del repositorio
        $this->authRepository = Mockery::mock(AuthRepository::class);
        $this->authService = new AuthService($this->authRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_creates_user_and_returns_token()
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

        $user = new UserModel();
        $user->id = 1;
        $user->nombre = $userData['name'];
        $user->email = $userData['email'];
        $user->tipo = $userData['tipo'];
        $user->programa_id = $userData['programa_id'];

        $this->authRepository
            ->shouldReceive('createUser')
            ->once()
            ->andReturn($user);

        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->andReturn('fake-jwt-token');

        // Act
        $result = $this->authService->register($userData);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals('fake-jwt-token', $result['token']);
    }

    public function test_login_returns_token_for_valid_credentials()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        
        $user = new UserModel();
        $user->id = 1;
        $user->email = $email;
        $user->contraseña = Hash::make($password);

        $this->authRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($user);

        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->andReturn('fake-jwt-token');

        // Act
        $result = $this->authService->login($email, $password);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals('fake-jwt-token', $result['token']);
    }

    public function test_login_throws_exception_for_invalid_email()
    {
        // Arrange
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->authRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Act
        $this->authService->login($email, $password);
    }

    public function test_login_throws_exception_for_invalid_password()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrongpassword';
        
        $user = new UserModel();
        $user->email = $email;
        $user->contraseña = Hash::make('correctpassword');

        $this->authRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($user);

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Act
        $this->authService->login($email, $password);
    }

    public function test_refresh_token_returns_new_token()
    {
        // Arrange
        $user = new UserModel();
        $user->id = 1;
        $user->email = 'test@example.com';

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        JWTAuth::shouldReceive('getToken')
            ->once()
            ->andReturn('old-token');

        JWTAuth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        JWTAuth::shouldReceive('refresh')
            ->once()
            ->andReturn('new-token');

        // Act
        $result = $this->authService->refreshToken();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals('new-token', $result['token']);
    }

    public function test_logout_calls_auth_logout()
    {
        // Arrange
        Auth::shouldReceive('logout')
            ->once();

        // Act
        $this->authService->logout();

        // No assertion needed as we're just verifying the method was called
    }

    public function test_me_returns_current_user()
    {
        // Arrange
        $user = new UserModel();
        $user->id = 1;
        $user->email = 'test@example.com';

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->authService->me();

        // Assert
        $this->assertEquals($user, $result);
    }
} 