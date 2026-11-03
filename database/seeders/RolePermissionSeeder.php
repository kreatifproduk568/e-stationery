<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Kategori
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Merek
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',

            // Produk
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // Inventaris
            'inventory.view',
            'inventory.stock_in',
            'inventory.stock_out',
            'inventory.adjust',

            // Pesanan
            'orders.view',
            'orders.process',
            'orders.cancel',

            // Pembayaran
            'payments.view',
            'payments.verify',
            'payments.refund',

            // Pengiriman
            'shipments.view',
            'shipments.create',
            'shipments.update',

            // Pengguna
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Role dan Permission
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.assign',

            // Laporan
            'reports.sales.view',
            'reports.inventory.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'Super Admin' => $permissions,

            'Admin Produk' => [
                'dashboard.view',
                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',
                'brands.view',
                'brands.create',
                'brands.update',
                'brands.delete',
                'products.view',
                'products.create',
                'products.update',
                'products.delete',
                'inventory.view',
                'orders.view',
                'reports.inventory.view',
            ],

            'Staff Gudang' => [
                'dashboard.view',
                'products.view',
                'inventory.view',
                'inventory.stock_in',
                'inventory.stock_out',
                'inventory.adjust',
                'orders.view',
                'reports.inventory.view',
            ],

            'Staff Pesanan' => [
                'dashboard.view',
                'products.view',
                'orders.view',
                'orders.process',
                'shipments.view',
                'shipments.create',
                'shipments.update',
            ],

            'Customer' => [
                'products.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
