<table border="1">
    <tr>
        <td>Nama</td>
        <td>Slug</td>
        <td>Deskripsi</td>  
        
    
@foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->description ?? '-' }}</td>
                    <td>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="px-6 py-4">
    <a
        href="{{ route('categories.edit', $category) }}"
        class="text-sm font-medium text-blue-600 hover:text-blue-800"
    >
        Edit
    </a>
</td>
                </tr>
@endforeach
</table>