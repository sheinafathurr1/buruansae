import L from 'leaflet';

/**
 * Peta sebaran kelompok Buruan SAE per kelurahan.
 * Data: GET /api/locations → [{ id, name, district, district_id, latitude, longitude, total_kelompok, active_kelompok }]
 * Semua teks dari data dimasukkan lewat textContent (bukan innerHTML).
 */
const root = document.querySelector('[data-map-explorer]');

if (root) {
    const config = JSON.parse(root.dataset.mapExplorer);
    const el = {
        map: root.querySelector('[data-map]'),
        district: root.querySelector('[data-filter-district]'),
        search: root.querySelector('[data-filter-search]'),
        showEmpty: root.querySelector('[data-filter-empty]'),
        list: root.querySelector('[data-location-list]'),
        summary: root.querySelector('[data-summary]'),
        status: root.querySelector('[data-status]'),
        locate: root.querySelector('[data-locate]'),
        locateLabel: root.querySelector('[data-locate-label]'),
        locateHint: root.querySelector('[data-locate-hint]'),
        locateStatus: root.querySelector('[data-locate-status]'),
        locateClear: root.querySelector('[data-locate-clear]'),
    };
    const numberFormat = new Intl.NumberFormat('id-ID');
    const decimalFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
    // Jarak garis lurus ke titik kelurahan: "± 450 m", "± 1,2 km" (spasi tak terputus).
    const formatDistance = (meters) =>
        meters < 1000
            ? `±\u00A0${numberFormat.format(Math.max(10, Math.round(meters / 10) * 10))}\u00A0m`
            : `±\u00A0${decimalFormat.format(meters / 1000)}\u00A0km`;
    // Nama di database tersimpan HURUF BESAR → "Babakan Ciparay".
    const displayName = (value) =>
        String(value ?? '')
            .toLowerCase()
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');

    const map = L.map(el.map, {
        maxBounds: config.maxBounds,
        maxBoundsViscosity: 1.0,
        zoomControl: true,
        scrollWheelZoom: true,
    }).setView(config.center, window.innerWidth < 768 ? 11 : 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 10,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const layer = L.featureGroup().addTo(map);
    let locations = [];
    const markers = new Map();

    // Lokasi pengunjung ("Gunakan lokasi saya"). Hanya dipakai di browser untuk
    // menghitung jarak; tidak pernah dikirim ke server.
    const userLayer = L.layerGroup().addTo(map);
    const cityBounds = L.latLngBounds(config.maxBounds);
    const distances = new Map(); // id kelurahan → meter
    let userPosition = null;
    let nearestId = null;

    const markerIcon = (location) => {
        const count = location.total_kelompok;
        const size = count > 0 ? Math.min(26 + count * 3, 48) : 14;
        const html = document.createElement('div');
        html.className = 'map-marker';
        html.style.width = html.style.height = `${size}px`;
        html.style.background = count > 0 ? '#1a6f3e' : '#94a3b8'; // brand-700: kontras teks putih ≥ 4,5:1
        html.style.fontSize = `${size > 34 ? 13 : 11}px`;
        if (count > 0) html.textContent = String(count);

        return L.divIcon({ html, className: '', iconSize: [size, size], iconAnchor: [size / 2, size / 2], popupAnchor: [0, -size / 2] });
    };

    const popupContent = (location) => {
        const wrap = document.createElement('div');
        wrap.className = 'min-w-44';
        const title = document.createElement('p');
        title.className = 'text-sm font-bold text-slate-900 !m-0';
        title.textContent = `Kel. ${displayName(location.name)}`;
        const sub = document.createElement('p');
        sub.className = 'text-xs text-slate-500 !mt-0.5 !mb-2';
        sub.textContent = `Kec. ${displayName(location.district)}`;
        const stats = document.createElement('p');
        stats.className = 'text-sm text-slate-700 !m-0';
        stats.textContent = `${numberFormat.format(location.total_kelompok)} kelompok · ${numberFormat.format(location.active_kelompok)} aktif`;
        wrap.append(title, sub, stats);

        if (distances.has(location.id)) {
            const distance = document.createElement('p');
            distance.className = 'text-xs text-slate-500 !mt-1 !mb-0';
            distance.textContent = `${formatDistance(distances.get(location.id))} dari lokasi Anda`;
            wrap.append(distance);
        }

        return wrap;
    };

    const visibleLocations = () => {
        const districtId = el.district.value ? Number(el.district.value) : null;
        const term = el.search.value.trim().toLowerCase();

        return locations.filter(
            (location) =>
                (districtId === null || location.district_id === districtId) &&
                (el.showEmpty.checked || location.total_kelompok > 0) &&
                (term === '' || location.name.toLowerCase().includes(term) || location.district.toLowerCase().includes(term)),
        );
    };

    const focusLocation = (location) => {
        const marker = markers.get(location.id);
        if (!marker) return;
        map.flyTo(marker.getLatLng(), Math.max(map.getZoom(), 14), { duration: 0.6 });
        marker.openPopup();
    };

    const renderList = (items) => {
        el.list.replaceChildren();

        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'px-4 py-8 text-center text-sm text-slate-500';
            empty.textContent = 'Tidak ada kelurahan yang cocok dengan filter.';
            el.list.append(empty);
            return;
        }

        const byDistance = userPosition !== null;

        [...items]
            .sort((a, b) =>
                byDistance
                    ? distances.get(a.id) - distances.get(b.id)
                    : b.total_kelompok - a.total_kelompok || a.name.localeCompare(b.name),
            )
            .forEach((location) => {
                const item = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.className =
                    'flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-brand-50 focus-visible:bg-brand-50 focus-visible:outline-none';
                const text = document.createElement('span');
                text.className = 'min-w-0';
                const name = document.createElement('span');
                name.className = 'block truncate text-sm font-semibold text-slate-800';
                name.textContent = displayName(location.name);
                const district = document.createElement('span');
                district.className = 'block truncate text-xs text-slate-500';
                district.textContent = byDistance
                    ? `Kec. ${displayName(location.district)} · ${formatDistance(distances.get(location.id))}`
                    : `Kec. ${displayName(location.district)}`;
                if (location.id === nearestId) {
                    const tag = document.createElement('span');
                    tag.className = 'mb-0.5 inline-block rounded-full bg-brand-700 px-2 py-0.5 text-[11px] font-bold text-white';
                    tag.textContent = 'Terdekat';
                    text.append(tag);
                }
                text.append(name, district);
                const badge = document.createElement('span');
                badge.className = `num shrink-0 rounded-full px-2.5 py-1 text-xs font-bold ${location.total_kelompok > 0 ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-500'}`;
                badge.textContent = `${numberFormat.format(location.total_kelompok)} kelompok`;
                button.append(text, badge);
                button.addEventListener('click', () => focusLocation(location));
                item.append(button);
                el.list.append(item);
            });
    };

    const render = ({ fit = false } = {}) => {
        const items = visibleLocations();
        layer.clearLayers();
        markers.clear();

        items.forEach((location) => {
            const marker = L.marker([location.latitude, location.longitude], {
                icon: markerIcon(location),
                title: `Kel. ${displayName(location.name)}: ${location.total_kelompok} kelompok`,
                alt: `Kel. ${displayName(location.name)}`,
                riseOnHover: true,
            }).bindPopup(() => popupContent(location));
            marker.addTo(layer);
            markers.set(location.id, marker);
        });

        const groups = items.reduce((sum, location) => sum + location.total_kelompok, 0);
        el.summary.textContent = `${numberFormat.format(groups)} kelompok di ${numberFormat.format(items.filter((l) => l.total_kelompok > 0).length)} kelurahan`;
        renderList(items);

        if (fit && items.length > 0) {
            map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 15 });
        }
    };

    el.district.addEventListener('change', () => render({ fit: true }));
    el.showEmpty.addEventListener('change', () => render());
    el.search.addEventListener('input', () => render());

    const dataReady = fetch(config.endpoint, { headers: { Accept: 'application/json' } })
        .then((response) => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then((data) => {
            locations = data.filter((l) => Number.isFinite(l.latitude) && Number.isFinite(l.longitude));
            el.status.hidden = true;
            render({ fit: Boolean(el.district.value) });
        });

    dataReady.catch(() => {
        el.status.textContent = 'Data peta gagal dimuat. Muat ulang halaman untuk mencoba lagi.';
        el.status.classList.add('text-red-700');
    });

    // ---- Gunakan lokasi saya -------------------------------------------------
    const userIcon = L.divIcon({ html: '<span class="map-user-marker"></span>', className: '', iconSize: [20, 20], iconAnchor: [10, 10] });
    const locateErrors = {
        1: 'Izin lokasi ditolak. Izinkan akses lokasi untuk situs ini di pengaturan browser, atau cari kelurahan Anda lewat kolom di bawah.',
        2: 'Lokasi Anda tidak dapat ditentukan. Pastikan layanan lokasi (GPS) perangkat aktif, lalu coba lagi.',
        3: 'Waktu mencari lokasi habis. Coba lagi.',
    };
    let locating = false;

    const setLocateStatus = (message, isError = false) => {
        el.locateStatus.textContent = message;
        el.locateHint.hidden = message !== '';
        el.locateStatus.classList.toggle('text-red-700', isError);
        el.locateStatus.classList.toggle('text-slate-800', !isError);
    };

    const finishLocating = () => {
        locating = false;
        el.locate.removeAttribute('aria-busy');
        el.locateLabel.textContent = userPosition ? 'Perbarui lokasi saya' : 'Gunakan lokasi saya';
    };

    const showPosition = ({ latitude, longitude, accuracy }) => {
        userPosition = L.latLng(latitude, longitude);
        distances.clear();
        locations.forEach((l) => distances.set(l.id, userPosition.distanceTo([l.latitude, l.longitude])));
        const nearest = locations
            .filter((l) => l.total_kelompok > 0)
            .reduce((best, l) => (best === null || distances.get(l.id) < distances.get(best.id) ? l : best), null);
        nearestId = nearest?.id ?? null;

        // Kosongkan filter supaya kelurahan terdekat pasti tampil di daftar.
        el.district.value = '';
        el.search.value = '';
        render();

        userLayer.clearLayers();
        const insideCity = cityBounds.contains(userPosition);
        if (insideCity) {
            if (accuracy > 0 && accuracy <= 3000) {
                L.circle(userPosition, { radius: accuracy, color: '#2563eb', weight: 1, fillOpacity: 0.08, interactive: false }).addTo(userLayer);
            }
            L.marker(userPosition, { icon: userIcon, title: 'Lokasi Anda', alt: 'Lokasi Anda', keyboard: false, zIndexOffset: 1000 })
                .bindPopup('Lokasi Anda')
                .addTo(userLayer);
        }
        el.locateClear.hidden = false;

        const target = nearest ? markers.get(nearest.id) : null;
        if (!target) {
            setLocateStatus('Belum ada kelurahan dengan kelompok yang bisa ditampilkan.', true);
            return;
        }

        // Tanpa animasi supaya popup dibuka setelah peta selesai berpindah.
        const view = insideCity ? L.latLngBounds([userPosition, target.getLatLng()]) : L.latLngBounds([target.getLatLng()]);
        map.fitBounds(view, { paddingTopLeft: [40, 150], paddingBottomRight: [40, 40], maxZoom: 15, animate: false });
        target.openPopup();

        setLocateStatus(
            `Terdekat: Kel. ${displayName(nearest.name)}, Kec. ${displayName(nearest.district)} ` +
                `(${formatDistance(distances.get(nearest.id))}, ${numberFormat.format(nearest.total_kelompok)} kelompok). ` +
                (insideCity ? 'Daftar diurutkan dari yang terdekat.' : 'Lokasi Anda berada di luar Kota Bandung.'),
        );
    };

    const locate = () => {
        if (locating) return;
        if (!window.isSecureContext || !('geolocation' in navigator)) {
            setLocateStatus(
                window.isSecureContext ? 'Browser Anda tidak mendukung fitur lokasi.' : 'Fitur lokasi hanya bisa dipakai lewat alamat https://.',
                true,
            );
            return;
        }

        locating = true;
        el.locate.setAttribute('aria-busy', 'true');
        el.locateLabel.textContent = 'Mencari lokasi Anda…';
        setLocateStatus('');

        navigator.geolocation.getCurrentPosition(
            (position) =>
                dataReady
                    .then(() => showPosition(position.coords))
                    .catch(() => {})
                    .finally(finishLocating),
            (error) => {
                setLocateStatus(locateErrors[error.code] ?? locateErrors[2], true);
                finishLocating();
            },
            { enableHighAccuracy: false, timeout: 15000, maximumAge: 300000 },
        );
    };

    el.locate.addEventListener('click', locate);
    el.locateClear.addEventListener('click', () => {
        userPosition = null;
        nearestId = null;
        distances.clear();
        userLayer.clearLayers();
        map.closePopup();
        el.locateClear.hidden = true;
        setLocateStatus('');
        finishLocating();
        render();
        el.locate.focus();
    });

    // Dari tombol "Cari kelompok terdekat" (…/map?lokasi=saya): langsung cari.
    if (new URLSearchParams(window.location.search).get('lokasi') === 'saya') locate();

    // Peta perlu dihitung ulang ukurannya bila panel/layout berubah.
    new ResizeObserver(() => map.invalidateSize()).observe(el.map);
}
