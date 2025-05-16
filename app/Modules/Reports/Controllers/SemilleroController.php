<?php
namespace App\Modules\Reports\Controllers;
use Illuminate\Routing\Controller; 
use App\Modules\Reports\Services\SemilleroService;
use Illuminate\Http\Request;

class SemilleroController extends Controller
{
    protected $semilleroService;

    public function __construct(SemilleroService $semilleroService)
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
}
