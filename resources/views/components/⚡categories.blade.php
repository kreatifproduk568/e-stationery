<?php

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new  class extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    // State untuk pencarian
    public string $search = '';

    // State untuk modal delete
    public bool $showDeleteModal = false;

    // ID category yang akan dihapus
    public ?string $deleteId = null;

    // Nama category yang akan dihapus
    public string $deleteName = '';


    /*
    |--------------------------------------------------------------------------
    | COMPUTED PROPERTY
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->when(
                $this->search,
                fn ($query) =>
                    $query->where(
                        'name',
                        'like',
                        '%' . $this->search . '%'
                    )
            )
            ->latest()
            ->paginate(2);
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        // Kembali ke halaman pertama
        // ketika keyword pencarian berubah.
        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | OPEN DELETE MODAL
    |--------------------------------------------------------------------------
    */

    public function confirmDelete(string $id): void
    {
        $this->authorize('categories.delete');

        $category = Category::findOrFail($id);

        // Simpan ID category
        $this->deleteId = $category->id;

        // Simpan nama untuk ditampilkan pada modal
        $this->deleteName = $category->name;

        // Buka modal
        $this->showDeleteModal = true;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function deleteCategory(): void
    {
        $this->authorize('categories.delete');

        // Pengaman jika tidak ada ID
        if (!$this->deleteId) {
            return;
        }

        $category = Category::findOrFail($this->deleteId);

        $category->delete();

        /*
        |--------------------------------------------------------------------------
        | RESET STATE MODAL
        |--------------------------------------------------------------------------
        */

        $this->showDeleteModal = false;

        $this->deleteId = null;

        $this->deleteName = '';


        /*
        |--------------------------------------------------------------------------
        | FLASH MESSAGE
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'Category berhasil dihapus.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL DELETE
    |--------------------------------------------------------------------------
    */

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;

        $this->deleteId = null;

        $this->deleteName = '';
    }

    public function mount(): void
    {
        $this->authorize('categories.view');
    }
};
?>


