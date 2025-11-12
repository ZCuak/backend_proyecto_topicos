<turbo-frame id="modal-frame">
    <div id="flyonui-modal-container">
        <!-- Overlay difuminado -->
        <div class="flyonui-overlay fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998]"
             onclick="FlyonUI.modal.close('editModal')">
        </div>

        <!-- Modal centrado -->
        <div id="editModal"
             class="flyonui-modal fixed inset-0 flex items-center justify-center z-[9999]"
             data-flyonui
             role="dialog"
             aria-modal="true">

            <!-- Contenedor con ancho dinámico y altura auto -->
            <div class="flyonui-dialog relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden 
                        animate-[flyonui-fade-in_0.3s_ease-out] mx-4 sm:mx-6 md:mx-auto my-10">
                
                <!-- Header -->
                <div class="flyonui-header flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-emerald-600"></i>
                        Editar Registro de Mantenimiento
                    </h3>
                    <button type="button"
                            onclick="FlyonUI.modal.close('editModal')"
                            class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flyonui-body p-6 bg-white overflow-y-auto max-h-[85vh]">
                    <form action="{{ route('maintenance-records.update', $record->id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          data-turbo-frame="modal-frame"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        @include('maintenance_records._form', ['buttonText' => 'Actualizar'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</turbo-frame>