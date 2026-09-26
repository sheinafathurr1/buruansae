<footer class="footer mt-auto pt-0 pb-1 bg-white" style="max-width:2048px;">
    <div class="row justify-content-center text-center">
      <div class="col pt-2">
          <a href="/" class="footer {{ Request::is('/') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('/') ? 'solid':'light' }} fa-house icon-footer"></i>
              <span class="text-footer">Home</span>
          </a>
      </div>
      {{-- Sembunyikan Harvest --}}
      <div class="col pt-2 d-none">
          <a href="harvest" class="footer {{ Request::is('harvest') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('harvest') ? 'solid':'light' }} fa-chart-line-up icon-footer"></i>
              <span class="text-footer">{{ __('messages.menuHarvest') }}</span>
          </a>
      </div>
      {{-- Sembunyikan Seed --}}
      <div class="col pt-2 d-none">
          <a href="seed" class="footer {{ Request::is('seed') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('seed') ? 'solid':'light' }} fa-hand-holding-seedling icon-footer"></i>
              <span class="text-footer">{{ __('messages.menuSeed') }}</span>
          </a>
      </div>
      <!-- <div class="col pt-2">
          <a href="distribution" class="footer {{ Request::is('distribution') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('distribution') ? 'solid':'light' }} fa-chart-simple icon-footer"></i>
              <span class="text-footer">{{ __('messages.menuDistribution') }}</span>
          </a>
      </div> -->
      <div class="col pt-2">
          <a href="/news" class="footer {{ Request::is('*news*') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('*news*') ? 'solid':'light' }} fa-newspaper icon-footer"></i>
              <span class="text-footer">Berita</span>
          </a>
      </div>
      <div class="col pt-2">
          <a href="/map" class="footer {{ Request::is('map') ? 'active-menu':'' }}">
              <i class="fa-{{ Request::is('map') ? 'solid':'light' }} fa-map icon-footer"></i>
              <span class="text-footer">Peta</span>
          </a>
      </div>
    </div>
  </footer>
