import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';



window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

const APP_TIMEZONE = 'Asia/Jakarta';
const APP_LOCALE = 'id-ID';

const formatLocalizedDate = (date, options = {}) => date.toLocaleDateString(APP_LOCALE, {
    timeZone: APP_TIMEZONE,
    ...options,
});

Alpine.data('villaForm', (config) => ({
    isResort: Boolean(config.isResort),
    showUpdateConfirmation: false,
    isEditMode: Boolean(config.isEditMode),
    name: config.name ?? '',
    slug: config.slug ?? '',
    location: config.location ?? '',
    status: config.status ?? 'draft',
    description: config.description ?? '',
    rules: config.rules ?? '',
    pros: config.pros ?? '',
    cons: config.cons ?? '',
    youtubeUrl: config.youtubeUrl ?? '',
    brandIds: (config.brandIds ?? []).map(Number),
    initialData: config.initialData ?? {},
    brandOptions: config.brandOptions ?? [],
    facilityOptions: config.facilityOptions ?? [],
    facilities: config.facilities?.length ? [...config.facilities] : [''],
    additionalFacilities: config.additionalFacilities?.length ? [...config.additionalFacilities] : [''],
    customFacility: '',
    dragIndex: null,

    addFacility(type) {
        if (type === 'primary') {
            this.facilities.push('');
            return;
        }

        this.additionalFacilities.push('');
    },

    removeFacility(type, index) {
        const list = type === 'primary' ? this.facilities : this.additionalFacilities;

        if (list.length === 1) {
            list[0] = '';
            return;
        }

        list.splice(index, 1);
    },

    hasFacility(name) {
        return this.facilities.includes(name);
    },

    toggleFacility(name) {
        if (this.hasFacility(name)) {
            this.facilities = this.facilities.filter((item) => item !== name);

            if (this.facilities.length === 0) {
                this.facilities = [''];
            }
            return;
        }

        if (this.facilities.length === 1 && this.facilities[0] === '') {
            this.facilities = [name];
            return;
        }

        this.facilities.push(name);
    },

    addCustomFacility() {
        const value = String(this.customFacility ?? '').trim();

        if (!value) {
            return;
        }

        if (this.facilities.length === 1 && this.facilities[0] === '') {
            this.facilities = [value];
        } else if (!this.facilities.includes(value)) {
            this.facilities.push(value);
        }

        this.customFacility = '';
    },

    dragStartList(type, index) {
        this.dragIndex = { type, index };
    },

    dropList(type, index) {
        if (!this.dragIndex || this.dragIndex.type !== type || this.dragIndex.index === index) {
            this.dragIndex = null;
            return;
        }

        const list = type === 'primary' ? [...this.facilities] : [...this.additionalFacilities];
        const [moved] = list.splice(this.dragIndex.index, 1);
        list.splice(index, 0, moved);

        if (type === 'primary') {
            this.facilities = list;
        } else {
            this.additionalFacilities = list;
        }

        this.dragIndex = null;
    },

    openUpdateConfirmation() {
        this.showUpdateConfirmation = true;
    },

    closeUpdateConfirmation() {
        this.showUpdateConfirmation = false;
    },

    submitConfirmedUpdate() {
        const form = this.$root.querySelector('form');

        if (!form) {
            return;
        }

        this.showUpdateConfirmation = false;
        form.requestSubmit();
    },

    cleanList(items) {
        return (items ?? []).filter((item) => String(item).trim() !== '');
    },

    formatBrands(ids) {
        const normalizedIds = (ids ?? []).map(Number);
        const selected = this.brandOptions
            .filter((option) => normalizedIds.includes(Number(option.id)))
            .map((option) => option.name);

        return selected.length ? selected.join(', ') : '-';
    },

    formatStatus(value) {
        return ({
            draft: 'Draft',
            active: 'Aktif',
            inactive: 'Nonaktif',
        })[value] ?? '-';
    },

    formatResort(value) {
        return value ? 'Resort' : 'Villa';
    },

    formatList(items) {
        const cleaned = this.cleanList(items);
        return cleaned.length ? cleaned.join(', ') : '-';
    },
}));

