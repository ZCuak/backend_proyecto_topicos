<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Listar vehículos (Web)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Vehicle::with(['brand', 'model', 'type', 'color', 'profileImage']);

        // Búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%")
                  ->orWhere('plate', 'ILIKE', "%{$search}%")
                  ->orWhere('year', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhereHas('brand', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('model', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('type', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('color', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        $vehicles = $query->orderBy('id', 'desc')->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage
        ]);

        return view('vehicles.index', compact('vehicles', 'search'));
    }

    /**
     * Mostrar formulario de creación (Web)
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $models = BrandModel::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();
        $colors = VehicleColor::orderBy('name')->get();

        return view('vehicles._modal_create', compact('brands', 'models', 'types', 'colors'));
    }

    /**
     * Almacenar nuevo vehículo (Web)
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:vehicles,code',
            'plate' => [
                'required', 
                'string',
                'unique:vehicles,plate',
                function ($attribute, $value, $fail) {
                    // Normalizar: convertir a mayúsculas y quitar espacios
                    $normalized = strtoupper(trim($value));
                    
                    // Validar formatos: XXXXXX, XX-XXXX, XXX-XXX
                    $formats = [
                        '/^[A-Z0-9]{6}$/',           // XXXXXX (6 caracteres sin guión)
                        '/^[A-Z0-9]{2}-[A-Z0-9]{4}$/', // XX-XXXX (2 caracteres, guión, 4 caracteres)
                        '/^[A-Z0-9]{3}-[A-Z0-9]{3}$/', // XXX-XXX (3 caracteres, guión, 3 caracteres)
                    ];
                    
                    $isValid = false;
                    foreach ($formats as $format) {
                        if (preg_match($format, $normalized)) {
                            $isValid = true;
                            break;
                        }
                    }
                    
                    if (!$isValid) {
                        $fail('La placa debe tener uno de los siguientes formatos: XXXXXX, XX-XXXX o XXX-XXX (ejemplo: ABC123, AB-1234, ABC-123)');
                    }
                }
            ],
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . date('Y'),
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value) || $value < 1900 || $value > date('Y')) {
                        $fail('El año debe ser un año válido entre 1900 y ' . date('Y'));
                    }
                }
            ],
            'occupant_capacity' => 'nullable|integer|min:0',
            'load_capacity' => 'nullable|integer|min:0',
            'compaction_capacity' => 'nullable|integer|min:0',
            'fuel_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:brandmodels,id',
            'type_id' => 'required|exists:vehicletypes,id',
            'color_id' => 'required|exists:vehiclecolors,id'
        ], [
            'plate.required' => 'La placa es obligatoria',
            'plate.unique' => 'Esta placa ya está registrada en otro vehículo',
            'code.unique' => 'Este código ya está registrado en otro vehículo',
            'year.required' => 'El año es obligatorio',
            'year.integer' => 'El año debe ser un número entero',
            'year.min' => 'El año debe ser mayor o igual a 1900',
            'year.max' => 'El año no puede ser mayor al año actual',
            'brand_id.required' => 'La marca es obligatoria',
            'brand_id.exists' => 'La marca seleccionada no es válida',
            'model_id.required' => 'El modelo es obligatorio',
            'model_id.exists' => 'El modelo seleccionado no es válido',
            'type_id.required' => 'El tipo de vehículo es obligatorio',
            'type_id.exists' => 'El tipo seleccionado no es válido',
            'color_id.required' => 'El color es obligatorio',
            'color_id.exists' => 'El color seleccionado no es válido'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();
            
            // Normalizar placa: convertir a mayúsculas y quitar espacios
            if (isset($data['plate'])) {
                $data['plate'] = strtoupper(trim($data['plate']));
            }
            
            // Asegurar que los campos de capacidad tengan valores por defecto si están vacíos
            $data['occupant_capacity'] = $data['occupant_capacity'] ?? 0;
            $data['load_capacity'] = $data['load_capacity'] ?? 0;
            $data['compaction_capacity'] = $data['compaction_capacity'] ?? null;
            $data['fuel_capacity'] = $data['fuel_capacity'] ?? null;
            
            $vehicle = Vehicle::create($data);
            
            // Manejar carga de imágenes
            if ($request->hasFile('images')) {
                $this->handleImageUpload($request->file('images'), $vehicle);
            }
            
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehículo registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo registrado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar vehículo: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vehículo específico (Web)
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['brand', 'model', 'type', 'color', 'profileImage', 'images']);
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Mostrar formulario de edición (Web)
     */
    public function edit(Vehicle $vehicle)
    {
        $brands = Brand::orderBy('name')->get();
        $models = BrandModel::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();
        $colors = VehicleColor::orderBy('name')->get();
        
        // Cargar imágenes del vehículo
        $vehicle->load(['images', 'profileImage']);

        return view('vehicles._modal_edit', compact('vehicle', 'brands', 'models', 'types', 'colors'));
    }

    /**
     * Actualizar vehículo (Web)
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        // Console logs para debuggear
        Log::info('=== UPDATE VEHICLE DEBUG ===');
        Log::info('Vehicle ID: ' . $vehicle->id);
        Log::info('Request data: ', $request->all());
        Log::info('Has files: ' . ($request->hasFile('images') ? 'YES' : 'NO'));
        Log::info('Has temp_images: ' . ($request->hasFile('temp_images') ? 'YES' : 'NO'));
        Log::info('Profile image index: ' . ($request->input('profile_image_index', 'NOT SET')));
        
        if ($request->hasFile('images')) {
            Log::info('Images count: ' . count($request->file('images')));
            foreach ($request->file('images') as $index => $file) {
                Log::info("Image {$index}: " . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
            }
        }
        
        if ($request->hasFile('temp_images')) {
            Log::info('Temp images count: ' . count($request->file('temp_images')));
            foreach ($request->file('temp_images') as $index => $file) {
                Log::info("Temp image {$index}: " . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
            }
        }
        Log::info('Is Turbo: ' . ($isTurbo ? 'YES' : 'NO'));

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:vehicles,code,' . $vehicle->id,
            'plate' => [
                'required', 
                'string',
                'unique:vehicles,plate,' . $vehicle->id,
                function ($attribute, $value, $fail) {
                    // Normalizar: convertir a mayúsculas y quitar espacios
                    $normalized = strtoupper(trim($value));
                    
                    // Validar formatos: XXXXXX, XX-XXXX, XXX-XXX
                    $formats = [
                        '/^[A-Z0-9]{6}$/',           // XXXXXX (6 caracteres sin guión)
                        '/^[A-Z0-9]{2}-[A-Z0-9]{4}$/', // XX-XXXX (2 caracteres, guión, 4 caracteres)
                        '/^[A-Z0-9]{3}-[A-Z0-9]{3}$/', // XXX-XXX (3 caracteres, guión, 3 caracteres)
                    ];
                    
                    $isValid = false;
                    foreach ($formats as $format) {
                        if (preg_match($format, $normalized)) {
                            $isValid = true;
                            break;
                        }
                    }
                    
                    if (!$isValid) {
                        $fail('La placa debe tener uno de los siguientes formatos: XXXXXX, XX-XXXX o XXX-XXX (ejemplo: ABC123, AB-1234, ABC-123)');
                    }
                }
            ],
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . date('Y'),
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value) || $value < 1900 || $value > date('Y')) {
                        $fail('El año debe ser un año válido entre 1900 y ' . date('Y'));
                    }
                }
            ],
            'occupant_capacity' => 'nullable|integer|min:0',
            'load_capacity' => 'nullable|integer|min:0',
            'compaction_capacity' => 'nullable|integer|min:0',
            'fuel_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'exists:brandmodels,id',
            'type_id' => 'exists:vehicletypes,id',
            'color_id' => 'exists:vehiclecolors,id'
        ], [
            'plate.required' => 'La placa es obligatoria',
            'plate.unique' => 'Esta placa ya está registrada en otro vehículo',
            'code.unique' => 'Este código ya está registrado en otro vehículo',
            'year.required' => 'El año es obligatorio',
            'year.integer' => 'El año debe ser un número entero',
            'year.min' => 'El año debe ser mayor o igual a 1900',
            'year.max' => 'El año no puede ser mayor al año actual',
            'brand_id.required' => 'La marca es obligatoria',
            'brand_id.exists' => 'La marca seleccionada no es válida',
            'model_id.required' => 'El modelo es obligatorio',
            'model_id.exists' => 'El modelo seleccionado no es válido',
            'type_id.required' => 'El tipo de vehículo es obligatorio',
            'type_id.exists' => 'El tipo seleccionado no es válido',
            'color_id.required' => 'El color es obligatorio',
            'color_id.exists' => 'El color seleccionado no es válido'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            
            // Normalizar placa: convertir a mayúsculas y quitar espacios
            if (isset($data['plate'])) {
                $data['plate'] = strtoupper(trim($data['plate']));
            }
            
            // Asegurar que los campos de capacidad tengan valores por defecto si están vacíos
            $data['occupant_capacity'] = $data['occupant_capacity'] ?? 0;
            $data['load_capacity'] = $data['load_capacity'] ?? 0;
            $data['compaction_capacity'] = $data['compaction_capacity'] ?? null;
            $data['fuel_capacity'] = $data['fuel_capacity'] ?? null;
            
            Log::info('Validated data: ', $data);
            
            $vehicle->update($data);
            
            Log::info('Vehicle updated successfully');

            // Manejar carga de imágenes
            if ($request->hasFile('images')) {
                Log::info('Processing images for update...');
                $profileIndex = $request->input('profile_image_index');
                $this->handleImageUpload($request->file('images'), $vehicle, null, $profileIndex);
                Log::info('Images processed successfully');
            } elseif ($request->hasFile('temp_images')) {
                Log::info('Processing temp_images for update...');
                $profileIndex = $request->input('profile_image_index');
                $this->handleImageUpload($request->file('temp_images'), $vehicle, null, $profileIndex);
                Log::info('Temp images processed successfully');
            } else {
                Log::info('No images to process');
            }

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehículo actualizado exitosamente.',
                ], 200);
            }

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo actualizado exitosamente');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar vehículo: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar vehículo (Web)
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            $vehicle->delete();

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener modelos por marca
     */
    public function getModelsByBrand($brandId)
    {
        $models = BrandModel::where('brand_id', $brandId)->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    /**
     * Manejar carga de imágenes
     */
    private function handleImageUpload($images, $vehicle, $isProfile = null, $profileIndex = null)
    {
        Log::info('=== HANDLE IMAGE UPLOAD DEBUG ===');
        Log::info('Vehicle ID: ' . $vehicle->id);
        Log::info('Images count: ' . count($images));
        Log::info('IsProfile parameter: ' . ($isProfile === null ? 'null' : ($isProfile ? 'true' : 'false')));
        Log::info('Profile index: ' . ($profileIndex !== null ? $profileIndex : 'null'));
        
        // Borrar todas las imágenes existentes del vehículo
        Log::info('Deleting existing images for vehicle...');
        $existingImages = VehicleImage::where('vehicle_id', $vehicle->id)->get();
        foreach ($existingImages as $existingImage) {
            // Eliminar archivo del storage
            if (Storage::disk('public')->exists($existingImage->path)) {
                Storage::disk('public')->delete($existingImage->path);
                Log::info("Deleted file: " . $existingImage->path);
            }
            // Eliminar registro de la base de datos
            $existingImage->delete();
            Log::info("Deleted VehicleImage record: " . $existingImage->id);
        }
        
        $uploadedImages = [];
        
        foreach ($images as $index => $image) {
            Log::info("Processing image {$index}: " . $image->getClientOriginalName());
            
            // Validar imagen
            if (!$image->isValid()) {
                Log::warning("Image {$index} is not valid: " . $image->getError());
                continue;
            }
            
            // Generar nombre único
            $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
            Log::info("Generated filename: {$filename}");
            
            // Guardar en storage
            $path = $image->storeAs('vehicles', $filename, 'public');
            Log::info("Stored at path: {$path}");
            
            // Determinar si es imagen de perfil
            $isProfileImage = false;
            if ($profileIndex !== null && $profileIndex == $index) {
                $isProfileImage = true;
            } elseif ($isProfile === null && $index === 0) {
                $isProfileImage = true;
            } elseif ($isProfile !== null) {
                $isProfileImage = $isProfile;
            }
            
            Log::info("Is profile image: " . ($isProfileImage ? 'YES' : 'NO'));
            
            // Crear registro en base de datos
            try {
                $vehicleImage = VehicleImage::create([
                    'path' => $path,
                    'is_profile' => $isProfileImage,
                    'vehicle_id' => $vehicle->id
                ]);
                Log::info("VehicleImage created with ID: " . $vehicleImage->id);
            } catch (\Exception $e) {
                Log::error("Error creating VehicleImage: " . $e->getMessage());
                throw $e;
            }
            
            $uploadedImages[] = $path;
        }
        
        Log::info('Uploaded images: ', $uploadedImages);
        return $uploadedImages;
    }

    /**
     * Establecer imagen de perfil (AJAX)
     */
    public function setProfileImage(Request $request, $vehicleId, $imageId)
    {
        try {
            $vehicle = Vehicle::findOrFail($vehicleId);
            $image = VehicleImage::where('vehicle_id', $vehicleId)->findOrFail($imageId);
            
            // Remover perfil de todas las imágenes del vehículo
            VehicleImage::where('vehicle_id', $vehicleId)->update(['is_profile' => false]);
            
            // Establecer nueva imagen de perfil
            $image->update(['is_profile' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen de perfil actualizada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar imagen de perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen (AJAX)
     */
    public function deleteImage(Request $request, $vehicleId, $imageId)
    {
        try {
            $vehicle = Vehicle::findOrFail($vehicleId);
            $image = VehicleImage::where('vehicle_id', $vehicleId)->findOrFail($imageId);
            
            // Eliminar archivo del storage
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            
            // Eliminar registro de la base de datos
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}
