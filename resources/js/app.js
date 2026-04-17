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
    },

    sanitizeMoney(value) {
        return Number.parseInt(String(value ?? '').replace(/\D/g, ''), 10) || 0;
    },

    formatMoney(value) {
        return formatRupiah(value);
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
                label: currentDate.toLocaleDateString('id-ID', {
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
}));

Alpine.data('calendarCard', (config) => ({
    currentMonth: config.initialMonth ?? new Date().toISOString().slice(0, 7),
    bookings: config.bookings ?? [],
    showBookingBaseUrl: config.showBookingBaseUrl ?? '',

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
        return this.monthDate.toLocaleDateString('id-ID', {
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
                    is_today: dateString === this.formatDate(new Date()),
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

    dayBadge(day) {
        if (day.is_check_in) {
            return 'IN';
        }

        if (day.is_check_out) {
            return 'OUT';
        }

        return 'BOOK';
    },

    bookingUrl(day) {
        if (!day.booking || !this.showBookingBaseUrl) {
            return '#';
        }

        return `${this.showBookingBaseUrl}/${day.booking.id}`;
    },

    openBooking(day) {
        if (!day.booking) {
            return;
        }

        window.location.href = this.bookingUrl(day);
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

    input.dataset.moneyInitialized = 'true';
    input.dataset.moneyName = originalName;
    input.removeAttribute('name');
    input.type = 'text';
    input.inputMode = 'numeric';
    input.autocomplete = 'off';
    input.spellcheck = false;
    input.value = formatRupiah(hiddenInput.value);

    input.insertAdjacentElement('afterend', hiddenInput);

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