Alpine.data('bookingForm', (config) => ({
    villa: config.villa,
    units: config.units ?? [],
    addons: config.addons ?? [],
    vouchers: config.vouchers ?? [],
    selectedUnitId: String(config.initialUnitId ?? ''),
    selectedAddonIds: (config.initialSelectedAddonIds ?? []).map(String),
    addonQuantities: Object.fromEntries(
        Object.entries(config.initialAddonQuantities ?? {}).map(([key, value]) => [String(key), String(value)])
    ),
    checkIn: config.initialCheckIn ?? '',
    checkOut: config.initialCheckOut ?? '',
    manualDiscountAmount: String(config.initialManualDiscountAmount ?? 0),
    markupAmount: String(config.initialMarkupAmount ?? 0),
    dpAmount: String(config.initialDpAmount ?? 0),
    voucherId: String(config.initialVoucherId ?? ''),
    todayDate: config.todayDate ?? '',
    showAddons: Boolean(config.initialShowAddons ?? false),
    showPricingAdjustments: Boolean(config.initialShowPricingAdjustments ?? false),
    showConfirmationModal: false,

    init() {
        if (!this.villa.is_resort && !this.selectedUnitId && this.units.length > 0) {
            this.selectedUnitId = String(this.units[0].id);
        }

        this.addons.forEach((addon) => {
            const addonId = String(addon.id);

            if (!this.addonQuantities[addonId] || Number.parseInt(this.addonQuantities[addonId], 10) < 1) {
                this.addonQuantities[addonId] = '1';
            }
        });

        if (
            this.voucherId
            || this.sanitizeMoney(this.manualDiscountAmount) > 0
            || this.sanitizeMoney(this.markupAmount) > 0
        ) {
            this.showPricingAdjustments = true;
        }
    },

    sanitizeMoney(value) {
        return Number.parseInt(String(value ?? '').replace(/\D/g, ''), 10) || 0;
    },

    formatMoney(value) {
        return formatRupiah(value);
    },

    formatDisplayDate(dateString) {
        if (!dateString) {
            return '-';
        }

        return formatLocalizedDate(new Date(`${dateString}T00:00:00`), {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    },

    normalizePhone(event) {
        event.target.value = String(event.target.value ?? '').replace(/\D/g, '');
    },

    syncMoney(event, field) {
        this[field] = String(this.sanitizeMoney(event.target.value));
    },

    addonBaseQuantity(addonId) {
        return Math.max(1, Number.parseInt(this.addonQuantities[String(addonId)] ?? '1', 10) || 1);
    },

    ensureAddonQuantity(addonId, isSelected = true) {
        const normalizedId = String(addonId);

        if (isSelected || !this.addonQuantities[normalizedId] || Number.parseInt(this.addonQuantities[normalizedId], 10) < 1) {
            this.addonQuantities[normalizedId] = '1';
        }
    },

    addonChargeLabel(addon) {
        switch (addon.charge_basis) {
            case 'per_night':
                return 'per malam';
            case 'per_item_per_night':
                return `per ${addon.unit_label} per malam`;
            case 'per_person_per_night':
                return `per ${addon.unit_label} per malam`;
            case 'per_person_per_stay':
                return `per ${addon.unit_label} per stay`;
            case 'per_item':
                return `per ${addon.unit_label}`;
            default:
                return 'per stay';
        }
    },

    calculateAddonQuantity(addon, baseQuantity) {
        switch (addon.charge_basis) {
            case 'per_night':
                return this.nightsCount;
            case 'per_item_per_night':
            case 'per_person_per_night':
                return baseQuantity * this.nightsCount;
            default:
                return baseQuantity;
        }
    },

    buildAddonSummary(addon, baseQuantity, quantity) {
        switch (addon.charge_basis) {
            case 'per_night':
                return `${this.addonChargeLabel(addon)} - ${this.nightsCount} malam`;
            case 'per_item_per_night':
            case 'per_person_per_night':
                return `${baseQuantity} ${addon.unit_label} x ${this.nightsCount} malam = ${quantity} charge`;
            case 'per_person_per_stay':
                return `${baseQuantity} ${addon.unit_label} per stay`;
            case 'per_item':
                return `${baseQuantity} ${addon.unit_label}`;
            default:
                return `${baseQuantity} ${addon.unit_label} per stay`;
        }
    },

    get selectedUnit() {
        return this.units.find((unit) => String(unit.id) === String(this.selectedUnitId)) ?? null;
    },

    get nightsCount() {
        if (!this.checkIn || !this.checkOut) {
            return 0;
        }

        const start = new Date(`${this.checkIn}T00:00:00`);
        const end = new Date(`${this.checkOut}T00:00:00`);
        const diff = Math.round((end - start) / 86400000);

        return diff > 0 ? diff : 0;
    },

    get previewNights() {
        if (!this.selectedUnit || this.nightsCount === 0) {
            return [];
        }

        return Array.from({ length: this.nightsCount }, (_, index) => {
            const currentDate = new Date(`${this.checkIn}T00:00:00`);
            currentDate.setDate(currentDate.getDate() + index);

            const dateString = currentDate.toISOString().slice(0, 10);
            const seasonalPrice = (this.selectedUnit.seasonal_prices ?? []).find((price) => {
                return dateString >= price.start_date && dateString <= price.end_date;
            });

            let amount = this.selectedUnit.price_weekday;
            let note = 'Harga weekday';
            const dayOfWeek = currentDate.getDay();

            if (seasonalPrice) {
                amount = seasonalPrice.price;
                note = seasonalPrice.note || 'Harga high season';
            } else if (dayOfWeek === 5) {
                amount = this.selectedUnit.price_semi_weekend;
                note = 'Harga semi weekend';
            } else if (dayOfWeek === 6) {
                amount = this.selectedUnit.price_weekend;
                note = 'Harga weekend';
            }

            return {
                date: dateString,
                label: formatLocalizedDate(currentDate, {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }),
                amount,
                note,
            };
        });
    },

    get selectedAddons() {
        return this.addons.filter((addon) => this.selectedAddonIds.includes(String(addon.id)));
    },

    get previewAddons() {
        return this.selectedAddons.map((addon) => {
            const baseQuantity = this.addonBaseQuantity(addon.id);
            const quantity = this.calculateAddonQuantity(addon, baseQuantity);
            const total = addon.price * quantity;

            return {
                ...addon,
                baseQuantity,
                quantity,
                total,
                summary: this.buildAddonSummary(addon, baseQuantity, quantity),
            };
        });
    },

    get subtotalNight() {
        return this.previewNights.reduce((total, item) => total + item.amount, 0);
    },

    get subtotalAddon() {
        return this.previewAddons.reduce((total, item) => total + item.total, 0);
    },

    get markupValue() {
        return this.sanitizeMoney(this.markupAmount);
    },

    get manualDiscountValue() {
        return this.sanitizeMoney(this.manualDiscountAmount);
    },

    get dpValue() {
        return this.sanitizeMoney(this.dpAmount);
    },

    get subtotalBeforeDiscount() {
        return this.subtotalNight + this.subtotalAddon + this.markupValue;
    },

    get selectedVoucher() {
        return this.vouchers.find((voucher) => String(voucher.id) === String(this.voucherId)) ?? null;
    },

    get hasPricingAdjustments() {
        return Boolean(this.voucherId)
            || this.manualDiscountValue > 0
            || this.markupValue > 0;
    },

    get voucherDiscountValue() {
        const voucher = this.selectedVoucher;

        if (!voucher || this.subtotalBeforeDiscount < voucher.minimum_transaction) {
            return 0;
        }

        if (voucher.discount_type === 'percentage') {
            return Math.round(this.subtotalBeforeDiscount * (voucher.amount / 100));
        }

        return Math.min(this.subtotalBeforeDiscount, voucher.amount);
    },

    get grandTotal() {
        return Math.max(0, this.subtotalBeforeDiscount - this.voucherDiscountValue - this.manualDiscountValue);
    },

    get remainingBalance() {
        return Math.max(0, this.grandTotal - this.dpValue);
    },

    get finalPaymentDueDate() {
        if (!this.checkIn) {
            return '';
        }

        const checkInDate = new Date(`${this.checkIn}T00:00:00`);
        const threshold = new Date(checkInDate);
        threshold.setDate(threshold.getDate() - 7);
        const dpDate = this.todayDate ? new Date(`${this.todayDate}T00:00:00`) : new Date();
        dpDate.setHours(0, 0, 0, 0);

        if (dpDate <= threshold) {
            const dueDate = new Date(checkInDate);
            dueDate.setDate(dueDate.getDate() - 3);

            return this.formatDateValue(dueDate);
        }

        return this.checkIn;
    },

    get finalPaymentDueLabel() {
        if (!this.finalPaymentDueDate || !this.checkIn) {
            return 'Belum ditentukan';
        }

        if (this.finalPaymentDueDate === this.checkIn) {
            return `Pelunasan saat check-in (${this.formatDisplayDate(this.finalPaymentDueDate)})`;
        }

        return `Pelunasan maksimal ${this.formatDisplayDate(this.finalPaymentDueDate)} (H-3)`;
    },

    formatDateValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    },

    openConfirmationModal() {
        this.showConfirmationModal = true;
    },

    closeConfirmationModal() {
        this.showConfirmationModal = false;
    },

    submitConfirmedBooking() {
        const form = this.$root.querySelector('form');

        if (!form) {
            return;
        }

        this.showConfirmationModal = false;
        form.requestSubmit();
    },
}));

Alpine.data('calendarCard', (config) => ({
    currentMonth: config.initialMonth ?? new Date().toISOString().slice(0, 7),
    bookings: config.bookings ?? [],
    showBookingBaseUrl: config.showBookingBaseUrl ?? '',
    createBookingUrl: config.createBookingUrl ?? '',

    prevMonth() {
        this.currentMonth = this.shiftMonth(-1);
    },

    nextMonth() {
        this.currentMonth = this.shiftMonth(1);
    },

    shiftMonth(step) {
        const [year, month] = String(this.currentMonth).split('-').map(Number);
        const date = new Date(year, (month - 1) + step, 1);

        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    },

    get monthDate() {
        const [year, month] = String(this.currentMonth).split('-').map(Number);

        return new Date(year, month - 1, 1);
    },

    get monthLabel() {
        return formatLocalizedDate(this.monthDate, {
            month: 'long',
            year: 'numeric',
        }).toUpperCase();
    },

    get weeks() {
        const start = new Date(this.monthDate);
        start.setDate(1);
        start.setDate(start.getDate() - start.getDay());

        const end = new Date(this.monthDate.getFullYear(), this.monthDate.getMonth() + 1, 0);
        end.setDate(end.getDate() + (6 - end.getDay()));

        const weeks = [];
        const cursor = new Date(start);

        while (cursor <= end) {
            const week = [];

            for (let dayIndex = 0; dayIndex < 7; dayIndex += 1) {
                const dateString = this.formatDate(cursor);
                const booking = this.findBooking(dateString);

                week.push({
                    date: dateString,
                    day: cursor.getDate(),
                    is_current_month: cursor.getMonth() === this.monthDate.getMonth(),
                    is_today: dateString === this.todayDateString(),
                    booking,
                    is_check_in: booking ? booking.check_in === dateString : false,
                    is_check_out: booking ? this.subtractOneDay(booking.check_out) === dateString : false,
                });

                cursor.setDate(cursor.getDate() + 1);
            }

            weeks.push(week);
        }

        return weeks;
    },

    get occupancyDays() {
        return this.weeks.flat().filter((day) => day.is_current_month && day.booking).length;
    },

    findBooking(dateString) {
        return this.bookings.find((booking) => dateString >= booking.check_in && dateString < booking.check_out) ?? null;
    },

    subtractOneDay(dateString) {
        const date = new Date(`${dateString}T00:00:00`);
        date.setDate(date.getDate() - 1);

        return this.formatDate(date);
    },

    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    },

    todayDateString() {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: APP_TIMEZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).formatToParts(new Date());

        const getPart = (type) => parts.find((part) => part.type === type)?.value ?? '';

        return `${getPart('year')}-${getPart('month')}-${getPart('day')}`;
    },

    dayClasses(day) {
        if (!day.is_current_month) {
            return 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500';
        }

        if (day.booking) {
            return 'bg-error-500 text-white';
        }

        return 'bg-success-50 text-gray-700 dark:bg-success-500/10 dark:text-gray-200';
    },

    dayTitle(day) {
        if (!day.booking) {
            return 'Tersedia';
        }

        return `${day.booking.guest_name} | ${day.booking.booking_code}`;
    },

    bookingUrl(day) {
        if (!day.booking || !this.showBookingBaseUrl) {
            return '#';
        }

        return `${this.showBookingBaseUrl}/${day.booking.id}`;
    },

    createUrl(day) {
        if (!this.createBookingUrl || day.booking) {
            return '#';
        }

        const checkOut = new Date(`${day.date}T00:00:00`);
        checkOut.setDate(checkOut.getDate() + 1);

        const url = new URL(this.createBookingUrl, window.location.origin);
        url.searchParams.set('check_in', day.date);
        url.searchParams.set('check_out', this.formatDate(checkOut));

        return url.toString();
    },

    openBooking(day) {
        if (day.booking) {
            window.location.href = this.bookingUrl(day);
            return;
        }

        if (this.createBookingUrl) {
            window.location.href = this.createUrl(day);
        }
    },
}));

