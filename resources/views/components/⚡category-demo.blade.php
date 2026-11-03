<?php

use Livewire\Component;
use App\Models\Category;

new class extends Component
{
    public function categories()
    {
        return Category::query()->paginate(2);
    }
};
?>

<div>
    
    <a href="{{ route('category.tambah') }}" class="btn btn-primary">Tambah Category</a>

    <table style="width: 100%; border: 1px solid black;">
        <tr>
            <td>Name</td>
            <td>Description</td>
        </tr>
      
            @foreach($this->categories() as $category)
            <tr>
                <td> {{ $category->name }} </td>
                <td> {{ $category->description }} </td>
            </tr>
            @endforeach

     </table>

     {{ $this->categories()->links() }}
            
   





   
</div>