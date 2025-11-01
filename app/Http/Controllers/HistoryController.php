<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * 📊 VISTA GLOBAL: Lista TODOS los cambios del sistema
     * 
     * Filtros disponibles:
     * - search: Buscar por usuario, campo, valor
     * - auditable_type: Filtrar por tipo de modelo
     * - user_name: Filtrar por usuario
     * - start_date: Fecha desde
     * - end_date: Fecha hasta
     * - campo_modificado: Filtrar por campo específico
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');

            $query = Audit::query()
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_name', 'ILIKE', "%{$search}%")
                        ->orWhere('campo_modificado', 'ILIKE', "%{$search}%")
                        ->orWhere('valor_anterior', 'ILIKE', "%{$search}%")
                        ->orWhere('valor_nuevo', 'ILIKE', "%{$search}%")
                        ->orWhere('nota_adicional', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->has('auditable_type') && $request->auditable_type) {
                $query->where('auditable_type', $request->auditable_type);
            }

            if ($request->has('user_name') && $request->user_name) {
                $query->where('user_name', 'ILIKE', "%{$request->user_name}%");
            }

            if ($request->has('start_date') && $request->start_date) {
                if ($request->has('end_date') && $request->end_date) {
                    $query->whereBetween('created_at', [
                        $request->start_date . ' 00:00:00',
                        $request->end_date . ' 23:59:59'
                    ]);
                } else {
                    $query->whereDate('created_at', $request->start_date);
                }
            }

            $audits = $query->paginate(20);
            $auditableTypes = Audit::select('auditable_type')
                ->distinct()
                ->orderBy('auditable_type')
                ->pluck('auditable_type');

            return view('history.index', compact('audits', 'search', 'auditableTypes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener el historial: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 VISTA INDIVIDUAL: Historial de cambios de UN registro específico
     * 
     * Ejemplo: Ver historial de Attendace #5
     * URL: /audits/show?type=Attendace&id=5
     */
    public function show(Request $request) {}

    /**
     * Obtener historial para un registro específico
     * 
     * @param string $auditableType Ej: "ASISTENCIA DE PERSONAL"
     * @param int $auditableId Ej: 5
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHistory($auditableType, $auditableId)
    {
        return Audit::where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
