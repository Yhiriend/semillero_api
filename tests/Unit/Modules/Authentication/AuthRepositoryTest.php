<?php

namespace Tests\Unit\Modules\Authentication;

use Tests\TestCase;
use App\Modules\Authentication\Repositories\AuthRepository;
use App\Modules\Users\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AuthRepository $authRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authRepository = new AuthRepository();
    }

    public function test_create_user_successfully()
    {
        // Arrange
        $userData = [
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => bcrypt('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ];

        // Act
        $user = $this->authRepository->createUser($userData);

        // Assert
        $this->assertInstanceOf(UserModel::class, $user);
        $this->assertEquals($userData['nombre'], $user->nombre);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['tipo'], $user->tipo);
        $this->assertEquals($userData['programa_id'], $user->programa_id);
        $this->assertDatabaseHas('usuario', [
            'email' => $userData['email'],
            'tipo' => $userData['tipo']
        ]);
    }

    public function test_find_by_email_returns_user()
    {
        // Arrange
        $userData = [
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contraseña' => bcrypt('password123'),
            'tipo' => 'estudiante',
            'programa_id' => 1
        ];

        $createdUser = $this->authRepository->createUser($userData);

        // Act
        $foundUser = $this->authRepository->findByEmail($userData['email']);

        // Assert
        $this->assertInstanceOf(UserModel::class, $foundUser);
        $this->assertEquals($createdUser->id, $foundUser->id);
        $this->assertEquals($userData['email'], $foundUser->email);
    }

    public function test_find_by_email_returns_null_for_nonexistent_email()
    {
        // Act
        $user = $this->authRepository->findByEmail('nonexistent@example.com');

        // Assert
        $this->assertNull($user);
    }
} 