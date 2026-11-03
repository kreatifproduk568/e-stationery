<?php

use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new  class extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    // Keyword pencarian
    public string $search = '';

    // Flash Message
    public bool $showSuccessMessage = true;

    // Modal delete
    public bool $showDeleteModal = false;

    // ID brand yang akan dihapus
    public ?string $deleteId = null;

    // Nama brand yang akan dihapus
    public string $deleteName = '';


    
    /*
    |--------------------------------------------------------------------------
    | COMPUTED PROPERTY
    |--------------------------------------------------------------------------
    */

    public function getBrandsProperty()
    {
        return Brand::query()
            ->withCount('products')
            ->when(
                $this->search,
                fn ($query) =>
                    $query->where(function ($query) {
                        $query
                            ->where(
                                'name',
                                'like',
                                '%' . $this->search . '%'
                            )
                            ->orWhere(
                                'slug',
                                'like',
                                '%' . $this->search . '%'
                            );
                    })
            )
            ->latest()
            ->paginate(3);
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | CLOSE FLASH MESSAGE
    |--------------------------------------------------------------------------
    */

    public function closeSuccessMessage(): void
    {
        $this->showSuccessMessage = false;
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM DELETE
    |--------------------------------------------------------------------------
    */

    public function confirmDelete(string $id): void
    {
        $brand = Brand::findOrFail($id);

        $this->deleteId = $brand->id;

        $this->deleteName = $brand->name;

        $this->showDeleteModal = true;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function deleteBrand(): void
    {
        if (!$this->deleteId) {
            return;
        }

        $brand = Brand::findOrFail($this->deleteId);

        $brand->delete();

        /*
        |--------------------------------------------------------------------------
        | RESET MODAL STATE
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
            'Brand berhasil dihapus.'
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
};
?>


<div class="p-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="mb-6 flex items-center justify-between">

        <div>

            <h1 class="text-2xl font-bold text-gray-800">
                Brands
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data brand produk.
            </p>

        </div>


        {{-- TAMBAH BRAND --}}

        <a
            href="{{ route('brands.create') }}"
            class="rounded-lg
                   bg-gray-900
                   px-4 py-2
                   text-sm
                   font-medium
                   text-white
                   hover:bg-gray-800"
        >
            + Tambah Brand
        </a>

    </div>


    {{-- =========================================================
     FLASH MESSAGE
    ========================================================== --}}

    @if (session()->has('success') && $showSuccessMessage)

        <div
            class="mb-5 flex items-center justify-between
                rounded-lg
                border border-green-200
                bg-green-50
                px-4 py-3
                text-sm text-green-700"
        >

            {{-- PESAN --}}

            <span>
                {{ session('success') }}
            </span>


            {{-- CLOSE BUTTON --}}

            <button
                type="button"
                wire:click="closeSuccessMessage"
                class="ml-4
                    inline-flex
                    h-7 w-7
                    shrink-0
                    items-center
                    justify-center
                    rounded-md
                    text-lg
                    font-semibold
                    leading-none
                    text-green-600
                    hover:bg-green-100
                    hover:text-green-800
                    focus:outline-none"
                aria-label="Tutup pesan"
                title="Tutup"
            >
                &times;
            </button>

        </div>

    @endif


    {{-- =========================================================
         SEARCH
    ========================================================== --}}

    <div
        class="mb-5
               rounded-xl
               border border-gray-200
               bg-white
               p-4"
    >

        <label
            for="search"
            class="mb-2 block
                   text-sm
                   font-medium
                   text-gray-700"
        >
            Cari Brand
        </label>


        <input
            id="search"
            type="text"
            wire:model.live="search"
            placeholder="Cari nama atau slug brand..."
            class="w-full
                   rounded-lg
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

                <thead
                    class="border-b
                           border-gray-200
                           bg-gray-50"
                >

                    <tr>

                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wide
                                   text-gray-600"
                        >
                            Brand
                        </th>


                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wide
                                   text-gray-600"
                        >
                            Slug
                        </th>


                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wide
                                   text-gray-600"
                        >
                            Status
                        </th>


                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wide
                                   text-gray-600"
                        >
                            Product
                        </th>


                        <th
                            class="px-6 py-3
                                   text-right
                                   text-xs
                                   font-semibold
                                   uppercase
                                   tracking-wide
                                   text-gray-600"
                        >
                            Aksi
                        </th>

                    </tr>

                </thead>


                {{-- TABLE BODY --}}

                <tbody class="divide-y divide-gray-100">

                    @forelse ($this->brands as $brand)

                        <tr class="hover:bg-gray-50">


                            {{-- =================================
                                 BRAND
                            ================================== --}}

                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    {{-- Initial --}}

                                    <div
                                        class="flex h-11 w-11
                                               shrink-0
                                               items-center
                                               justify-center
                                               rounded-lg
                                               bg-gray-100
                                               text-sm
                                               font-medium
                                               text-gray-600"
                                    >
                                        {{ strtoupper(substr($brand->name, 0, 1)) }}
                                    </div>


                                    {{-- Name + Description --}}

                                    <div>

                                        <div
                                            class="font-semibold
                                                   text-gray-800"
                                        >
                                            {{ $brand->name }}
                                        </div>


                                        @if (!empty($brand->description))

                                            <div
                                                class="mt-1
                                                       text-xs
                                                       text-gray-500"
                                            >
                                                {{ $brand->description }}
                                            </div>

                                        @else

                                            <div
                                                class="mt-1
                                                       text-xs
                                                       text-gray-400"
                                            >
                                                Merek {{ $brand->name }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- =================================
                                 SLUG
                            ================================== --}}

                            <td
                                class="px-6 py-4
                                       text-gray-600"
                            >
                                {{ $brand->slug }}
                            </td>


                            {{-- =================================
                                 STATUS
                            ================================== --}}

                            <td
                                class="px-6 py-4
                                       text-center"
                            >

                                @if ($brand->is_active)

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


                            {{-- =================================
                                 PRODUCT COUNT
                            ================================== --}}

                            <td
                                class="px-6 py-4
                                       text-center"
                            >

                                <span
                                    class="inline-flex
                                           min-w-8
                                           justify-center
                                           rounded-full
                                           bg-gray-100
                                           px-2 py-1
                                           text-xs
                                           font-medium
                                           text-gray-700"
                                >
                                    {{ $brand->products_count }}
                                </span>

                            </td>


                            {{-- =================================
                                 ACTION
                            ================================== --}}

                            <td
                                class="px-6 py-4
                                       text-right"
                            >

                                <div
                                    class="flex
                                           justify-end
                                           gap-4"
                                >

                                    {{-- EDIT --}}

                                    <a
                                        href="{{ route(
                                            'brands.edit',
                                            $brand
                                        ) }}"
                                        class="text-sm
                                               font-medium
                                               text-blue-600
                                               hover:text-blue-800"
                                    >
                                        Edit
                                    </a>


                                    {{-- DELETE --}}

                                    <button
                                        type="button"
                                        wire:click="confirmDelete('{{ $brand->id }}')"
                                        class="text-sm
                                               font-medium
                                               text-red-600
                                               hover:text-red-800"
                                    >
                                        Hapus
                                    </button>

                                </div>

                            </td>

                        </tr>


                    @empty

                        {{-- =====================================
                             EMPTY DATA
                        ====================================== --}}

                        <tr>

                            <td
                                colspan="5"
                                class="px-6 py-12
                                       text-center
                                       text-gray-500"
                            >

                                @if ($search)

                                    Tidak ditemukan brand
                                    dengan kata:

                                    <strong class="text-gray-700">
                                        "{{ $search }}"
                                    </strong>

                                @else

                                    Belum ada data brand.

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

        @if ($this->brands->hasPages())

            <div
                class="border-t
                       border-gray-200
                       px-6 py-4"
            >

                {{ $this->brands->links() }}

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

            {{-- MODAL CONTAINER --}}

            <div
                class="w-full
                       max-w-md
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

                    <div
                        class="flex
                               items-center
                               gap-3"
                    >

                        {{-- WARNING ICON --}}

                        <div
                            class="flex
                                   h-10 w-10
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
                                    d="M12 9v3.75m9-.75a9 9
                                       0 1 1-18 0 9 9 0
                                       0118 0z
                                       M12 16.5h.008v.008H12V16.5z"
                                />

                            </svg>

                        </div>


                        {{-- TITLE --}}

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
                        brand berikut?
                    </p>


                    {{-- BRAND NAME --}}

                    <div
                        class="mt-4
                               rounded-lg
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
                            Brand
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


                    {{-- YA HAPUS --}}

                    <button
                        type="button"
                        wire:click="deleteBrand"
                        wire:loading.attr="disabled"
                        wire:target="deleteBrand"
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

                        <span
                            wire:loading.remove
                            wire:target="deleteBrand"
                        >
                            Ya, Hapus
                        </span>

                        <span
                            wire:loading
                            wire:target="deleteBrand"
                        >
                            Menghapus...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    @endif

</div>