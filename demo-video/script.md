# Predictive Inventory System for Steven Hydrotech Exponent Water Treatment and Supply Services Using the Economic Order Quantity (EOQ)

> One "##" block per scene. The id after "##" must match a function in scenes.js.
> Write narration the way you would say it. [#name] marks a moment you can
> animate on, [pause 0.5] adds silence, {API|A P I} shows "API" but says "A P I".
> Replace each line below with your own narration, and rename the scenes as you like.

## intro {chapter="The hook"}
Monday morning. [pause 0.4] A customer walks in asking for a water filter. [#shelf] You walk over to the shelf. [pause 0.5] [#zero] Empty. [pause 0.4] [#lost] That is a lost sale, an unhappy customer, and a very long Monday. [#but] But what if your system had warned you three weeks ago? [#title] This is the Predictive Inventory System for Steven Hydrotech Exponent Water Treatment and Supply Services, and in the next few minutes, I will show you how data and mathematics eliminate stockouts for good.

## problem
Here is the challenge every water treatment business faces. Inventory is a delicate balancing act. [#left] Order too little, and you run out of critical filters and fittings. [#right] Order too much, and your operating cash gets trapped in bulky water gallons and storage tanks. [#balance] Somewhere in the middle is the optimal order point. [pause 0.4] One exact number, for every single product in your warehouse. [#hold] We will calculate that exact number before this demonstration ends.

## tour {chapter="Enterprise architecture"}
Let's step into the system. [#dark] Built on an enterprise dark foundation, this collapsible sidebar is your central command center. [#nav] At the top, your Dashboard, Point of Sale, and Transaction History. [#middle] In the middle, Catalog Management, Inventory Control, and Forecasting and {EOQ|E O Q}. [#bottom] And below, Suppliers and Purchase Orders, Audit Logs, and System Backups. [#theme] Prefer light mode during daytime? One click on the theme toggle instantly shifts the entire workspace into crisp high-contrast light mode, [#darkback] or back to dark mode for comfortable evening audits.

## login {chapter="Four user roles"}
Access control begins at the front door. [#login] The login portal features Steven Hydrotech's brand identity and four guiding principles. [#type] Enter your username and password, [#click] and the system authenticates your specific role. [#roles] There are four distinct operational roles. [#admin] Admins oversee the entire system, manage user accounts, trigger database backups, and inspect audit logs. [#manager] Managers control product catalogs, calibrate {EOQ|E O Q} parameters, approve restocks, and manage purchase orders. [#staff] Staff operate the counter point of sale and request retail restocks. [#wh] And warehouse staff manage inventory transfers and submit purchase requests. [#enter] Each user sees only what their job requires. Let's enter as the administrator.

## dashboard {chapter="Executive dashboard"}
Welcome to the dashboard. [#cards] Five summary cards deliver immediate business pulse: today's sales, overall sales, completed orders today, active catalog products, and critical items requiring restock. [#chart] Below them, the sales predictions chart plots actual historical sales against our simple moving average forecast, with an uncertainty band showing best and worst-case demand. [#alerts] And this live inventory alerts table highlights combined store and warehouse stock levels right beside their reorder points.

## velocity
Right above the alerts table is the fast and slow moving products analyzer. [#fast] Fast-moving items are sorted by sales velocity, warning managers to reorder quickly to prevent stockouts. [#slow] Slow-moving items are ranked by remaining days of supply, signaling management to pause reorders and avoid excess carrying costs. [#filter] You can filter by category or date range with a single click.

## pos {chapter="Point of sale"}
Now, let's look at the counter point of sale. [#badge] A pulsing counter indicator confirms you are live. [#dual] Notice this dual-mode toggle. For daily walk-in customers, staff stay in retail sale mode. [#project] But for contractors installing commercial filtration plants, switching to project and service mode unlocks custom installation bundles with itemized materials. [#search] Filter by category or search by {SKU|S K U}, [#click1] select a water filter, [#cart] and it enters the active cart with live tax, discount, and total computations.

## parked
Busy counter? [#park] If a customer steps away to browse fittings, staff can park the order with one click, [#clear] clear the counter to serve the next customer in line, [#restore] and restore the parked cart the moment they return. [#disc] Staff can also apply percentage or fixed peso discounts to individual items or the entire order.

## checkout
Let's complete the sale. [#summary] The order summary confirms every line item and the grand total. [#tender] Enter the cash tendered, [#change] and the system automatically calculates the customer's change. [#confirm] Confirm payment, [#receipt] and an official receipt is formatted, ready to print. [#magic] But here is the critical backend process. [#stock] Store stock is decremented immediately, [#demand] and a verified demand record is logged. That demand entry feeds the predictive forecast.

## history
All finalized sales join the transaction history ledger. [#table] Each record preserves the transaction I D, timestamp, cashier name, payment breakdown, and total. [#receipt] Managers can reopen and reprint any historical receipt at any time, ensuring total transparency.

## catalog {chapter="Catalog & stock management"}
Product management houses the master catalog. [#table] Each product displays its category, purchase cost, selling price, store stock, warehouse stock, and a dynamic status badge: green for healthy stock, amber for low stock, and red for stockouts. [#reorder] You define the specific reorder point that triggers low stock warnings. [#csv] Have existing inventory in spreadsheets? The {CSV|C S V} importer uploads entire catalogs in seconds, automatically categorizing items and skipping malformed rows.

## inventory {chapter="Warehouse & stock control"}
Inventory management segregates retail counter stock from back warehouse storage. [#table] You see exactly where every unit sits. [#transfer] Need more sediment cartridges at the counter? The stock transfer modal moves inventory between the warehouse and store with full audit remarks. [#restock] Or, submit a direct restock request.

## approvals
When a staff member submits a restock request, it does not alter stock immediately. [#queue] It routes here, to the pending approvals queue. [#review] The manager inspects the product, requested quantity, requester identity, timestamp, and reason. [#approve] Click approve, and the quantities update across the system. [#reject] If rejected, the manager must document a reason. Nothing bypasses authorization.

## batch
Receiving a major supplier shipment? [#mode] Batch restock mode allows warehouse personnel to enter new quantities for dozens of products simultaneously on a single screen. [#logs] Every movement is committed to the stock logs, preserving who requested it, who approved it, previous stock, quantity added, and final balances.

## requests {chapter="Procurement & suppliers"}
When stock runs low, the procurement cycle begins with purchase requests. [#pr] Staff and warehouse personnel submit material requests for needed filters and fittings. [#badge] Real-time notification badges immediately alert managers in the sidebar and topbar. [#convert] The manager reviews the request and converts it directly into an official purchase order.

## orders
Purchase orders eliminate procurement errors. [#suggestions] The low-stock suggestions engine automatically recommends orders for items below their reorder point, preset to their calculated economic order quantity. [#po] Managers generate the purchase order, [#pdf] export an official {PDF|P D F} for the supplier, [#receive] and use the receiving form to verify incoming deliveries directly into warehouse stock.

## suppliers
Managing vendor relationships is effortless. [#dir] The suppliers directory tracks authorized distributors, contact details, and performance ratings. [#ledger] The supplier ledger maintains a complete record of invoices, payments, and outstanding balances. [#discrepancy] And discrepancy reports log any damaged or missing items, automatically feeding supplier return workflows.

## forecast {chapter="Mathematical forecasting"}
Now, the engine behind the prediction. [#math] How does the system turn historical demand records into future forecasts? [#sma] With a Simple Moving Average. [#window] We select an observation window of recent days, say thirty days. [#sum] We sum all units sold across those thirty days: three hundred sixty units. [#div] Divide by thirty. [#daily] That yields an average daily demand of twelve units per day. [#annual] Multiply by three hundred sixty-five days, and our projected annual demand is four thousand three hundred eighty units.

## settings
Different products move at different speeds. [#global] In forecast settings, administrators define a global default window. [#overrides] Then, add category or product overrides. A fast-moving filter gets a fourteen-day window to react swiftly to demand spikes. A seasonal water tank gets a ninety-day window to smooth out fluctuations. The most specific rule always wins.

## eoq {chapter="Economic order quantity"}
With annual demand established, how many units should Steven Hydrotech order at once? [#curve] Placing frequent small orders increases annual ordering costs. But placing massive bulk orders increases warehouse holding costs. [#optimum] At the lowest point of the total cost curve sits the Economic Order Quantity. [#formula] The formula takes the square root of two times annual demand times ordering cost, divided by holding cost. [#calc] With four thousand three hundred eighty units of annual demand, one hundred fifty pesos ordering cost, and twenty-four pesos annual holding cost, the optimal order quantity is exactly two hundred thirty-four units.

## calibration
Costs fluctuate over time. [#settings] Global {EOQ|E O Q} settings configure flat ordering costs or percentage fallbacks, alongside holding costs per unit or cubic meter. [#table] In the {EOQ|E O Q} calibration workbench, managers can filter by timeframe—from annual down to daily demand—select multiple products, enter updated cost parameters, and apply batch recalculations with live target {EOQ|E O Q} results.

## admin {chapter="Administration & governance"}
Enterprise governance ensures total reliability. [#users] User management allows administrators to create accounts, assign roles, and revoke access. [#audit] The system audit log records every login, price update, and stock transfer with I P addresses and timestamps. [#backup] And system backups allow administrators to generate, download, or restore complete database snapshots with one click.

## payoff {chapter="The closed loop"}
Every piece of Steven Hydrotech's system forms an interconnected loop. [#pos] A sale generates demand. [#forecast] Demand updates the forecast. [#eoq] The forecast calculates the {EOQ|E O Q}. [#po] Low stock generates a purchase order. [#receive] And manager-approved receiving restocks the shelf. [#monday] Remember Monday morning? [#shelves] This time, the shelves are fully stocked, cash flow is protected, and the system saw it coming.

## outro
This is the Predictive Inventory System for Steven Hydrotech Exponent Water Treatment and Supply Services. Accurate store and warehouse stock. Data-backed order quantities. Complete role governance. And zero empty shelves. Thank you for watching.
