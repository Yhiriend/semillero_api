<?php
namespace App\Modules\GestionDeSemilleros\Controllers;
use App\Http\Controllers\Controller;

use App\Http\Resources\ProyectoResource;
use App\Modules\GestionDeSemilleros\Models\Evaluacion;
use App\Modules\GestionDeSemilleros\Models\Proyecto;
use App\Modules\GestionDeSemilleros\Models\User;
use App\Modules\GestionDeSemilleros\Models\Semillero;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/proyectos",
     *     operationId="getProyectosPorSemillero",
     *     tags={"Proyectos"},
     *     summary="Consultar proyectos de un semillero",
     *     description="Devuelve todos los proyectos asociados a un semillero específico con información detallada",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="semillero_id",
     *         in="query",
     *         description="ID del semillero",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar por estado del proyecto",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"activo", "inactivo", "finalizado", "en_revision"},
     *             example="activo"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="incluir_evaluaciones",
     *         in="query",
     *         description="Incluir evaluaciones de los proyectos",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de proyectos del semillero",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total_proyectos", type="integer", example=5),
     *                 @OA\Property(property="semillero", type="string", example="Semillero de IA")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Semillero no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Semillero no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'semillero_id'         => 'required|integer|exists:Semillero,id',
            'estado'               => 'sometimes|string|in:activo,inactivo,finalizado,en_revision',
            'incluir_evaluaciones' => 'sometimes|in:true,false,1,0',
        ]);

        // Verificar existencia del semillero
        $semillero = Semillero::find($validated['semillero_id']);
        if (! $semillero) {
            return response()->json(['message' => 'Semillero no encontrado'], 404);
        }

        // Construir query base
        $query = Proyecto::where('semillero_id', $validated['semillero_id'])
            ->with(['lider', 'coordinador', 'semillero']);

        // Filtrar por estado si está presente
        if ($request->has('estado')) {
            $query->where('estado', $validated['estado']);
        }

        // Incluir evaluaciones si se solicita
        if ($request->boolean('incluir_evaluaciones')) {
            $query->with(['evaluaciones' => function ($query) {
                $query->orderBy('fecha_creacion', 'desc');
            }]);
        }

        // Paginación y ordenamiento
        $proyectos = $query->orderBy('fecha_creacion', 'desc')
            ->paginate(15);

        // Respuesta enriquecida
        return response()->json([
            'data' => ProyectoResource::collection($proyectos),
            'meta' => [
                'total_proyectos' => $proyectos->total(),
                'semillero'       => $semillero->nombre,
                'coordinador'     => $semillero->coordinador->nombre ?? 'Sin coordinador',
            ],
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/proyectos",
     *     operationId="storeProyecto",
     *     tags={"Proyectos"},
     *     summary="Registrar nuevo proyecto en un semillero (sin autenticación)",
     *     description="Registra un proyecto asociado a un semillero con todos los campos requeridos.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","semillero_id","descripcion","lider_id","coordinador_id","estado","fecha_inicio","fecha_fin"},
     *             @OA\Property(property="titulo", type="string", example="Proyecto de Energía Solar"),
     *             @OA\Property(property="descripcion", type="string", example="Desarrollo de paneles solares para zonas rurales."),
     *             @OA\Property(property="semillero_id", type="integer", example=1),
     *             @OA\Property(property="lider_id", type="integer", example=5),
     *             @OA\Property(property="coordinador_id", type="integer", example=3),
     *             @OA\Property(property="estado", type="string", example="Activo"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2025-05-09"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proyecto registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="titulo", type="string", example="Proyecto de Energía Solar"),
     *             @OA\Property(property="descripcion", type="string", example="Desarrollo de paneles solares para zonas rurales."),
     *             @OA\Property(property="semillero_id", type="integer", example=1),
     *             @OA\Property(property="lider_id", type="integer", example=5),
     *             @OA\Property(property="coordinador_id", type="integer", example=3),
     *             @OA\Property(property="estado", type="string", example="Activo"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date", example="2025-05-09"),
     *             @OA\Property(property="fecha_creacion", type="string", example="2025-05-09T18:30:00.000000Z"),
     *             @OA\Property(property="fecha_actualizacion", type="string", example="2025-05-09T18:30:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'         => 'required|string|max:255',
            'descripcion'    => 'required|string',
            'semillero_id'   => 'required|exists:semillero,id',
            'lider_id'       => 'required|integer',
            'coordinador_id' => 'required|integer',
            'estado'         => 'required|string|max:50',
            'fecha_inicio'   => 'required|date',
        ]);

        $proyecto = Proyecto::create([
            'titulo'              => $request->titulo,
            'descripcion'         => $request->descripcion,
            'semillero_id'        => $request->semillero_id,
            'lider_id'            => $request->lider_id,
            'coordinador_id'      => $request->coordinador_id,
            'estado'              => $request->estado,
            'fecha_inicio'        => $request->fecha_inicio,
            'fecha_creacion'      => now(),
            'fecha_actualizacion' => now(),
        ]);

        return (new ProyectoResource($proyecto))->response()->setStatusCode(201);
    }

/**
 * @OA\Post(
 *     path="/api/proyectos/{id}/asignar-estudiantes",
 *     summary="Asignar estudiantes a un proyecto",
 *     description="Permite asignar estudiantes a un proyecto específico.",
 *     tags={"Proyectos"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del proyecto",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"estudiantes"},
 *                 @OA\Property(
 *                     property="estudiantes",
 *                     type="array",
 *                     @OA\Items(type="integer", example=1)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Estudiantes asignados correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Estudiantes asignados correctamente")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Proyecto no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Proyecto no encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Lista de estudiantes inválida",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Lista de estudiantes inválida")
 *         )
 *     )
 * )
 */
    public function asignarEstudiantes(Request $request, $id)
    {
        // Validar si el proyecto existe
        $proyecto = Proyecto::find($id);
        if (! $proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        // Validar estudiantes
        $estudiantes = $request->input('estudiantes');
        if (empty($estudiantes) || ! is_array($estudiantes)) {
            return response()->json(['message' => 'Lista de estudiantes inválida'], 400);
        }

        // Asignar estudiantes
        foreach ($estudiantes as $estudiante_id) {
            $usuario = User::find($estudiante_id);
            if (! $usuario) {
                return response()->json(['message' => 'Estudiante no encontrado'], 404);
            }

            // Asignar estudiante al proyecto
            $proyecto->usuarios()->attach($estudiante_id);
        }

        return response()->json(['message' => 'Estudiantes asignados correctamente']);
    }

    /**
     * @OA\Put(
     *     path="/api/proyectos/{id}/evaluar",
     *     summary="Evaluar un proyecto",
     *     description="Permite evaluar un proyecto sin restricciones de autenticación.",
     *     tags={"Proyectos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del proyecto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"evaluador_id", "dominio_tema", "manejo_auditorio", "planteamiento_problema"},
     *                 @OA\Property(property="evaluador_id", type="integer", example=18),
     *                 @OA\Property(property="dominio_tema", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="manejo_auditorio", type="integer", minimum=1, maximum=5, example=3),
     *                 @OA\Property(property="planteamiento_problema", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="justificacion", type="integer", minimum=1, maximum=5, example=3),
     *                 @OA\Property(property="objetivo_general", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="objetivo_especifico", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="marco_teorico", type="integer", minimum=1, maximum=5, example=3),
     *                 @OA\Property(property="metodologia", type="integer", minimum=1, maximum=5, example=2),
     *                 @OA\Property(property="resultado_esperado", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="referencia_bibliografica", type="integer", minimum=1, maximum=5, example=3),
     *                 @OA\Property(property="comentarios", type="string", example="Proyecto sólido, pero necesita mejorar la metodología")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evaluación registrada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Evaluación registrada correctamente"),
     *             @OA\Property(property="puntuacion", type="number", format="float", example=3.4),
     *             @OA\Property(property="puntaje_total", type="integer", example=34)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proyecto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proyecto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El proyecto no está inscrito en un evento",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El proyecto debe estar inscrito en un evento para ser evaluado")
     *         )
     *     )
     * )
     */
    public function evaluar(Request $request, $proyectoId)
    {
        // Validar que el proyecto existe
        $proyecto = Proyecto::find($proyectoId);
        if (! $proyecto) {
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        }

        // Validar campos de evaluación (1-5)
        $camposEvaluacion = [
            'dominio_tema',
            'manejo_auditorio',
            'planteamiento_problema',
            'justificacion',
            'objetivo_general',
            'objetivo_especifico',
            'marco_teorico',
            'metodologia',
            'resultado_esperado',
            'referencia_bibliografica',
        ];

        foreach ($camposEvaluacion as $campo) {
            if ($request->has($campo) && ($request->$campo < 1 || $request->$campo > 5)) {
                return response()->json(['message' => "El campo $campo debe ser entre 1 y 5"], 400);
            }
        }

        // Crear la evaluación
        $evaluacion = Evaluacion::create([
            'proyecto_id'              => $proyectoId,
            'evaluador_id'             => $request->evaluador_id,
            'comentarios'              => $request->comentarios,
            'dominio_tema'             => $request->dominio_tema,
            'manejo_auditorio'         => $request->manejo_auditorio,
            'planteamiento_problema'   => $request->planteamiento_problema,
            'justificacion'            => $request->justificacion,
            'objetivo_general'         => $request->objetivo_general,
            'objetivo_especifico'      => $request->objetivo_especifico,
            'marco_teorico'            => $request->marco_teorico,
            'metodologia'              => $request->metodologia,
            'resultado_esperado'       => $request->resultado_esperado,
            'referencia_bibliografica' => $request->referencia_bibliografica,
            'estado'                   => 'completada',
            'fecha_creacion'           => now(),
            'fecha_actualizacion'      => now(),
        ]);

        return response()->json([
            'message'       => 'Evaluación registrada correctamente',
            'puntuacion'    => $evaluacion->puntuacion,
            'puntaje_total' => $evaluacion->puntaje_total,
        ], 201);
    }

}
