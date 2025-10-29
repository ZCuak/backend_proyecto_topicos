<turbo-frame id="modal-frame">
    <div id="flyonui-modal-container">
        <!-- Fondo difuminado -->
        <div class="flyonui-overlay fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998]"
             onclick="FlyonUI.modal.close('massiveModal')">
        </div>

        <!-- Modal centrado -->
        <div id="massiveModal"
             class="flyonui-modal fixed inset-0 flex items-center justify-center z-[9999]"
             data-flyonui
             role="dialog"
             aria-modal="true">

            <!-- 🧩 Ancho flexible y acoplable -->
            <div class="flyonui-dialog relative bg-white rounded-2xl shadow-2xl w-full
                        max-w-4xl sm:mx-4 overflow-hidden
                        animate-[flyonui-fade-in_0.3s_ease-out]">

                <!-- Header -->
                <div class="flyonui-header flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-plus text-emerald-600"></i> Programación Masiva
                    </h3>
                    <button type="button"
                            onclick="FlyonUI.modal.close('massiveModal')"
                            class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flyonui-body p-6 bg-white">
                    <form action="{{ route('schedulings.store-massive') }}"
                          method="POST"
                          data-turbo-frame="modal-frame"
                          class="space-y-6">
                        @csrf
                        @include('schedulings.massive._form', ['buttonText' => 'Crear Programaciones'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</turbo-frame>
