<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Roles y Permisos</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Esta sección permite administrar roles y permisos del sistema. Los roles por defecto (superadmin, admin, client, handyman) no pueden ser eliminados.
                    </div>

                    <h5>Roles del Sistema</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Nombre de Visualización</th>
                                    <th>Descripción</th>
                                    <th>Usuarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="role in roles" :key="role.id">
                                    <td>{{ role.name }}</td>
                                    <td>{{ role.display_name }}</td>
                                    <td>{{ role.description }}</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ role.users_count || 0 }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'RoleManagement',
    setup() {
        const roles = ref([]);

        const fetchRoles = async () => {
            try {
                const response = await axios.get('/roles');
                roles.value = response.data;
            } catch (error) {
                console.error('Error fetching roles:', error);
            }
        };

        onMounted(() => {
            fetchRoles();
        });

        return {
            roles,
        };
    },
};
</script>