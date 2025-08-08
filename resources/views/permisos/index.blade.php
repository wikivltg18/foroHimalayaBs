<x-app-layout>
    <x-slot name="buttonPress">
        {{-- Botón para crear una nueva cargo --}}
        <a href="{{ url('/equipo/roles/create') }}" class="btn btn-primary">Crear Rol</a>
    </x-slot>
    <x-slot name="slot">
        {{-- Contenedor principal --}}
<select name="roles" id="roles">
    <option value="">Selecciona un rol</option>
    @foreach ($roles as $role)
        <option value="{{ $role->id }}">{{ $role->name }}</option>
    @endforeach
</select>


<div id="permissions-container" class="row mt-4"></div>
<button type="button" id="savePermissionsBtn" class="btn btn-success mt-3">Guardar Permisos</button>
<div id="responseMsg" class="mt-2 text-success"></div>

@push('scripts')
    <script>
    const rolePermissions = @json($rolePermissions);
    const allPermissions = @json($permissions);

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
    
    document.getElementById('savePermissionsBtn').addEventListener('click', function () {
    const selectedRoleId = document.getElementById('roles').value;
    const selectedPermissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked')).map(input => input.value);

    fetch(`/permisos/${selectedRoleId}/permissions/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ permissions: selectedPermissions })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('responseMsg').textContent = data.message;
    })
    .catch(error => {
        console.error('Error al guardar los permisos:', error);
    });
});
</script>
@endpush

    </x-slot>
</x-app-layout>