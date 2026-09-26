<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.index');
    }

    public function news()
    {
        $articles = [
            [
                'id' => 1,
                'image' => 'assets/images/news/WhatsApp-Image-2022-06-23-at-15.55.44-1-770x428.jpeg',
                'title' => 'Buruan SAE Sajuta Saratus',
                'content' => 'Buruan SAE Sajuta Saratus terletak di RW 4 Kel. Cipaganti Kec. Coblong atau yang lebih tepatnya berada di dekat Pos Linmas Jl. Cihampelas Gg Masjid RT 7 RW 4 Kel. Cipaganti. Buruan SAE Sajuta Saratus memiliki luas lahan 100 m2 yang terus berkembang dan diperluas hingga saat ini. Buruan SAE Sajuta Saratus menjadi salah satu kelompok Buruan SAE yang dapat memanfaatkan dan memaksimalkan lahan yang tersedia. Lokasi lahan yang berada di dalam gang tidak menyurutkan semangat warga dan kelompok untuk terus berupaya menciptakan ketahanan pangan di lingkungannya.',
                'slug' => 'buruan-sae-sajuta-saratus',
            ],
            [
                'id' => 2,
                'image' => 'assets/images/news/WhatsApp-Image-2020-09-19-at-16.34.00-770x428.jpeg',
                'title' => 'Sekemala Integrated Farming (Seinfarm)',
                'content' => 'Sein Farm atau Sekelama Integrated Farming adalah salahsatu merk inovasi pertanian terpadu di Kota Bandung yang menggabungkan unsur – unsur pertanian, peternakan dan perikanan. Sekelama sendiri diambil dari nama jalan dimana SEIN FARM berada yaitu di Jalan Sekemala kelurahan Pasanggrahan, Kecamatan Ujungberung, Kota Bandung. Daerah ini merupakan daerah terluar dari Kota Bandung yang terdapat sawah abadi milik PEMKOT Bandung.',
                'slug' => 'sekemala-integrated-farming',
            ],
            [
                'id' => 3,
                'image' => 'assets/images/news/WhatsApp-Image-2021-11-18-at-13.04.44-800x445-1.jpeg',
                'title' => 'Buruan Sae Walagri RW 05 Rancabolang Gelar Pelatihan Urban Farming Bertajuk “Ngokolakeun Buruan Keur Ketahanan Pangan Warga RW 05',
                'content' => 'Buruan Sae Walagri RW 05 Kelurahan Rancabolang, Kecamatan Gedebage, Kota Bandung mengadakan kegiatan Pelatihan Urban Farming Buruan Sae bagi warga RW 05 dengan tema “Ngokolakeun Buruan Keur Ketahanan Pangan Warga RW 05”, Selasa (16/11/2021). Acara tersebut dihadiri oleh Kepala Dinas Ketahanan Pangan dan Pertanian (DKPP) Kota Bandung Ir. Gin Gin Ginanjar, M.Eng., Camat Gedebage Jaenudin, AP., M.Si., Lurah Rancabolang Ahmad Nurhasan, S.STP., Ketua RW 05 Rancabolang Dr. H. Kadar Nurjaman, SE., MM., dan para peserta pelatihan dari perwakilan masing-masing RT yang terdiri dari 3 orang dari 8 RT di lingkungan RW 05 Rancabolang.',
                'slug' => 'buruan-sae-walagri-rw-05',
            ],
        ];

        return view('home.news', compact('articles'));
    }

    public function blog($slug)
    {
        $articles = [
            [
                'id' => 1,
                'image' => 'assets/images/news/WhatsApp-Image-2022-06-23-at-15.55.44-1-770x428.jpeg',
                'title' => 'Buruan SAE Sajuta Saratus',
                'content' => 'Buruan SAE Sajuta Saratus terletak di RW 4 Kel. Cipaganti Kec. Coblong atau yang lebih tepatnya berada di dekat Pos Linmas Jl. Cihampelas Gg Masjid RT 7 RW 4 Kel. Cipaganti. Buruan SAE Sajuta Saratus memiliki luas lahan 100 m2 yang terus berkembang dan diperluas hingga saat ini. Buruan SAE Sajuta Saratus menjadi salah satu kelompok Buruan SAE yang dapat memanfaatkan dan memaksimalkan lahan yang tersedia. Lokasi lahan yang berada di dalam gang tidak menyurutkan semangat warga dan kelompok untuk terus berupaya menciptakan ketahanan pangan di lingkungannya.',
                'slug' => 'buruan-sae-sajuta-saratus',
            ],
            [
                'id' => 2,
                'image' => 'assets/images/news/WhatsApp-Image-2020-09-19-at-16.34.00-770x428.jpeg',
                'title' => 'Sekemala Integrated Farming (Seinfarm)',
                'content' => 'Sein Farm atau Sekelama Integrated Farming adalah salahsatu merk inovasi pertanian terpadu di Kota Bandung yang menggabungkan unsur – unsur pertanian, peternakan dan perikanan. Sekelama sendiri diambil dari nama jalan dimana SEIN FARM berada yaitu di Jalan Sekemala kelurahan Pasanggrahan, Kecamatan Ujungberung, Kota Bandung. Daerah ini merupakan daerah terluar dari Kota Bandung yang terdapat sawah abadi milik PEMKOT Bandung.',
                'slug' => 'sekemala-integrated-farming',
            ],
            [
                'id' => 3,
                'image' => 'assets/images/news/WhatsApp-Image-2021-11-18-at-13.04.44-800x445-1.jpeg',
                'title' => 'Buruan Sae Walagri RW 05 Rancabolang Gelar Pelatihan Urban Farming Bertajuk “Ngokolakeun Buruan Keur Ketahanan Pangan Warga RW 05',
                'content' => 'Buruan Sae Walagri RW 05 Kelurahan Rancabolang, Kecamatan Gedebage, Kota Bandung mengadakan kegiatan Pelatihan Urban Farming Buruan Sae bagi warga RW 05 dengan tema “Ngokolakeun Buruan Keur Ketahanan Pangan Warga RW 05”, Selasa (16/11/2021). Acara tersebut dihadiri oleh Kepala Dinas Ketahanan Pangan dan Pertanian (DKPP) Kota Bandung Ir. Gin Gin Ginanjar, M.Eng., Camat Gedebage Jaenudin, AP., M.Si., Lurah Rancabolang Ahmad Nurhasan, S.STP., Ketua RW 05 Rancabolang Dr. H. Kadar Nurjaman, SE., MM., dan para peserta pelatihan dari perwakilan masing-masing RT yang terdiri dari 3 orang dari 8 RT di lingkungan RW 05 Rancabolang.',
                'slug' => 'buruan-sae-walagri-rw-05',
            ],
        ];
        $article = collect($articles)->firstWhere('slug', $slug);

        if (!$article) {
            abort(404);
        }

        // Get all articles for related posts (excluding current article)
        $allArticles = collect($articles);

        return view('home.full-news', compact('article', 'allArticles'));
    }
}