const formatRupiah = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

const sanitizeRupiah = (value) => {
    const digits = String(value ?? '').replace(/\D/g, '');

    if (digits === '') {
        return '0';
    }

    return String(Number.parseInt(digits, 10) || 0);
};

const initializeMoneyInput = (input) => {
    if (input.dataset.moneyInitialized === 'true') {
        return;
    }

    const originalName = input.getAttribute('name');

    if (!originalName) {
        return;
    }

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = originalName;
    hiddenInput.value = sanitizeRupiah(input.value || input.dataset.defaultValue || '0');

    const wrapper = document.createElement('div');
    wrapper.className = 'money-input-wrap';

    const prefix = document.createElement('span');
    prefix.className = 'money-input-prefix';
    prefix.textContent = 'Rp';

    input.parentNode?.insertBefore(wrapper, input);
    wrapper.appendChild(prefix);
    wrapper.appendChild(input);

    input.dataset.moneyInitialized = 'true';
    input.dataset.moneyName = originalName;
    input.removeAttribute('name');
    input.type = 'text';
    input.inputMode = 'numeric';
    input.autocomplete = 'off';
    input.spellcheck = false;
    input.classList.add('money-input-field');
    input.value = formatRupiah(hiddenInput.value);

    wrapper.insertAdjacentElement('afterend', hiddenInput);

    input.addEventListener('focus', () => {
        if (hiddenInput.value === '0') {
            input.select();
        }
    });

    input.addEventListener('input', () => {
        const sanitizedValue = sanitizeRupiah(input.value);
        hiddenInput.value = sanitizedValue;
        input.value = formatRupiah(sanitizedValue);
    });

    input.form?.addEventListener('submit', () => {
        hiddenInput.value = sanitizeRupiah(input.value);
    });
};

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-money]').forEach(initializeMoneyInput);

    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
