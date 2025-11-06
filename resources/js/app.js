import './bootstrap';
import './theme.js';
import '@hotwired/turbo';
import 'alpinejs';
import '../css/app.css';
import Swal from 'sweetalert2';

/* =======================================================
 Config global SweetAlert2 estilo FlyonUI / Tailwind
======================================================= */
Swal.mixin({
    customClass: {
        popup: 'rounded-2xl shadow-2xl border border-emerald-100',
        confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition',
        cancelButton: 'bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg transition',
    },
    buttonsStyling: false,
});

/* =======================================================
   🔝 Asegurar que SweetAlert esté siempre encima (z-index)
======================================================= */
const fixSweetAlertZIndex = () => {
    const swalContainer = document.querySelector('.swal2-container');
    if (swalContainer) swalContainer.style.zIndex = '10050';
};





/* =======================================================
 FlyonUI + Turbo: manejo universal de modales
======================================================= */
document.addEventListener("turbo:frame-load", (e) => {
    const frame = e.target;
    if (frame.id !== "modal-frame") return;

    const modal = frame.querySelector(".flyonui-modal");
    const overlay = frame.querySelector(".flyonui-overlay");

    if (!modal) return;
    modal.classList.remove("hidden");

    window.FlyonUI = window.FlyonUI || {};
    FlyonUI.modal = FlyonUI.modal || {};

    FlyonUI.modal.close = (id) => {
        const modalEl = document.getElementById(id);
        const overlayEl = document.querySelector(".flyonui-overlay");
        if (modalEl) {
            modalEl.classList.add("opacity-0", "scale-95", "transition-all", "duration-200");
            setTimeout(() => modalEl.remove(), 200);
        }
        if (overlayEl) {
            overlayEl.classList.add("opacity-0", "transition-opacity", "duration-200");
            setTimeout(() => overlayEl.remove(), 200);
        }
        const frameEl = document.getElementById("modal-frame");
        if (frameEl) frameEl.innerHTML = "";
    };

    document.addEventListener("keydown", (evt) => {
        if (evt.key === "Escape") {
            const activeModal = document.querySelector(".flyonui-modal");
            if (activeModal) FlyonUI.modal.close(activeModal.id);
        }
    });
});

/* =======================================================
 Loader global para todas las acciones Turbo
======================================================= */
const toggleLoader = (show) => {
    const loader = document.getElementById("page-loader");
    if (!loader) return;
    loader.classList.toggle("hidden", !show);
};

document.addEventListener("turbo:before-visit", () => toggleLoader(true));
document.addEventListener("turbo:visit", () => toggleLoader(true));
document.addEventListener("turbo:load", () => toggleLoader(false));
document.addEventListener("turbo:frame-render", () => toggleLoader(false));
document.addEventListener("turbo:frame-error", () => toggleLoader(false));
document.addEventListener("turbo:submit-start", () => toggleLoader(true));
document.addEventListener("turbo:submit-end", () => toggleLoader(false));

