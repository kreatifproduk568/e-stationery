<?php

use Livewire\Component;

new class extends Component
{
    // Deklarasi variabel - membuat STATE
    // public Access Modifier / Hak Akses, bisa di akses di seluluh bagian program
    // $nama Nama Property/Variabel
    // "Oegik" Assignment / Pengisian Nilai

    public $nama = "Oegik";
    public $kelas = "12 SIJA A";
    public $kelompok = "01";

    // membuat action method
    public function ubahNama(){
        $this->nama = "John Doe";
    }
};
?>

<div>
    <p>E-STATIONERY</p>
    <p>Livewire berhasil dijalankan</p>


<p>Nama: {{ $nama }}</p>
<p>Kelas: {{ $kelas }}</p>
<p>Kelompok: {{ $kelompok}}</p>
<button 
    wire:click="ubahNama"
    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
    Ubah Nama
</button>

</div>