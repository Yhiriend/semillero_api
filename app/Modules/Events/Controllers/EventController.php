<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Services\EventService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    use ApiResponse;

    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function getRegisteredUsers(int $eventId): JsonResponse
    {
        try {
            return $this->eventService->getRegisteredUsers($eventId);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los usuarios inscritos: ' . $e->getMessage());
        }
    }
} 