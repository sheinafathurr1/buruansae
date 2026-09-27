<?php

return [

    /*
    | Identitas & kontak yang tampil di header/footer.
    */
    'agency' => 'Dinas Ketahanan Pangan dan Pertanian Kota Bandung',
    'agency_short' => 'DKPP Kota Bandung',
    'contact' => [
        'address' => 'Jl. Arjuna No. 45, Bandung, Jawa Barat',
        'phone' => '022-6015102',
        'email' => 'dispangtan@bandung.go.id',
    ],

    /*
    | Foto pembuka beranda. Artikel terkaitnya tidak diulang di daftar berita
    | beranda, dan keterangan fotonya menautkan ke artikel itu.
    */
    'hero' => [
        'image' => 'images/news/WhatsApp-Image-2022-06-23-at-15.55.44-1-770x428.jpeg',
        'alt' => 'Deretan tanaman dalam pot di bawah atap paranet biru, di sepanjang gang permukiman',
        'caption' => 'Kebun kelompok Buruan SAE Sajuta Saratus di gang RW 04, Kelurahan Cipaganti, Kecamatan Coblong.',
        'article' => 'buruan-sae-sajuta-saratus',
    ],

    /*
    | Peta sebaran: pusat, zoom, dan batas wilayah Kota Bandung. Kelurahan dengan
    | koordinat di luar batas "locations_bounds" tidak dikirim oleh /api/locations
    | (sama dengan aplikasi lama).
    */
    'map' => [
        'center' => [-6.9175, 107.6191],
        'max_bounds' => [[-7.2150, 107.3500], [-6.7500, 107.8500]],
        'locations_bounds' => ['south' => -7.05, 'west' => 107.45, 'north' => -6.80, 'east' => 107.75],
        'cache_seconds' => 300,
    ],

];
