# SmartGov Market - Database Schema Reference

Database Name: `smartgov_market`  
Character Set: `utf8mb4_unicode_ci`

---

## Normalized Tables Summary (23 Tables)

1. `roles`: Role definitions (`admin`, `officer`, `vendor`, `customer`).
2. `departments`: Government licensing & inspection departments (`DCI`, `DAFS`, `CPA`).
3. `users`: System users with hashed passwords, contact info, and role references.
4. `addresses`: Customer and vendor physical addresses with municipality & district.
5. `vendors`: Business profiles, sector, status (`Pending`, `Approved`, `Suspended`), GIS coordinates.
6. `vendor_applications`: Application lifecycle tracking (`VND-YYYY-XXXXXX`), officer remarks.
7. `vendor_documents`: Uploaded PAN/VAT certificates, ID cards, and verification status.
8. `licenses`: Digital business licenses (`LIC-YYYY-XXXXXX`), validity dates, and QR code URL.
9. `government_services`: Online government services directory, fee, and processing time.
10. `service_applications`: Customer service requests (`SRV-YYYY-XXXXXX`) and status.
11. `categories`: Product categories (`name`, `slug`, `image_path`).
12. `products`: Vendor product catalog (`name`, `price`, `stock_quantity`, `status`).
13. `product_images`: Additional product images.
14. `cart`: Active shopping carts.
15. `cart_items`: Itemized cart quantities and unit prices.
16. `orders`: Customer orders (`ORD-YYYY-XXXXXX`), shipping address, totals, and fulfillment status.
17. `order_items`: Order items mapping vendor, product, quantity, subtotal.
18. `payments`: Payment transactions (`TXN-XXXXXX`), payment method (COD / Gateway), status.
19. `complaints`: Public grievances (`CMP-YYYY-XXXXXX`), priority, department, GIS pins, resolution.
20. `complaint_attachments`: Uploaded evidence files.
21. `reviews`: 1 to 5 star ratings and reviews for delivered products.
22. `notifications`: Real-time user in-app notifications.
23. `audit_logs`: System security audit logs.
