import $ from 'jquery';
window.$ = window.jQuery = $;

import './bootstrap';
import '../css/app.css';
import '../css/custom.css';
import './custom';
import Swal from 'sweetalert2';
window.Swal = Swal;
import 'bootstrap';
import Sortable from 'sortablejs';
window.Sortable = Sortable;

// Quill
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// --------- util genérico para montar un editor ----------
function mountQuill(el, {
    fileInputId,
    hiddenTargetId,     // id del hidden donde escribir (HTML) al enviar
    hiddenSourceId,     // id del hidden desde donde precargar (old())
    formId,             // opcional: id del form; si no, busca el form más cercano
} = {}) {
    if (!el || el.dataset.quillInitialized === '1' || el.__quill) return;

    const quill = new Quill(el, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });
    el.dataset.quillInitialized = '1';

    // Preload desde hidden (old())
    if (hiddenSourceId) {
        const src = document.getElementById(hiddenSourceId);
        if (src && src.value) quill.root.innerHTML = src.value;
    }

    // Envío → copiar HTML al hidden target
    const form = (formId && document.getElementById(formId)) || el.closest('form');
    if (form && hiddenTargetId) {
        form.addEventListener('submit', (e) => {
            const html = quill.root.innerHTML.trim();
            const plain = quill.getText().trim();
            if (!plain || html === '<p><br></p>') {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({ title: 'Contenido vacío', text: 'Escribe algo antes de guardar.', icon: 'warning' });
                } else {
                    alert('Escribe algo antes de guardar.');
                }
                return;
            }
            const hidden = document.getElementById(hiddenTargetId);
            if (hidden) hidden.value = html;
        });
    }

    // Subida de imágenes (si hay uploadUrl + file input)
    const uploadUrl = el.dataset.uploadUrl;
    const csrf = el.dataset.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.content ?? '');
    const fileInput = fileInputId ? document.getElementById(fileInputId) : null;

    if (uploadUrl && fileInput) {
        const toolbar = quill.getModule('toolbar');
        toolbar.addHandler('image', () => fileInput.click());

        fileInput.addEventListener('change', async (ev) => {
            const file = ev.target.files?.[0];
            if (!file) return;
            try {
                const fd = new FormData();
                fd.append('file', file);
                if (csrf) fd.append('_token', csrf);

                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const text = await res.text();
                let payload; try { payload = JSON.parse(text); } catch { payload = { message: text }; }
                if (!res.ok) throw new Error(payload?.message || `HTTP ${res.status}`);

                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', payload.url, 'user');
                quill.setSelection(range.index + 1, 0);
                ev.target.value = '';
            } catch (err) {
                console.error(err);
                window.Swal
                    ? Swal.fire({ title: 'Error al subir imagen', text: err?.message || 'No fue posible subir la imagen.', icon: 'error' })
                    : alert('No fue posible subir la imagen.');
            }
        });
    }
}

// ---------- inits para tus dos vistas actuales ----------
function initQuillComentario() {
    const el = document.getElementById('comment-editor');
    if (!el) return;
    mountQuill(el, {
        fileInputId: 'quill-comment-image-input',
        hiddenTargetId: 'comentario_html',
        hiddenSourceId: null,
        formId: 'formComentario',
    });
}

function initQuillCreateOrEdit() {
    const el = document.getElementById('editor-container');
    if (!el) return;
    mountQuill(el, {
        fileInputId: 'quill-image-input',
        hiddenTargetId: el.dataset.targetHiddenId || 'descripcion', // por defecto 'descripcion'
        hiddenSourceId: el.dataset.sourceHiddenId || 'descripcion',
    });
}

// (opcional) soporte para otros editores marcados con data-quill-editor
function initQuillDataAttrs() {
    document.querySelectorAll('[data-quill-editor]:not([data-quill-initialized])').forEach((el) => {
        mountQuill(el, {
            fileInputId: el.getAttribute('data-file-input-id') || null,
            hiddenTargetId: el.getAttribute('data-target-hidden-id') || null,
            hiddenSourceId: el.getAttribute('data-source-hidden-id') || null,
            formId: el.getAttribute('data-form-id') || null,
        });
        el.setAttribute('data-quill-initialized', '1');
    });
}

// boot
function boot() {
    initQuillComentario();
    initQuillCreateOrEdit();
    initQuillDataAttrs();
}

['DOMContentLoaded', 'turbo:load', 'livewire:load'].forEach(evt =>
    document.addEventListener(evt, boot)
);
if (document.readyState === 'interactive' || document.readyState === 'complete') boot();
