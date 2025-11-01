<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HistoryChanges
{
    /**
     * Función de soporte: Resuelve un ID a su representación textual.
     */
    protected function resolveRelatedText(Model $model, string $dbField, $id): string
    {
        if (!$id) return 'SIN ASIGNAR';

        $relationName = str_replace('_id', '', $dbField);

        try {

            $relatedModel = $model->{$relationName}()->getRelated();
            $record = $relatedModel->find($id);
            if ($record) {
                return $record->name ?? $record->nombre ?? $record->firstname . " " . $record->lastname  ?? 'ID ' . $id;
            }
        } catch (\Throwable $th) {
            return 'ID ' . $id . ' (Error: ' . $th->getMessage() . ')';
        }

        return 'ID ' . $id . ' (Registro no encontrado)';
    }

    /**
     * Obtiene el nombre legible y corto del modelo para el registro de auditoría.
     */
    protected function getAuditTypeName(Model $model): string
    {
        $className = get_class($model);

        if (property_exists($className, 'auditName')) {
            return $className::$auditName;
        }

        return class_basename($model);
    }

    protected function getFieldNameForAudit(Model $model, string $dbField): string
    {
        $className = get_class($model);

        if (property_exists($className, 'auditFieldNames')) {
            $map = $className::$auditFieldNames;

            if (isset($map[$dbField])) {
                return $map[$dbField];
            }
        }

        if (str_ends_with($dbField, '_id')) {
            return str_replace('_id', '', $dbField);
        }

        return $dbField;
    }

    /**
     * Registra los cambios detectados en la tabla de auditoría.
     * @param Model $model El modelo actualizado.
     * @param array $originalData Los atributos del modelo ANTES de la actualización.
     * @param string|null $nota Nota opcional del usuario.
     * @param array $exceptFields Campos a ignorar (e.g., timestamps, id).
     */
    protected function registrarCambios(
        Model $model,
        array $originalData,
        ?string $nota = null,
        array $exceptFields = ['id', 'created_at', 'updated_at', 'deleted_at']
    ): void {
        $changes = [];
        $userName = Auth::check() ? Auth::user()->username : 'Sistema/Invitado';
        $currentData = $model->getAttributes();

        foreach ($currentData as $field => $newValue) {

            if (in_array($field, $exceptFields)) {
                continue;
            }

            $originalValue = $originalData[$field] ?? null;

            if (isset($originalData[$field]) && (string) $originalValue !== (string) $newValue) {

                if (str_ends_with($field, '_id')) {
                    $valorAnteriorTexto = $this->resolveRelatedText($model, $field, $originalValue);
                    $valorNuevoTexto = $this->resolveRelatedText($model, $field, $newValue);
                } else {
                    $valorAnteriorTexto = (string) $originalValue;
                    $valorNuevoTexto = (string) $newValue;
                }
                $campo = $this->getFieldNameForAudit($model, $field);

                $changes[] = [
                    'campo_modificado' => $campo,
                    'valor_anterior' => $valorAnteriorTexto,
                    'valor_nuevo' => $valorNuevoTexto,
                ];
            }
        }

        if (!empty($changes)) {
            foreach ($changes as $change) {
                $auditTypeName = $this->getAuditTypeName($model);
                Audit::create([
                    'auditable_type' => $auditTypeName,
                    'auditable_id' => $model->id,
                    'campo_modificado' => $change['campo_modificado'],
                    'valor_anterior' => $change['valor_anterior'],
                    'valor_nuevo' => $change['valor_nuevo'],
                    'user_name' => $userName,
                    'nota_adicional' => $nota,
                ]);
            }
        }
    }

    /**
     * Registra el evento de eliminación de un registro.
     * @param Model $model El modelo que está siendo eliminado (antes del delete()).
     * @param string|null $nota La nota opcional proporcionada por el usuario.
     */
    protected function registrarEliminacion(Model $model, ?string $nota = null): void
    {
        $userName = Auth::check() ? Auth::user()->name : 'Sistema/Invitado';

        Audit::create([
            'auditable_type' => $this->getAuditTypeName($model), // Nombre legible del modelo
            'auditable_id' => $model->id,
            'campo_modificado' => 'registro_eliminado', // Marcador de acción
            // Guardamos todos los datos (incluyendo IDs) como JSON para el historial
            'valor_anterior' => json_encode($model->getOriginal()),
            'valor_nuevo' => 'ELIMINADO',
            'user_name' => $userName,
            'nota_adicional' => $nota ?? 'Eliminación física del registro.',
        ]);
    }
}
