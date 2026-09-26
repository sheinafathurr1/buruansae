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
    };
    const numberFormat = new Intl.NumberFormat('id-ID');
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

    const markerIcon = (location) => {
        const count = location.total_kelompok;
        const size = count > 0 ? Math.min(26 + count * 3, 48) : 14;
        const html = document.createElement('div');
        html.className = 'map-marker';
        html.style.width = html.style.height = `${size}px`;
        html.style.background = count > 0 ? '#1f8a4c' : '#94a3b8';
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

        [...items]
            .sort((a, b) => b.total_kelompok - a.total_kelompok || a.name.localeCompare(b.name))
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
                district.textContent = `Kec. ${displayName(location.district)}`;
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

    fetch(config.endpoint, { headers: { Accept: 'application/json' } })
        .then((response) => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then((data) => {
            locations = data.filter((l) => Number.isFinite(l.latitude) && Number.isFinite(l.longitude));
            el.status.hidden = true;
            render({ fit: Boolean(el.district.value) });
        })
        .catch(() => {
            el.status.textContent = 'Data peta gagal dimuat. Muat ulang halaman untuk mencoba lagi.';
            el.status.classList.add('text-red-700');
        });

    // Peta perlu dihitung ulang ukurannya bila panel/layout berubah.
    new ResizeObserver(() => map.invalidateSize()).observe(el.map);
}
