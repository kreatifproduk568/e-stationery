<?php

use Livewire\Component;
// mengakses model Product
use App\Models\Product;

new class extends Component
{
    public function products()
    {
        return Product::all();
    }
};
?>

<div>
   @foreach($this->products() as $product)
       <div> {{ $product->name }} </div>
    @endforeach
</div>