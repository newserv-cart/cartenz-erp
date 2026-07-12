window.ShipmentUtils = {
    toFloat(value) {
        return parseFloat(value || 0);
    },

    toInt(value) {
        return parseInt(value || 0, 10);
    },

    formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(parseFloat(value || 0));
    },

    debounce(callback, wait = 300) {
        let timer;

        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => callback.apply(this, args), wait);
        };
    }
};