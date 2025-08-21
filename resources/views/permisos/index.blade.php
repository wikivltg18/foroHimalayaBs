{{-- 
    Vista para la gestión de permisos de roles
    Esta vista permite:
    1. Seleccionar un rol del sistema
    2. Ver y modificar los permisos asignados a ese rol
    3. Guardar los cambios realizados
--}}
<x-app-layout>
    <x-slot name="buttonPress">
        {{-- Botón para crear un nuevo rol --}}
        <a href="{{ url('/equipo/roles/create') }}" class="btn btn-primary">Crear Rol</a>
    </x-slot>
    <x-slot name="slot">
        {{-- Selector de roles --}}
        <div class="form-group">
            <label for="roles" class="form-label">Seleccione un Rol:</label>
            <select name="roles" id="roles" class="form-select">
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>


<div id="permissions-container" class="row mt-4"></div>
<button type="button" id="savePermissionsBtn" class="btn btn-success mt-3">Guardar Permisos</button>

@push('scripts')
    <script>
        // Objeto que almacena los permisos actuales de cada rol
        // Estructura: { roleId: [permissionId1, permissionId2, ...] }
        const rolePermissions = @json($rolePermissions);

        // Lista de todos los permisos disponibles en el sistema
        const allPermissions = @json($permissions);

        /**
        * Renderiza los checkboxes de permisos para un rol seleccionado
        * 
        * @param {string|number} selectedRoleId - ID del rol seleccionado
        */
        function renderPermissions(selectedRoleId) {
            const container = document.getElementById('permissions-container');
            container.innerHTML = '';

            allPermissions.forEach(permission => {
                const isChecked = rolePermissions[selectedRoleId]?.includes(permission.id) ? 'checked' : '';
                const checkbox = `
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="${permission.id}" ${isChecked}>
                            <label class="form-check-label">
                                ${permission.name}
                            </label>
                        </div>
                    </div>

                `;
                container.innerHTML += checkbox;
            });
        }

        document.getElementById('roles').addEventListener('change', function () {
            const selectedRoleId = this.value;
            if (selectedRoleId) {
                renderPermissions(selectedRoleId);
            } else {
                document.getElementById('permissions-container').innerHTML = '';
            }
        });

        // Si hay un rol seleccionado inicialmente, puedes mostrarlo desde el principio:
        window.addEventListener('DOMContentLoaded', () => {
            const initialRole = document.getElementById('roles').value;
            if (initialRole) {
                renderPermissions(initialRole);
            }
        });
        
        /**
        * Manejador de eventos para guardar los cambios en los permisos
        * 
        * Este evento:
        * 1. Recopila los permisos seleccionados
        * 2. Envía una petición AJAX al servidor
        * 3. Actualiza la interfaz según la respuesta
        * 4. Maneja posibles errores
        */
        document.getElementById('savePermissionsBtn').addEventListener('click', async function () {
        try {
            const selectedRoleId = document.getElementById('roles').value;
            if (!selectedRoleId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, selecciona un rol primero',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Mostrar alerta de carga
            Swal.fire({
                title: 'Guardando cambios',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const selectedPermissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked')).map(input => input.value);

            const response = await fetch(`/permisos/${selectedRoleId}/permissions/update`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ permissions: selectedPermissions })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en la respuesta del servidor');
            }

            if (data.success) {
                // Actualizar el objeto rolePermissions con los nuevos permisos
                rolePermissions[selectedRoleId] = data.data.permissions;
                
                // Mostrar mensaje de éxito
                await Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Los permisos se han actualizado correctamente',
                    confirmButtonColor: '#3085d6'
                });
                
                // Actualizar la interfaz visual
                renderPermissions(selectedRoleId);
            }

        } catch (error) {
            console.error('Error:', error);
            const responseMsg = document.getElementById('responseMsg');
            responseMsg.textContent = error.message || 'Error al guardar los permisos. Por favor, intenta de nuevo.';
            responseMsg.className = 'mt-2 alert alert-danger';
            
        } finally {
            // Restaurar el botón
            const saveButton = document.getElementById('savePermissionsBtn');
            saveButton.disabled = false;
            saveButton.textContent = 'Guardar Permisos';
            }
        });
    </script>
@endpush
    </x-slot>
</x-app-layout>