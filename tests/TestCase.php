<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Deshabilitar VACUUM para SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->disableQueryLog();
        }

        // Configurar JWTAuth para pruebas
        config(['jwt.secret' => 'test-key-please-change']);
        JWTAuth::parser()->setRequest(request());
    }
}
