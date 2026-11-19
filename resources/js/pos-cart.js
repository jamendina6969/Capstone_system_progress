// Hydrotech POS Cart Calculation & State Engine
export const PosCart = {
    VAT_RATE: 0.12,

    formatCurrency(amount) {
        const num = parseFloat(amount) || 0;
        return '₱' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    calculateItemDiscount(item) {
        const baseSubtotal = (parseFloat(item.selling_price) || 0) * (parseInt(item.quantity) || 1);
        const type = item.item_discount_type || 'none';
        const val = parseFloat(item.item_discount_value) || 0;
        const scope = item.item_discount_scope || 'whole';
        const qty = parseInt(item.quantity) || 1;

        if (type === 'percentage' && val > 0) {
            return Math.round(baseSubtotal * (val / 100) * 100) / 100;
        }

        if (type === 'amount' && val > 0) {
            if (scope === 'per_item') {
                return Math.min(baseSubtotal, Math.round(val * qty * 100) / 100);
            }
            return Math.min(baseSubtotal, val);
        }

        return 0;
    },

    calculateCartDiscount(subtotal, discountConfig) {
        if (!discountConfig || discountConfig.type === 'none') return 0;

        const type = discountConfig.type;
        const mode = discountConfig.mode || 'percentage';
        const val = parseFloat(discountConfig.value) || 0;

        if (['senior', 'pwd'].includes(type)) {
            return Math.round(subtotal * 0.20 * 100) / 100;
        }

        if (type === 'percentage') {
            return Math.round(subtotal * (val / 100) * 100) / 100;
        }

        if (type === 'amount') {
            return Math.min(subtotal, val);
        }

        if (['employee', 'voucher'].includes(type)) {
            if (mode === 'percentage') {
                return Math.round(subtotal * (val / 100) * 100) / 100;
            }
            return Math.min(subtotal, val);
        }

        return 0;
    },

    calculateTotals(cartItems, discountConfig) {
        let rawSubtotal = 0;
        let totalItemDiscounts = 0;

        const processedItems = Object.entries(cartItems || {}).map(([key, item]) => {
            const qty = parseInt(item.quantity) || 1;
            const price = parseFloat(item.selling_price) || 0;
            const baseTotal = qty * price;
            const itemDiscount = this.calculateItemDiscount(item);
            const lineSubtotal = Math.max(0, baseTotal - itemDiscount);

            rawSubtotal += baseTotal;
            totalItemDiscounts += itemDiscount;

            return {
                ...item,
                key,
                baseTotal,
                itemDiscount,
                lineSubtotal
            };
        });

        const subtotal = Math.max(0, rawSubtotal - totalItemDiscounts);
        const cartDiscountAmount = this.calculateCartDiscount(subtotal, discountConfig);
        const netTotal = Math.max(0, subtotal - cartDiscountAmount);
        const vatAmount = Math.round(netTotal * this.VAT_RATE * 100) / 100;
        const grandTotal = Math.round((netTotal + vatAmount) * 100) / 100;

        return {
            items: processedItems,
            rawSubtotal,
            totalItemDiscounts,
            subtotal,
            cartDiscountAmount,
            netTotal,
            vatAmount,
            grandTotal,
            totalQuantity: processedItems.reduce((acc, it) => acc + (parseInt(it.quantity) || 1), 0)
        };
    }
};

window.PosCart = PosCart;
export default PosCart;
