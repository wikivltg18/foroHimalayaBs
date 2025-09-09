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

// Verificar que jQuery está disponible globalmente
console.log('jQuery version:', $.fn.jquery);

