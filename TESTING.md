# SmartGov Market - End-to-End Testing Procedure

This document outlines the step-by-step test verification procedure corresponding to **Section 57: End-to-End Acceptance Test**.

---

## E2E Scenario Verification Steps

### 1. Vendor Registration & Application
- Go to `vendor/register.php` -> Register new vendor (e.g. "Kathmandu Crafts")
- Set GIS map pin location & upload PDF tax certificate -> Submit Application (`VND-2026-XXXXXX`).

### 2. Government Officer Review & Approval
- Login as Officer (`officer@smartgov.gov.np` / `password123`) -> Open Application
- Inspect document -> Click **Approve & Issue License** -> Digital License generated (`LIC-2026-XXXXXX`).

### 3. Vendor Product Listing
- Login as newly approved vendor -> View active Digital License
- Add new product (e.g. "Yak Wool Blanket", Price: 1500.00, Stock: 20) -> Published to marketplace.

### 4. Customer Purchase & Checkout
- Register customer or login (`customer@gmail.com` / `password123`) -> Search product
- Add to Cart -> Checkout -> Select Cash on Delivery -> Place Order (`ORD-2026-XXXXXX`).

### 5. Vendor Order Fulfillment
- Login as Vendor -> Receive Order notification -> Update status to `Delivered`.

### 6. Customer Review & Complaint
- Customer tracks order -> Receive `Delivered` status -> Submit 5-star product review.
- Submit complaint -> Officer inspects complaint on GIS map and records resolution findings.

### 7. Public License Verification
- Open `verify.php?license_no=LIC-2026-000101` -> Confirm `VALID` official government status.