<div class="p-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="mb-6 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Category
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data category produk.
            </p>
        </div>


        {{-- Tombol Tambah --}}

        @can('categories.create')
            <a
                href="{{ route('categories.create') }}"
                class="rounded-lg bg-gray-900
                    px-4 py-2
                    text-sm font-medium
                    text-white
                    hover:bg-gray-800"
            >
                + Tambah Category
            </a>
        @endcan

    </div>


    {{-- =========================================================
         FLASH MESSAGE
    ========================================================== --}}

    @if (session()->has('success'))

        <div
            class="mb-5 rounded-lg
                   border border-green-200
                   bg-green-50
                   px-4 py-3
                   text-sm text-green-700"
        >
            {{ session('success') }}
        </div>

    @endif


    {{-- =========================================================
         SEARCH
    ========================================================== --}}

    <div
        class="mb-5 rounded-xl
               border border-gray-200
               bg-white
               p-4"
    >

        <label
            for="search"
            class="mb-2 block
                   text-sm font-medium
                   text-gray-700"
        >
            Cari Category
        </label>

        <input
            id="search"
            type="text"
            wire:model.live="search"
            placeholder="Cari nama category..."
            class="w-full rounded-lg
                   border border-gray-300
                   px-4 py-2
                   text-sm
                   focus:border-gray-500
                   focus:outline-none
                   focus:ring-1
                   focus:ring-gray-500"
        >

    </div>


    {{-- =========================================================
         TABLE
    ========================================================== --}}

    <div
        class="overflow-hidden
               rounded-xl
               border border-gray-200
               bg-white"
    >

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                {{-- TABLE HEADER --}}

                <thead class="border-b border-gray-200 bg-gray-50">

                    <tr>

                        <th
                            class="px-6 py-3
                                   text-left
                                   font-semibold
                                   text-gray-700"
                        >
                            #
                        </th>

                        <th
                            class="px-6 py-3
                                   text-left
                                   font-semibold
                                   text-gray-700"
                        >
                            Name
                        </th>

                        <th
                            class="px-6 py-3
                                   text-left
                                   font-semibold
                                   text-gray-700"
                        >
                            Slug
                        </th>

                        <th
                            class="px-6 py-3
                                   text-left
                                   font-semibold
                                   text-gray-700"
                        >
                            Description
                        </th>

                        <th
                            class="px-6 py-3
                                   text-center
                                   font-semibold
                                   text-gray-700"
                        >
                            Status
                        </th>

                        <th
                            class="px-6 py-3
                                   text-right
                                   font-semibold
                                   text-gray-700"
                        >
                            Action
                        </th>

                    </tr>

                </thead>


                {{-- TABLE BODY --}}

                <tbody class="divide-y divide-gray-100">

                    @forelse ($this->categories as $category)

                        <tr class="hover:bg-gray-50">

                            {{-- NUMBER --}}

                            <td
                                class="px-6 py-4
                                       text-gray-500"
                            >
                                {{ $this->categories->firstItem() + $loop->index }}
                            </td>


                            {{-- NAME --}}

                            <td
                                class="px-6 py-4
                                       font-medium
                                       text-gray-800"
                            >
                                {{ $category->name }}
                            </td>


                            {{-- SLUG --}}

                            <td
                                class="px-6 py-4
                                       text-gray-500"
                            >
                                {{ $category->slug }}
                            </td>


                            {{-- DESCRIPTION --}}

                            <td
                                class="max-w-xs
                                       px-6 py-4
                                       text-gray-500"
                            >
                                {{ $category->description ?: '-' }}
                            </td>


                            {{-- STATUS --}}

                            <td
                                class="px-6 py-4
                                       text-center"
                            >

                                @if ($category->is_active)

                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-green-100
                                               px-3 py-1
                                               text-xs
                                               font-medium
                                               text-green-700"
                                    >
                                        Aktif
                                    </span>

                                @else

                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-gray-100
                                               px-3 py-1
                                               text-xs
                                               font-medium
                                               text-gray-600"
                                    >
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}

                            <td
                                class="px-6 py-4
                                       text-right"
                            >

                                <div
                                    class="flex
                                           justify-end
                                           gap-3"
                                >

                                    {{-- EDIT --}}

                                    @can('categories.update')
                                        <a
                                            href="{{ route('categories.edit', $category) }}"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800"
                                        >
                                            Edit
                                        </a>
                                    @endcan


                                    {{-- DELETE --}}

                                    @can('categories.delete')
                                        <button
                                            type="button"
                                            wire:click="confirmDelete('{{ $category->id }}')"
                                            class="text-sm font-medium text-red-600 hover:text-red-800"
                                        >
                                            Hapus
                                        </button>
                                    @endcan

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-10
                                       text-center
                                       text-gray-500"
                            >

                                @if ($search)

                                    Tidak ditemukan category
                                    dengan kata:

                                    <strong>
                                        "{{ $search }}"
                                    </strong>

                                @else

                                    Belum ada data category.

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        @if ($this->categories->hasPages())

            <div
                class="border-t
                       border-gray-200
                       px-6 py-4"
            >

                {{ $this->categories->links() }}

            </div>

        @endif

    </div>


    {{-- =========================================================
         DELETE MODAL
    ========================================================== --}}

    @if ($showDeleteModal)

        <div
            class="fixed inset-0 z-50
                   flex items-center
                   justify-center
                   bg-black/50
                   p-4"
        >

            {{-- MODAL --}}

            <div
                class="w-full max-w-md
                       overflow-hidden
                       rounded-xl
                       bg-white
                       shadow-2xl"
            >


                {{-- =================================================
                     MODAL HEADER
                ================================================== --}}

                <div
                    class="border-b
                           border-gray-200
                           px-6 py-4"
                >

                    <div class="flex items-center gap-3">

                        {{-- Icon --}}

                        <div
                            class="flex h-10 w-10
                                   items-center
                                   justify-center
                                   rounded-full
                                   bg-red-100"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5
                                       text-red-600"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0
                                       1 1-18 0 9 9 0 0118 0z
                                       M12 16.5h.008v.008H12V16.5z"
                                />

                            </svg>

                        </div>


                        {{-- Title --}}

                        <div>

                            <h2
                                class="text-lg
                                       font-semibold
                                       text-gray-800"
                            >
                                Konfirmasi Hapus
                            </h2>

                            <p
                                class="text-sm
                                       text-gray-500"
                            >
                                Tindakan ini perlu dikonfirmasi.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     MODAL CONTENT
                ================================================== --}}

                <div class="px-6 py-5">

                    <p
                        class="text-sm
                               leading-6
                               text-gray-600"
                    >
                        Apakah Anda yakin ingin menghapus
                        category berikut?
                    </p>


                    {{-- CATEGORY NAME --}}

                    <div
                        class="mt-4 rounded-lg
                               border border-gray-200
                               bg-gray-50
                               px-4 py-3"
                    >

                        <p
                            class="text-xs
                                   font-medium
                                   uppercase
                                   tracking-wide
                                   text-gray-500"
                        >
                            Category
                        </p>

                        <p
                            class="mt-1
                                   font-semibold
                                   text-gray-800"
                        >
                            {{ $deleteName }}
                        </p>

                    </div>


                    {{-- WARNING --}}

                    <p
                        class="mt-4
                               text-sm
                               text-red-600"
                    >
                        Data yang sudah dihapus
                        tidak dapat dikembalikan.
                    </p>

                </div>


                {{-- =================================================
                     MODAL FOOTER
                ================================================== --}}

                <div
                    class="flex
                           justify-end
                           gap-3
                           border-t
                           border-gray-200
                           bg-gray-50
                           px-6 py-4"
                >

                    {{-- BATAL --}}

                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="rounded-lg
                               border
                               border-gray-300
                               bg-white
                               px-4 py-2
                               text-sm
                               font-medium
                               text-gray-700
                               hover:bg-gray-100"
                    >
                        Batal
                    </button>


                    {{-- DELETE --}}

                    <button
                        type="button"
                        wire:click="deleteCategory"
                        wire:loading.attr="disabled"
                        wire:target="deleteCategory"
                        class="rounded-lg
                               bg-red-600
                               px-4 py-2
                               text-sm
                               font-medium
                               text-white
                               hover:bg-red-700
                               disabled:cursor-not-allowed
                               disabled:opacity-50"
                    >

                        <span wire:loading.remove wire:target="deleteCategory">
                            Ya, Hapus
                        </span>

                        <span wire:loading wire:target="deleteCategory">
                            Menghapus...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    @endif

</div>