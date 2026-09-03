# SmartGov Market - API & Backend Handlers Reference

---

## 1. Cart API (`api/cart.php`)

- **`POST action=add`**: Adds product to cart. Parameters: `product_id`, `quantity`. Validates stock and active status.
- **`POST action=update`**: Updates quantity of cart item. Parameters: `cart_item_id`, `quantity`.
- **`GET action=remove`**: Removes item from cart. Parameters: `id`.

---

## 2. Order Placement API (`api/place_order.php`)

- **`POST`**: Executes atomic order placement transaction.
  - **Inputs:** `shipping_address`, `municipality`, `district`, `payment_method`
  - **Process:** Validates stock, calculates backend total, creates order (`ORD-YYYY-XXXXXX`), inserts order items, decrements stock, records payment, clears cart, dispatches notifications, logs audit.

---

## 3. License Verification Endpoint (`verify.php`)

- **`GET license_no=LIC-YYYY-XXXXXX`**: Public verification endpoint for QR codes. Validates issue date, expiry date, status (`VALID`, `EXPIRED`, `SUSPENDED`).
