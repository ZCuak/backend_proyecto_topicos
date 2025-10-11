<?php

namespace App\Http\Controllers\Api\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

/**
 * Controlador para la gestión de empresas.
 * 
 * Este controlador implementa las operaciones CRUD (Create, Read, Update, Delete)
 * para el modelo Empresa, incluyendo funcionalidades avanzadas como:
 * - Búsqueda global en todos los campos
 * - Filtrado por campos específicos
 * - Ordenamiento personalizable
 * - Paginación de resultados
 * - Validación de datos de entrada
 * - Soft delete para eliminación segura
 * - Restauración automática de empresas eliminadas con RUC duplicado
 * 
 * Todas las respuestas se devuelven en formato JSON con estructura consistente
 * que incluye success, data y message.
 * 
 * @package App\Http\Controllers\Api\Empresa
 * @author Sistema de Gestión de Empresas
 * @version 1.0
 */
class EmpresaController extends Controller
{
    /**
     * Obtiene una lista paginada de empresas con funcionalidades de búsqueda, filtrado y ordenamiento.
     * 
     * @param Request $request Contiene parámetros de consulta:
     *   - search: Término de búsqueda global en todos los campos
     *   - per_page: Número de elementos por página (default: 10)
     *   - sortBy: Campo para ordenar (default: 'id')
     *   - sortOrder: Dirección de ordenamiento 'asc' o 'desc' (default: 'asc')
     *   - all: Si es true, retorna todos los registros sin paginación -> Esto lo usaré para los selects
     *   - [cualquier_campo]: Filtro exacto por campo específico
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - data: Array de empresas o objeto de paginación
     *   - message: Mensaje descriptivo del resultado
     */
public function index(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'asc');

        $query = Empresa::query();

        // 🔍 Buscar en todos los campos de la tabla (menos timestamps y deleted_at)
        if ($search) {
            $columns = Schema::getColumnListing('empresas');
            $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
            $columns = array_diff($columns, $excluir);

            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        // 📌 Aplicar filtros exactos por campo (si vienen en el request)
        foreach ($request->all() as $key => $value) {
            if (Schema::hasColumn('empresas', $key) && $key !== 'search' && $key !== 'sortBy' && $key !== 'sortOrder' && $key !== 'per_page') {
                $query->where($key, $value);
            }
        }

        // 📌 Ordenamiento
        if (Schema::hasColumn('empresas', $sortBy)) {
            $query->orderBy($sortBy, $sortOrder);
        }
        $all = $request->input('all', false);
        if ($all) {
            $empresas = $query->get();
        } else {
           $empresas = $query->paginate($perPage);
        }
        

        return response()->json([
            'success' => true,
            'data' => $empresas,
            'message' => 'Empresas obtenidas exitosamente'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener las empresas',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Crea una nueva empresa en la base de datos con validación de datos y verificación de RUC único.
     * 
     * @param Request $request Datos de la empresa a crear:
     *   - razon_social: string requerido, máximo 255 caracteres
     *   - direccion: string opcional, máximo 255 caracteres
     *   - ruc: string requerido, exactamente 11 caracteres (único)
     *   - email: string opcional, formato email válido, máximo 255 caracteres
     *   - telefono_fijo: string opcional, máximo 20 caracteres
     *   - telefono_movil: string opcional, máximo 20 caracteres
     *   - logo: string opcional, máximo 255 caracteres
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - data: Objeto de la empresa creada o restaurada
     *   - message: Mensaje descriptivo del resultado
     * 
     * @note Si existe una empresa con el mismo RUC eliminada (soft delete), la restaura y actualiza sus datos.
     * Si existe una empresa activa con el mismo RUC, retorna error de validación.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'razon_social' => 'required|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'ruc' => 'required|string|size:11',
                'email' => 'nullable|email|max:255',
                'telefono_fijo' => 'nullable|string|max:20',
                'telefono_movil' => 'nullable|string|max:20',
                'logo' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si existe una empresa con el mismo RUC (incluyendo eliminadas)
            $empresaExistente = Empresa::withTrashed()->where('ruc', $request->ruc)->first();

            if ($empresaExistente) {
                if ($empresaExistente->trashed()) {
                    // Si la empresa existe pero fue eliminada, restaurarla y actualizar datos
                    $empresaExistente->restore();
                    $empresaExistente->update($request->all());
                    
                    return response()->json([
                        'success' => true,
                        'data' => $empresaExistente->fresh(),
                        'message' => 'Empresa restaurada y actualizada exitosamente'
                    ], 200);
                } else {
                    // Si la empresa existe y no está eliminada, retornar error
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una empresa con este RUC',
                        'errors' => ['ruc' => ['El RUC ya está registrado']]
                    ], 422);
                }
            }

            // Si no existe, crear nueva empresa
            $empresa = Empresa::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $empresa,
                'message' => 'Empresa creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los datos de una empresa específica por su ID.
     * 
     * @param string $id ID único de la empresa a consultar
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - data: Objeto de la empresa encontrada (null si no existe)
     *   - message: Mensaje descriptivo del resultado
     * 
     * @throws 404 Si la empresa no existe en la base de datos
     */
    public function show(string $id): JsonResponse
    {
        try {
            $empresa = Empresa::find($id);
            
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $empresa,
                'message' => 'Empresa obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza los datos de una empresa existente por su ID.
     * 
     * @param Request $request Datos a actualizar (todos los campos son opcionales):
     *   - razon_social: string, máximo 255 caracteres
     *   - direccion: string, máximo 255 caracteres
     *   - ruc: string, exactamente 11 caracteres (debe ser único)
     *   - email: string, formato email válido, máximo 255 caracteres
     *   - telefono_fijo: string, máximo 20 caracteres
     *   - telefono_movil: string, máximo 20 caracteres
     *   - logo: string, máximo 255 caracteres
     * 
     * @param string $id ID único de la empresa a actualizar
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - data: Objeto de la empresa actualizada
     *   - message: Mensaje descriptivo del resultado
     * 
     * @throws 404 Si la empresa no existe en la base de datos
     * @throws 422 Si hay errores de validación (RUC duplicado, formato inválido, etc.)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $empresa = Empresa::find($id);
            
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'razon_social' => 'sometimes|required|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'ruc' => 'sometimes|required|string|size:11|unique:empresas,ruc,' . $id,
                'email' => 'nullable|email|max:255',
                'telefono_fijo' => 'nullable|string|max:20',
                'telefono_movil' => 'nullable|string|max:20',
                'logo' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $empresa->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $empresa,
                'message' => 'Empresa actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una empresa de la base de datos utilizando soft delete.
     * 
     * @param string $id ID único de la empresa a eliminar
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - message: Mensaje descriptivo del resultado
     * 
     * @throws 404 Si la empresa no existe en la base de datos
     * 
     * @note Esta función utiliza soft delete, por lo que la empresa no se elimina
     * físicamente de la base de datos, solo se marca como eliminada. Esto permite
     * restaurarla posteriormente si es necesario.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $empresa = Empresa::find($id);
            
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            $empresa->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Empresa eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
