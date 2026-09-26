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

document.addEventListener('alpine:init', () => {
    window.Alpine.data('productionForm', productionForm);
    window.Alpine.data('harvestForm', harvestForm);
});

initSearchableSelects();
