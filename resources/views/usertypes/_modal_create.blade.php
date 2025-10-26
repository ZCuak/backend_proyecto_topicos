<turbo-frame id="modal-frame">
    <div id="flyonui-modal-container">
        
        {{--OVERLAY (fondo difuminado)--}}
        <div class="flyonui-overlay fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998]"
             onclick="FlyonUI.modal.close('createModal')">
        </div>

        {{-- MODAL CENTRADO --}}
        <div id="createModal"
             class="flyonui-modal fixed inset-0 flex items-center justify-center z-[9999]"
             data-flyonui
             role="dialog"
             aria-modal="true">

            {{-- Contenedor del modal --}}
            <div class="flyonui-dialog relative bg-white rounded-2xl shadow-2xl w-full 
                        max-w-5xl sm:mx-4 overflow-hidden 
                        animate-[flyonui-fade-in_0.3s_ease-out]">

                {{-- HEADER --}}
                <div class="flyonui-header flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-emerald-600"></i> Registrar Nuevo Tipo
                    </h3>
                    <button type="button"
                            onclick="FlyonUI.modal.close('createModal')"
                            class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="flyonui-body p-6 bg-white">
                    <form action="{{ route('usertypes.store') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          data-turbo-frame="modal-frame"
                          class="space-y-6">
                        @csrf
                        
                        @include('usertypes._form', ['buttonText' => 'Registrar'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</turbo-frame>