@extends('layouts.app')

@section('page_name', 'HOME')
@section('content')
@push('styles')
<style>
  /* Menjadikan elemen di dalam link 'transparan' terhadap klik mouse */
 [id^="sector-"] {
    display: block;
    pointer-events: auto !important;
  }

  /* Pastikan saat parent di-hover, teks di dalamnya berubah warna */
  [id^="sector-"]:hover .feature-content p {
    color: #eab308 !important; /* Warna kuning yellow-500 */
  }

  /* Gambar dibuat tembus klik agar tidak mengganggu selector Katalon */
  [id^="sector-"] img {
    pointer-events: none !important;
  }
</style>
@endpush
@include('layouts.header')
<div style="max-width: 2048px; margin:auto;">
  <div class="banner banner-pad-1 bg_img md:h-screen content-center">
    <div class="container  ">
      <div class="banner-content">
          <div class="banner-title">BURUAN SAE</div>
          <div class="banner-desc">Urban Farming yang Terintegrasi</div>
          <a href="#about" class="lab-btn"><span>Discover </span></a>
      </div>
  </div>
</div>

<section id="about" class="agricul-farm-section padding-tb bg_img_1 bg_img px-2 ">
  <div class="container">
      <div class="section-wrapper">
              <div class="col-lg-12 col-12 wow fadeInUp" data-wow-delay="0.4s">
                  <div class="farm-right">
                      <div class="section-header text-center flex gap-4 flex-col">
                          <p class="title-about text-xl md:text-6xl font-bold ">What is<br><span style="color: #2f589b;">BURUAN SAE</span> <span style="color: #ce254a;">?</span></p>
                          <p class="text-sm md:text-2xl">Buruan Sae is an integrated urban farming program promoted by the Food and Agriculture Security Department (DKPP) of Bandung City, which is aimed at overcoming the inequality of food problems in Bandung City. through utilizing the yard or existing land through gardening to meet the family's own food needs.</p>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</section>

<section class="feature-section padding-tb">
  <div class="shape-feature d-none d-xl-block"></div>
  <div class="container py-2">
    <div class="section-header wow fadeInUp" data-wow-delay="0.3s">
      <p class="text-xl md:text-4xl font-bold">Buruan Sae's Sectors</p>
    </div>
    <div class="section-wrapper">
  <div class="row justify-content-center">

    <!-- Vegetable -->
    <a href="/vegetable?sector=sayur" id="sector-vegetable" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.4s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/vegetable.png" alt="Vegetable" width="120">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Sayur-sayuran</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Tanaman Obat -->
    <a href="/medicalplant?sector=tanaman_obat" id="sector-medicalplant" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.7s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/herbplant.png" alt="Tanaman Obat" width="100">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Tanaman Obat</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Fruit -->
    <a href="/fruit?sector=buah" id="sector-fruit" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.5s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/fruit.png" alt="Fruit" width="115">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Buah</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Ternak -->
    <a href="/livestock?sector=ternak" id="sector-livestock" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.7s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/animal.png" alt="Ternak" width="125">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Ternak</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Ikan -->
    <a href="/fish?sector=ikan" id="sector-fish" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.6s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/fish.png" alt="Ikan" width="120">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Ikan</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Olahan Hasil -->
    <a href="#" id="comingSoonBtn" id="sector-result" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.7s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/result.png" alt="Olahan Hasil" width="130">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Olahan Hasil</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Pengolahan Sampah -->
    <a href="/waste-processing?sector=sampah" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.7s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/compost.png" alt="Pengolahan Sampah" width="130">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Pengolahan Sampah</p>
          </div>
        </div>
      </div>
    </a>

    <!-- Pembibitan -->
    <a href="/nursery?sector=bibit" class="block col-xl-6 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.7s">
      <div class="feature-item">
        <div class="feature-inner">
          <div class="feature-thumb flex justify-center">
            <img src="assets/images/nursery.png" alt="Pembibitan" width="120">
          </div>
          <div class="feature-content">
            <p class="text-md md:text-2xl font-bold hover:text-yellow-500">Pembibitan</p>
          </div>
        </div>
      </div>
    </a>

  </div>
</div>

  </div>
</section>

