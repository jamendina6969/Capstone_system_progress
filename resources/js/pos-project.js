// Hydrotech POS Project & Service Pricing Engine
export const PosProject = {
    calculateBreakdown(params) {
        const materials = params.materials || [];
        const outsource = parseFloat(params.outsource) || 0;
        const labor = parseFloat(params.labor) || 0;
        const installation = parseFloat(params.installation) || 0;
        const additional = parseFloat(params.additional) || 0;
        const freight = parseFloat(params.freight) || 0;
        const markupPercent = parseFloat(params.markupPercent) || 0;

        const materialsCost = materials.reduce((acc, m) => {
            const price = parseFloat(m.price) || 0;
            const qty = parseInt(m.qty) || 1;
            return acc + (price * qty);
        }, 0);

        const directCosts = materialsCost + outsource + labor + installation + additional + freight;
        const markupAmount = Math.round(directCosts * (markupPercent / 100) * 100) / 100;
        const totalProjectPrice = Math.round((directCosts + markupAmount) * 100) / 100;

        return {
            materialsCost,
            directCosts,
            markupAmount,
            totalProjectPrice
        };
    }
};

window.PosProject = PosProject;
export default PosProject;
