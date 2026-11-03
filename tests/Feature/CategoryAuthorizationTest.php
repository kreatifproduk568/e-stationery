<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Category;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_store_does_not_have_category_delete_permission(): void
    {
        // Buat permission yang diuji.
        $permission = Permission::findOrCreate(
            'categories.delete',
            'web'
        );

        // Buat role Staff Gudang.
        $role = Role::findOrCreate(
            'Staff Gudang',
            'web'
        );

        // Pastikan role tidak memiliki permission hapus.
        $role->revokePermissionTo($permission);

        // Buat akun pengujian di database SQLite.
        $staff = User::factory()->create([
            'name' => 'Staff Gudang',
            'email' => 'staff-test@smartstationery.test',
        ]);

        // Tetapkan role Staff Gudang.
        $staff->assignRole($role);

        // Verifikasi bahwa staff tidak dapat menghapus kategori.
        $this->assertFalse(
            $staff->fresh()->can('categories.delete')
        );
    }

    
public function test_staff_store_can_access_category_page(): void
{
    Permission::findOrCreate('categories.view', 'web');

    $role = Role::findOrCreate('Staff Gudang', 'web');
    $role->givePermissionTo('categories.view');

    $staff = User::factory()->create([
        'name' => 'Staff Gudang',
        'email' => 'staff-page-test@smartstationery.test',
    ]);

    $staff->assignRole($role);

    $this->actingAs($staff)
        ->get(route('categories.index'))
        ->assertOk();
}
    public function test_staff_store_cannot_open_delete_confirmation(): void
{
    Permission::findOrCreate('categories.view', 'web');
    Permission::findOrCreate('categories.delete', 'web');

    $role = Role::findOrCreate('Staff Gudang', 'web');
    $role->givePermissionTo('categories.view');
    $role->revokePermissionTo('categories.delete');

    $staff = User::factory()->create([
        'name' => 'Staff Gudang',
        'email' => 'staff-confirm-test@smartstationery.test',
    ]);

    $staff->assignRole($role);

    $category = Category::create([
        'name' => 'Alat Tulis Test',
        'slug' => 'alat-tulis-test',
    ]);

    $this->actingAs($staff);

    $componentClass = app('livewire.factory')
        ->resolveComponentClass('categories');

    Livewire::test($componentClass)
        ->call('confirmDelete', $category->id)
        ->assertForbidden();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'slug' => 'alat-tulis-test',
    ]);
}

    
public function test_staff_store_cannot_delete_category_directly(): void
{
    Permission::findOrCreate('categories.view', 'web');
    Permission::findOrCreate('categories.delete', 'web');

    $role = Role::findOrCreate('Staff Gudang', 'web');
    $role->givePermissionTo('categories.view');
    $role->revokePermissionTo('categories.delete');

    $staff = User::factory()->create([
        'name' => 'Staff Gudang',
        'email' => 'staff-direct-delete-test@smartstationery.test',
    ]);

    $staff->assignRole($role);

    $category = Category::create([
        'name' => 'Kategori Uji Hapus',
        'slug' => 'kategori-uji-hapus',
    ]);

    $this->actingAs($staff);

    $componentClass = app('livewire.factory')
        ->resolveComponentClass('categories');

    Livewire::test($componentClass)
        ->set('deleteId', $category->id)
        ->call('deleteCategory')
        ->assertForbidden();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'slug' => 'kategori-uji-hapus',
    ]);
}

}