/* =======================================================
    Form submit con feedback + reload controlado
======================================================= */
document.addEventListener("turbo:submit-end", async (e) => {
    try {
        const response = e.detail.fetchResponse?.response;
        if (!response) return;

        const contentType = response.headers.get("content-type") || "";
        if (!contentType.includes("application/json")) return;

        const data = await response.json();

        // Éxito
        if (data.success) {
            const modal = document.querySelector(".flyonui-modal");
            if (modal && window.FlyonUI?.modal) FlyonUI.modal.close(modal.id);
            toggleLoader(true);

            const onTurboLoaded = () => {
                toggleLoader(false);
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: data.message,
                    toast: true,
                    position: 'bottom-right',
                    timer: 2200,
                    showConfirmButton: false,
                    background: "#f0fdf4",
                    color: "#065f46",
                });
                document.removeEventListener("turbo:load", onTurboLoaded);
            };
            document.addEventListener("turbo:load", onTurboLoaded);
            Turbo.visit(window.location.href, { action: "replace" });
        }

        // Validación (422)
        else if (response.status === 422 && data.errors) {
            let errorList = "<ul style='text-align:left;margin:0;padding-left:20px'>";
            for (const [field, messages] of Object.entries(data.errors)) {
                messages.forEach(msg => errorList += `<li>${msg}</li>`);
            }
            errorList += "</ul>";

            Swal.fire({
                icon: "warning",
                title: "Revisa los datos ingresados",
                html: `<p>Algunos campos necesitan tu atención:</p>${errorList}`,
                confirmButtonColor: "#d97706",
                background: "#fff7ed",
                color: "#78350f",
            });
            fixSweetAlertZIndex();

            document.querySelectorAll(".input, select").forEach(el => {
                el.classList.remove("border-red-500");
            });
            Object.keys(data.errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) input.classList.add("border-red-500", "focus:ring-red-500");
            });
        }

        // Error 500
        else if (response.status >= 500) {
            Swal.fire({
                icon: "error",
                title: "Error interno",
                text: data.message || "Ocurrió un error inesperado.",
                confirmButtonColor: "#dc2626",
                background: "#fef2f2",
                color: "#7f1d1d",
            });
            fixSweetAlertZIndex();
        }

    } catch (error) {
        console.error("Error procesando respuesta:", error);
        toggleLoader(false);
    }
});

/* =======================================================
Alertas de sesión (Laravel flash)
======================================================= */
document.addEventListener("turbo:load", () => {
    const successMessage = document.body.dataset.success;
    const errorMessage = document.body.dataset.error;

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: successMessage,
            confirmButtonColor: '#059669',
            background: '#f0fdf4',
            color: '#065f46',
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            position: 'bottom-right',
        });
        fixSweetAlertZIndex();
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            confirmButtonColor: '#dc2626',
            background: '#fef2f2',
            color: '#7f1d1d',
            showConfirmButton: true,
        });
        fixSweetAlertZIndex();
    }
});

/* =======================================================
 Loader automático para enlaces de modales
======================================================= */
document.addEventListener("turbo:load", () => {
    document.querySelectorAll('a[data-turbo-frame="modal-frame"]').forEach(link => {
        link.addEventListener("click", () => toggleLoader(true));
    });
});

/* =======================================================
 Confirmación y eliminación con SweetAlert2 + Turbo
======================================================= */
document.addEventListener("turbo:load", () => {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            if (!url) return;

            const result = await Swal.fire({
                icon: "warning",
                title: "¿Eliminar este registro?",
                text: "Esta acción no se puede deshacer.",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#6b7280",
                background: "#fff7ed",
                color: "#78350f",
                reverseButtons: true,
            });

            if (result.isConfirmed) {
                try {
                    toggleLoader(true);
                    const token = document.querySelector('meta[name="csrf-token"]').content;

                    const response = await fetch(url, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": token,
                            "Accept": "application/json",
                        },
                    });

                    const data = await response.json();

                    const onTurboLoaded = () => {
                        toggleLoader(false);
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "¡Eliminado!",
                                text: data.message,
                                toast: true,
                                position: 'bottom-right',
                                timer: 2200,
                                showConfirmButton: false,
                                background: "#f0fdf4",
                                color: "#065f46",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: data.message || "No se pudo eliminar el registro.",
                                confirmButtonColor: "#dc2626",
                                background: "#fef2f2",
                                color: "#7f1d1d",
                            });
                        }
                        document.removeEventListener("turbo:load", onTurboLoaded);
                    };

                    document.addEventListener("turbo:load", onTurboLoaded);
                    Turbo.visit(window.location.href, { action: "replace" });

                } catch (err) {
                    toggleLoader(false);
                    console.error("Error eliminando:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error interno",
                        text: "Ocurrió un problema al eliminar el registro.",
                        confirmButtonColor: "#dc2626",
                        background: "#fef2f2",
                        color: "#7f1d1d",
                    });
                }
            }
        });
    });
});

/* =======================================================
    Exponer SweetAlert2 al ámbito global (window)
======================================================= */
window.Swal = Swal;
