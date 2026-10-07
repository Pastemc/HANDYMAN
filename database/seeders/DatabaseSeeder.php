<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles
        $superadmin = Role::create([
            'name' => 'superadmin',
            'display_name' => 'Super Administrador',
            'description' => 'Acceso total al sistema'
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrador',
            'description' => 'Gestiona el sistema'
        ]);

        $handyman = Role::create([
            'name' => 'handyman',
            'display_name' => 'Handyman',
            'description' => 'Presta servicios'
        ]);

        $client = Role::create([
            'name' => 'client',
            'display_name' => 'Cliente',
            'description' => 'Solicita servicios'
        ]);

        // Crear Permisos
        $permissions = [
            // Usuarios
            ['name' => 'users.view', 'display_name' => 'Ver Usuarios'],
            ['name' => 'users.create', 'display_name' => 'Crear Usuarios'],
            ['name' => 'users.edit', 'display_name' => 'Editar Usuarios'],
            ['name' => 'users.delete', 'display_name' => 'Eliminar Usuarios'],
            
            // Roles
            ['name' => 'roles.view', 'display_name' => 'Ver Roles'],
            ['name' => 'roles.create', 'display_name' => 'Crear Roles'],
            ['name' => 'roles.edit', 'display_name' => 'Editar Roles'],
            ['name' => 'roles.delete', 'display_name' => 'Eliminar Roles'],
            
            // Handymen
            ['name' => 'handymen.view', 'display_name' => 'Ver Handymen'],
            ['name' => 'handymen.approve', 'display_name' => 'Aprobar Handymen'],
            ['name' => 'handymen.edit', 'display_name' => 'Editar Handymen'],
            ['name' => 'handymen.delete', 'display_name' => 'Eliminar Handymen'],
            
            // Servicios
            ['name' => 'services.view', 'display_name' => 'Ver Servicios'],
            ['name' => 'services.create', 'display_name' => 'Crear Servicios'],
            ['name' => 'services.edit', 'display_name' => 'Editar Servicios'],
            ['name' => 'services.delete', 'display_name' => 'Eliminar Servicios'],
            
            // Solicitudes
            ['name' => 'requests.view', 'display_name' => 'Ver Solicitudes'],
            ['name' => 'requests.manage', 'display_name' => 'Gestionar Solicitudes'],
            
            // Pagos
            ['name' => 'payments.view', 'display_name' => 'Ver Pagos'],
            ['name' => 'payments.manage', 'display_name' => 'Gestionar Pagos'],
            
            // Reportes
            ['name' => 'reports.view', 'display_name' => 'Ver Reportes'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Asignar todos los permisos a superadmin
        $superadmin->permissions()->attach(Permission::all());

        // Asignar permisos a admin (todos excepto roles)
        $admin->permissions()->attach(Permission::whereNotIn('name', ['roles.create', 'roles.edit', 'roles.delete'])->get());

        // Crear Super Admin
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '+1 (678) 381-5907',
            'status' => 'active',
        ]);
        $superAdminUser->roles()->attach($superadmin);

        // Crear Admin
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '+1 (678) 394-6860',
            'status' => 'active',
        ]);
        $adminUser->roles()->attach($admin);

        // Crear Categorías de Servicio
        ServiceCategory::create([
            'name' => 'Servicio de Pintura',
            'slug' => 'pintura',
            'description' => 'Servicios profesionales de pintura para interiores y exteriores',
            'icon' => 'fas fa-paint-roller',
            'is_active' => true,
        ]);

        ServiceCategory::create([
            'name' => 'Soluciones Eléctricas',
            'slug' => 'electricidad',
            'description' => 'Instalaciones y reparaciones eléctricas',
            'icon' => 'fas fa-bolt',
            'is_active' => true,
        ]);

        ServiceCategory::create([
            'name' => 'Plomería',
            'slug' => 'plomeria',
            'description' => 'Reparación e instalación de sistemas de plomería',
            'icon' => 'fas fa-wrench',
            'is_active' => true,
        ]);

        ServiceCategory::create([
            'name' => 'Remodelación',
            'slug' => 'remodelacion',
            'description' => 'Remodelación de baños, cocinas, oficinas',
            'icon' => 'fas fa-hammer',
            'is_active' => true,
        ]);

        ServiceCategory::create([
            'name' => 'Mantenimiento General',
            'slug' => 'mantenimiento-general',
            'description' => 'Reparaciones generales del hogar',
            'icon' => 'fas fa-tools',
            'is_active' => true,
        ]);

        ServiceCategory::create([
            'name' => 'Mantenimiento Preventivo',
            'slug' => 'mantenimiento-preventivo',
            'description' => 'Inspección y mantenimiento preventivo',
            'icon' => 'fas fa-clipboard-check',
            'is_active' => true,
        ]);
    }
}