<section class="about-us bg_img_1 bg_img padding-tb mt-4 ">
  <div class="shape-about d-none d-xl-block"></div>
  <div class="container flex justify-center items-center px-10 py-4">
    <div class="row">
      <div class="col-lg-12 col-12">

        <!-- Section Header -->
        <div class="section-header text-start wow fadeInUp flex flex-col gap-3" data-wow-delay="0.3s">
          <h2 class="text-xl md:text-6xl font-bold">Tagline Kami</h2>
          <p>
            Melalui program ini Pemkot Bandung berharap bahwa masyarakat dapat belajar untuk
            memproduksi bahan pangannya sendiri, sehingga makanan yang dikonsumsi dapat lebih
            sehat, alami, serta ekonomis.
          </p>
        </div>

        <!-- Section Content -->
        <div class="section-wrapper">

          <!-- Sehat -->
          <div class="about-item wow fadeInUp" data-wow-delay="0.4s">
            <div class="about-inner">
              <div class="about-thumb">
                <img src="assets/images/tagline/1.png" alt="Sehat">
              </div>
              <div class="about-content flex flex-col gap-1">
                <a href="#"><h5 class="text-md md:text-2xl font-bold hover:text-yellow-500">SEHAT</h5></a>
                <p>
                  Bahan pangan tersebut dikelola sendiri langsung oleh masyarakat sehingga terjaga
                  prosesnya dan tidak banyak menggunakan pestisida kimia.
                </p>
              </div>
            </div>
          </div>

          <!-- Alami -->
          <div class="about-item wow fadeInUp" data-wow-delay="0.5s">
            <div class="about-inner">
              <div class="about-thumb">
                <img src="assets/images/tagline/2.png" alt="Alami">
              </div>
              <div class="about-content flex flex-col gap-1">
                <a href="#"><h5 class="text-md md:text-2xl font-bold hover:text-yellow-500">ALAMI</h5></a>
                <p>
                  Produk langsung dari alam dan diolah dengan media pupuk serta pestisida alami.
                </p>
              </div>
            </div>
          </div>

          <!-- Ekonomis -->
          <div class="about-item wow fadeInUp" data-wow-delay="0.6s">
            <div class="about-inner">
              <div class="about-thumb">
                <img src="assets/images/tagline/3.png" alt="Ekonomis" class="w-40 h-18">
              </div>
              <div class="about-content flex flex-col gap-1">
                <a href="#"><h5 class="text-md md:text-2xl font-bold hover:text-yellow-500">EKONOMIS</h5></a>
                <p>
                  Mampu menghasilkan bahan pangan yang bisa dikonsumsi sendiri atau dijual
                  dalam jumlah mikro.
                </p>
              </div>
            </div>
          </div>

        </div><!-- /.section-wrapper -->

      </div>
    </div>
  </div>
</section>

<div class="sponsor-section padding-tb" style="background-color: #a9bfd7;">
  <div class="container">
    <div class="section-wrapper wow fadeInUp" data-wow-delay="0.4s">
      <div class="sponsor-slider">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <div class="sponsor-item">
              <div class="sponsor-thumb">
                <a href="#"><img src="assets/images/sponsor/dkpp.png" alt="sponsor"></a>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="sponsor-item">
              <div class="sponsor-thumb">
                <a href="#"><img src="assets/images/sponsor/bandungo.png" alt="sponsor"></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="footer-section padding-tb">
  <div class="container">
    <div class="footer-top">
      <div class="row">
        <div class="col-xl-12 col-md-6 col-12">
          <div class="footer-item">
            <div class="footer-inner">
              <div class="footer-logo">
                <img src="assets/images/logo.png" alt="Buruan Sae Logo" width="200">
              </div>
              <div class="footer-desc">
                <p>
                  Buruan Sae adalah sebuah program urban farming terintegrasi yang
                  digalakan oleh Dinas Ketahanan Pangan dan Pertanian (DKPP) Kota Bandung.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-12 col-md-6 col-12">
          <div class="footer-item footer-addtess">
            <div class="footer-inner">
              <div class="footer-title">
                <h5>Keep In Touch</h5>
              </div>
              <div class="footer-body">
                <ul class="agri-ul">
                  <li>
                    <div class="icon">
                      <i class="icofont-google-map"></i>
                    </div>
                    <div class="details">
                      <p>Jl. Arjuna No.45, Bandung, Jawa Barat, Indonesia</p>
                    </div>
                  </li>
                  <li>
                    <div class="icon">
                      <i class="icofont-phone"></i>
                    </div>
                    <div class="details">
                      <p>022-6015102</p>
                    </div>
                  </li>
                  <li>
                    <div class="icon">
                      <i class="icofont-envelope"></i>
                    </div>
                    <div class="details">
                      <p>dispangtan@bandung.go.id</p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>Copyright &copy; {{ date('Y') }}<a href="#" target="_blank"><span>BURUAN SAE</span></a><br>All Rights Reserved.
    </div>
  </div>
</div>
  <a href="#" class="scrollToTop"><i class="icofont-swoosh-up"></i><span class="pluse_1"></span><span class="pluse_2"></span></a>
</div>

<div class="row mt-4 d-none">
  <div class="col-lg-12">
    <div id="kecamatan-cards" class="row"></div>
  </div>
</div>

@endsection
@push('scripts')
<script>
  document.getElementById('comingSoonBtn').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
      title: "Coming Soon!",
      text: "Fitur Belum Tersedia Saat ini",
      icon: "info",
      confirmButtonText: "OK"
    });
  });
if (window.jQuery) {
        $('.wow').removeClass('wow').css({
            'visibility': 'visible',
            'animation-name': 'none',
            'opacity': '1'
        });
    }
</script>
@endpush
