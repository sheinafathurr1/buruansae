import { initSearchableSelects } from './searchable-select';

const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 3 });
const pad = (n) => String(n).padStart(2, '0');
const toIsoDate = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

/** Form data tanam: info kelompok & perkiraan tanggal panen otomatis. */
function productionForm(state, groups, growingDays) {
    return {
        ...state,
        groups,
        growingDaysMap: growingDays,
        autoDate: !state.estimatedDate,

        get group() {
            return this.groups[this.groupId] ?? null;
        },
        get growingDays() {
            return this.growingDaysMap[this.commodityId] ?? null;
        },
        get computedDate() {
            if (!this.startDate || !this.growingDays) return '';
            const date = new Date(`${this.startDate}T00:00:00`);
            date.setDate(date.getDate() + Number(this.growingDays));
            return toIsoDate(date);
        },
        get computedLabel() {
            return this.computedDate
                ? new Date(`${this.computedDate}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                : '';
        },
        init() {
            // Tanggal yang sudah tersimpan & sama dengan hitungan otomatis tetap ikut berubah.
            if (this.estimatedDate && this.estimatedDate === this.computedDate) this.autoDate = true;
            this.$watch('computedDate', (value) => {
                if (this.autoDate && value) this.estimatedDate = value;
            });
            if (this.autoDate && this.computedDate) this.estimatedDate = this.computedDate;
        },
    };
}

/** Form panen: total panen = jumlah seluruh penyaluran. */
function harvestForm(quantities) {
    return {
        quantities,
        get total() {
            return Object.values(this.quantities).reduce((sum, value) => sum + (parseFloat(value) || 0), 0);
        },
        get totalLabel() {
            return numberFormat.format(this.total);
        },
    };
}

/** Kata sandi sementara acak (tanpa huruf/angka yang mirip: l, 1, O, 0). */
function passwordGenerator() {
    const letters = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    const digits = '23456789';
    const pick = (chars, count) => Array.from(crypto.getRandomValues(new Uint32Array(count)), (n) => chars[n % chars.length]).join('');

    return {
        generate() {
            // 7 huruf + 3 angka: selalu lolos aturan "huruf dan angka".
            this.$refs.input.value = pick(letters, 7) + pick(digits, 3);
            this.$refs.input.focus();
            this.$refs.input.select();
        },
    };
}

const normalize = (value) => value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

/** Pilih kelompok binaan staf: cari, tampilkan yang dipilih, pilih semua per kecamatan. */
function groupPicker() {
    return {
        query: '',
        onlySelected: false,
        count: 0,
        noMatches: false,

        init() {
            this.recount();
            this.$watch('query', () => this.filter());
            this.$watch('onlySelected', () => this.filter());
        },
        filter() {
            const query = normalize(this.query.trim());
            const filtering = query !== '' || this.onlySelected;
            let total = 0;

            this.$root.querySelectorAll('[data-district]').forEach((district) => {
                let visible = 0;
                district.querySelectorAll('[data-group]').forEach((row) => {
                    const show = (!query || row.dataset.search.includes(query)) && (!this.onlySelected || row.querySelector('input').checked);
                    row.hidden = !show;
                    if (show) visible += 1;
                });
                district.hidden = visible === 0;
                if (filtering && visible > 0) district.open = true;
                total += visible;
            });
            this.noMatches = total === 0;
        },
        toggleDistrict(button, checked) {
            button.closest('[data-district]').querySelectorAll('[data-group]:not([hidden]) input').forEach((input) => {
                input.checked = checked;
            });
            this.recount();
        },
        recount() {
            this.count = this.$root.querySelectorAll('[data-group] input:checked').length;
            this.$root.querySelectorAll('[data-district]').forEach((district) => {
                district.querySelector('[data-district-count]').textContent = district.querySelectorAll('[data-group] input:checked').length;
            });
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('productionForm', productionForm);
    window.Alpine.data('harvestForm', harvestForm);
    window.Alpine.data('passwordGenerator', passwordGenerator);
    window.Alpine.data('groupPicker', groupPicker);
});

initSearchableSelects();
