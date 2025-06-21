<?php
namespace App\Modules\Reports\Controllers;
use Illuminate\Routing\Controller; 
use App\Modules\Reports\Services\SeedbedsReportService;
use Illuminate\Http\Request;
use App\Enums\ResponseCode;
use Illuminate\Http\JsonResponse;

class SeedbedsReportController extends Controller
{
    protected $semilleroService;

    public function __construct(SeedbedsReportService $semilleroService)
    {
        $this->semilleroService = $semilleroService;
    }

    public function index(Request $request)
    {
        $universidadId = $request->query('universidad');
        $facultadId = $request->query('facultad');
        $programaId = $request->query('programa');

        if (!$universidadId) {
            return response()->json(['error' => 'Se requiere el parámetro universidad'], 400);
        }

        $data = $this->semilleroService->getSemillerosPorFiltros($universidadId, $facultadId, $programaId);

        return response()->json($data);
    }

    public function getUsersBySeedbed(int $semilleroId): JsonResponse
    {
        $result = $this->semilleroService->getUsersBySeedbed($semilleroId);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 404);
        }

        return response()->json($result);
    }

    public function getAllSeedbeds(): JsonResponse
    {
        $semilleros = $this->semilleroService->getAllSemilleros();
        return response()->json($semilleros);
    }

}
