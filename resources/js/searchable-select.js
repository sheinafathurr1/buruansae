import TomSelect from 'tom-select';

/**
 * Ubah <select data-searchable> menjadi pilihan dengan kolom pencarian.
 * Teks tambahan untuk pencarian (mis. kelurahan) bisa diberikan lewat
 * <option data-data='{"search": "..."}'>.
 */
export function initSearchableSelects(root = document) {
    root.querySelectorAll('select[data-searchable]').forEach((select) => {
        if (select.tomselect) return;

        new TomSelect(select, {
            allowEmptyOption: true,
            maxOptions: null,
            create: false,
            dataAttr: 'data', // kunci dataset untuk atribut data-data
            searchField: ['text', 'search'],
            render: {
                no_results: () => '<div class="no-results">Tidak ditemukan</div>',
            },
        });
    });
}
