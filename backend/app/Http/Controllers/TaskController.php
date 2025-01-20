<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Listar todas las tareas del usuario",
     *     description="Obtiene todas las tareas asociadas al usuario autenticado.",
     *     operationId="listTasks",
     *     tags={"Tasks"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tareas obtenida exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $tasks = Auth::user()->tasks()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Crear una nueva tarea",
     *     description="Permite al usuario autenticado crear una nueva tarea.",
     *     operationId="createTask",
     *     tags={"Tasks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Mi nueva tarea"),
     *             @OA\Property(property="description", type="string", example="Detalles de la tarea"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarea creada exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tarea creada exitosamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Auth::user()->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'active'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea creada exitosamente',
            'data' => $task
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Mostrar una tarea específica",
     *     description="Obtiene los detalles de una tarea específica del usuario autenticado.",
     *     operationId="getTask",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la tarea"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la tarea obtenidos exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Tarea no encontrada")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $task = Auth::user()->tasks()->find($id);
        
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarea no encontrada'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Actualizar una tarea",
     *     description="Permite al usuario autenticado actualizar los detalles de una tarea existente.",
     *     operationId="updateTask",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la tarea"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Título actualizado"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea actualizada exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tarea actualizada exitosamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Tarea no encontrada")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $task = Auth::user()->tasks()->find($id);
        
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarea no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea actualizada exitosamente',
            'data' => $task
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Eliminar una tarea",
     *     description="Permite al usuario autenticado eliminar una tarea existente.",
     *     operationId="deleteTask",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la tarea"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea eliminada exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tarea eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Tarea no encontrada")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $task = Auth::user()->tasks()->find($id);
        
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarea no encontrada'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tarea eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}/toggle-status",
     *     summary="Cambiar el estado de una tarea",
     *     description="Permite al usuario cambiar el estado de una tarea entre 'active' e 'inactive'.",
     *     operationId="toggleTaskStatus",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID de la tarea"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado de la tarea actualizado exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Estado de la tarea actualizado exitosamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarea no encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Tarea no encontrada")
     *         )
     *     )
     * )
     */
    public function toggleStatus($id)
    {
        $task = Auth::user()->tasks()->find($id);
        
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarea no encontrada'
            ], 404);
        }

        $task->status = $task->status === 'active' ? 'inactive' : 'active';
        $task->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la tarea actualizado exitosamente',
            'data' => $task
        ]);
    }
}
