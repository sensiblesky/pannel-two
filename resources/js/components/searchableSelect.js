export default (config = {}) => ({
    searchUrl: config.searchUrl || '',
    name: config.name || '',
    formId: config.formId || null,
    placeholder: config.placeholder || 'Search...',
    oldValue: config.oldValue || '',

    query: '',
    results: [],
    selectedId: '',
    selectedText: '',
    open: false,
    loading: false,
    highlighted: -1,

    // Quick create customer modal
    showCreateCustomer: false,
    savingCustomer: false,
    createError: '',
    newCustomer: {
        name: '', email: '', phone: '', password: '',
        role: 'customer', status: '1', branch_id: '',
        company: '', country: '', city: '', customer_notes: '',
    },

    init() {
        if (this.oldValue) {
            this.selectedId = this.oldValue;
        }
    },

    async search() {
        if (this.query.length < 1) {
            this.results = [];
            return;
        }
        this.loading = true;
        this.open = true;
        this.highlighted = -1;
        try {
            const url = `${this.searchUrl}?q=${encodeURIComponent(this.query)}`;
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            this.results = await res.json();
        } catch (e) {
            this.results = [];
        } finally {
            this.loading = false;
        }
    },

    select(item) {
        this.selectedId = item.id;
        this.selectedText = item.text;
        this.query = '';
        this.open = false;
        this.results = [];
    },

    clear() {
        this.selectedId = '';
        this.selectedText = '';
        this.query = '';
        this.results = [];
    },

    highlightNext() {
        if (this.highlighted < this.results.length - 1) {
            this.highlighted++;
        }
    },

    highlightPrev() {
        if (this.highlighted > 0) {
            this.highlighted--;
        }
    },

    selectHighlighted() {
        if (this.highlighted >= 0 && this.results[this.highlighted]) {
            this.select(this.results[this.highlighted]);
        }
    },

    async createCustomer(url) {
        this.createError = '';
        if (!this.newCustomer.name.trim()) {
            this.createError = 'Full name is required.';
            return;
        }
        if (this.newCustomer.password && this.newCustomer.password.length < 7) {
            this.createError = 'Password must be at least 7 characters.';
            return;
        }
        this.savingCustomer = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const endpoint = url || '/tickets/quick-customer';
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.newCustomer),
            });
            if (!res.ok) {
                const err = await res.json();
                this.createError = err.message || (err.errors ? Object.values(err.errors).flat().join(' ') : 'Failed to create customer.');
                return;
            }
            const customer = await res.json();
            this.select(customer);
            this.showCreateCustomer = false;
            this.newCustomer = {
                name: '', email: '', phone: '', password: '',
                role: 'customer', status: '1', branch_id: '',
                company: '', country: '', city: '', customer_notes: '',
            };
        } catch (e) {
            this.createError = 'An error occurred. Please try again.';
        } finally {
            this.savingCustomer = false;
        }
    },
});
