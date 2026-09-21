# -*- coding: utf-8 -*-
"""
SmartGov Market - Academic Report Content Data Module
Contains all chapter contents, tables, diagrams, and appendices.
Based exclusively on the real SmartGov Market implementation.
"""

METADATA = {
    "title": "SMARTGOV MARKET",
    "subtitle": "An Integrated E-Governance and E-Commerce Platform",
    "doc_type": "Project Documentation / Software Project Report",
    "academic_year": "[LEAVE PLACEHOLDER - e.g., 2025/2026]",
    "student_name": "[LEAVE PLACEHOLDER - Student Name]",
    "roll_no": "[LEAVE PLACEHOLDER - Roll / Registration No.]",
    "course": "[LEAVE PLACEHOLDER - B.Sc. CSIT / B.E. Computer / BCA]",
    "department": "[LEAVE PLACEHOLDER - Department of Computer Science & Information Technology]",
    "institution": "[LEAVE PLACEHOLDER - University / College Name]",
    "supervisor": "[LEAVE PLACEHOLDER - Project Supervisor / Lecturer]",
    "submission_date": "[LEAVE PLACEHOLDER - Month, Year]"
}

PRELIMINARY = {
    "certificate": """This is to certify that the project entitled "SMARTGOV MARKET: An Integrated E-Governance and E-Commerce Platform", submitted by [LEAVE PLACEHOLDER - Student Name] (Roll/Reg No: [LEAVE PLACEHOLDER - Roll / Registration No.]) in partial fulfillment of the requirements for the degree of [LEAVE PLACEHOLDER - Course/Subject] to the [LEAVE PLACEHOLDER - Department], [LEAVE PLACEHOLDER - College/University], is a bonafide record of independent software development work carried out under my supervision and guidance.

The results embodied in this report have been verified against the actual system implementation and have not been submitted to any other university or institute for the award of any degree or diploma.


___________________________                          ___________________________
[LEAVE PLACEHOLDER - Supervisor]                     [LEAVE PLACEHOLDER - Head of Dept]
Project Supervisor                                   Head of Department
Department of Computer Science                       Department of Computer Science
Date: ____________________                           Date: ____________________""",

    "declaration": """I hereby declare that this project report entitled "SMARTGOV MARKET: An Integrated E-Governance and E-Commerce Platform" is an authentic record of my own work conducted under the supervision of [LEAVE PLACEHOLDER - Supervisor], [LEAVE PLACEHOLDER - College/University].

I confirm that:
1. The software codebase, architectural designs, database schemas, and documentation presented herein reflect the actual working system implemented for this project.
2. The work has not been previously submitted in part or full for the award of any other academic degree, diploma, or certificate.
3. Proper acknowledgements, citations, and references have been provided for all external libraries, packages, and frameworks utilized during development.


___________________________
[LEAVE PLACEHOLDER - Student Name]
Roll / Reg. No.: [LEAVE PLACEHOLDER]
Department: [LEAVE PLACEHOLDER]
Date: ____________________""",

    "acknowledgement": """I would like to express my deepest gratitude and sincere appreciation to my project supervisor, [LEAVE PLACEHOLDER - Supervisor], whose invaluable guidance, constructive criticism, and continuous encouragement made the successful design and implementation of the SmartGov Market platform possible.

I am profoundly indebted to the faculty members and staff of the [LEAVE PLACEHOLDER - Department], [LEAVE PLACEHOLDER - College/University] for providing the academic foundation, computing laboratories, and technical resources necessary for conducting this project.

Special thanks are extended to the open-source software communities responsible for PHP, MariaDB/MySQL, Bootstrap 5, Leaflet.js, OpenStreetMap, and Chart.js, without whose robust software foundations this integrated e-governance and e-commerce platform could not have been engineered.

Finally, I express my heartfelt gratitude to my family and fellow peers for their unwavering moral support, patience, and encouragement throughout the entire duration of this project.

[LEAVE PLACEHOLDER - Student Name]""",

    "abstract": """The rapid proliferation of digital technologies has transformed governance and retail individually; however, a deep divide persists between citizen-centric municipal administration and local economic commerce. Traditional local businesses face cumbersome, paper-heavy verification workflows to obtain operating licenses, while citizens must navigate disjointed portals for municipal grievances, licensing status, and verified local shopping. 

To bridge this operational gap, this report presents the design, architecture, and implementation of "SmartGov Market" (locally branded as HATIYA - Digital Governance & Marketplace Platform), an integrated web platform that unifies municipal e-governance with a verified local e-commerce marketplace into a cohesive single-tenant digital ecosystem. Built on a resilient PHP 8.2+ backend with a normalized MariaDB relational database (25 tables), the platform provides four distinct Role-Based Access Control (RBAC) tiers: System Administrator, Municipal Government Officer, Verified Local Vendor, and Citizen/Customer.

The E-Governance subsystem automates the entire commercial licensing lifecycle. Prospective vendors submit multi-step digital applications, attach official compliance files (PAN/VAT certificates, citizenship cards, municipal trade permits), and define their store location on an interactive Leaflet/OpenStreetMap GIS map. Government inspection officers audit submitted records via an inspection dashboard, review individual files, issue correction requests, and upon approval, trigger the automated generation of a cryptographically traceable Digital Business License (LIC-YYYY-XXXXXX). Each license features a dynamic QR code linked to a public real-time verification endpoint (verify.php). Furthermore, the governance module features a public grievance system (CMP-YYYY-XXXXXX) that supports geographic pinpointing, priority assignment, evidence uploads, and departmental dispatch.

The E-Commerce subsystem restricts marketplace listing privileges strictly to approved, verified vendors possessing an active business license. Citizens browse verified local goods with category filtering, manage persistent carts, and execute checkout transactions with server-side stock and price validation. Payment processing supports native Cash on Delivery (COD) and a simulated gateway interface for national digital wallets (eSewa, Khalti, ConnectIPS) designed specifically for demonstration and academic evaluation without requiring live banking credentials. An atomic database transaction protocol guarantees inventory consistency and triggers immediate real-time in-app notifications and audit log records. A verified-buyer review system ensures feedback authenticity.

Comprehensive testing—including unit, integration, role authorization, and security vulnerability evaluations—confirmed high reliability, sub-second query responsiveness, strict protection against SQL injection and Cross-Site Request Forgery (CSRF), and reliable bilingual support (English and Nepali). The resulting platform demonstrates that integrating municipal governance workflows directly with localized digital commerce enhances administrative transparency, eliminates unlicensed commercial activity, fosters citizen trust, and empowers local economic growth."""
}

ABBREVIATIONS = [
    ("API", "Application Programming Interface"),
    ("BCRYPT", "Blowfish-based Cryptographic Hashing Algorithm"),
    ("COD", "Cash on Delivery"),
    ("CRUD", "Create, Read, Update, Delete"),
    ("CSRF", "Cross-Site Request Forgery"),
    ("CSS", "Cascading Style Sheets"),
    ("DBMS", "Database Management System"),
    ("DFD", "Data Flow Diagram"),
    ("DOM", "Document Object Model"),
    ("ER / ERD", "Entity-Relationship / Entity-Relationship Diagram"),
    ("GIS", "Geographic Information System"),
    ("HTML", "HyperText Markup Language"),
    ("HTTP / HTTPS", "HyperText Transfer Protocol / HyperText Transfer Protocol Secure"),
    ("i18n", "Internationalization / Multilingual Localization"),
    ("JSON", "JavaScript Object Notation"),
    ("LAN", "Local Area Network"),
    ("MIME", "Multipurpose Internet Mail Extensions"),
    ("MVC", "Model-View-Controller"),
    ("MySQL / MariaDB", "My Structured Query Language Relational Database Engine"),
    ("PAN", "Permanent Account Number"),
    ("PDO", "PHP Data Objects"),
    ("PHP", "Hypertext Preprocessor"),
    ("QR Code", "Quick Response Code"),
    ("RBAC", "Role-Based Access Control"),
    ("SQL", "Structured Query Language"),
    ("UAT", "User Acceptance Testing"),
    ("UI", "User Interface"),
    ("URI / URL", "Uniform Resource Identifier / Uniform Resource Locator"),
    ("VAT", "Value Added Tax"),
    ("XAMPP", "Cross-Platform, Apache, MariaDB, PHP, and Perl"),
    ("XSS", "Cross-Site Scripting")
]

TABLES_SUMMARY = [
    ("roles", "Defines system access roles (admin, officer, vendor, customer) for RBAC enforcement"),
    ("departments", "Stores municipal inspection and licensing departments (Commerce, Agriculture, Consumer Protection)"),
    ("users", "User account master table storing credentials, bcrypt hashes, contact info, and role linkages"),
    ("addresses", "Physical addresses for customers and vendors with municipality and district data"),
    ("vendors", "Vendor business profiles, registration data, licensing status, and GIS map coordinates"),
    ("vendor_applications", "Tracks multi-step vendor onboarding applications, inspection remarks, and review cycles"),
    ("vendor_documents", "Stores uploaded official verification documents (PAN, citizenship, permits) and review status"),
    ("licenses", "Digital business licenses issued by officers with serial numbers, validity dates, and QR code paths"),
    ("government_services", "Catalog of municipal digital services, documentation requirements, processing times, and fees"),
    ("service_applications", "Citizen applications submitted for municipal services, approval state, and officer remarks"),
    ("service_application_documents", "Document attachments supporting citizen municipal service requests"),
    ("categories", "Product taxonomy and categories with slugs and banner images"),
    ("products", "Merchant product catalog records with pricing, stock quantity, slug, and active status"),
    ("product_images", "Supplementary product gallery images linked to individual catalog items"),
    ("cart", "Active persistent shopping cart headers tied to authenticated customer sessions"),
    ("cart_items", "Itemized product entries within shopping carts with recorded quantities and unit prices"),
    ("orders", "Master customer order records with fulfillment status, payment method, and shipping address"),
    ("order_items", "Itemized line items within orders linking products, vendors, quantities, and subtotal amounts"),
    ("payments", "Payment transaction logs capturing payment methods, transaction IDs, amounts, and statuses"),
    ("complaints", "Public grievance repository capturing incident descriptions, GIS coordinates, and assignments"),
    ("complaint_attachments", "Photographic and evidentiary document attachments supporting filed complaints"),
    ("reviews", "Verified customer product ratings (1-5 stars) and qualitative feedback restricted to delivered orders"),
    ("notifications", "In-app notification messages delivered to individual user dashboards with redirection links"),
    ("audit_logs", "Security audit trail recording system actions, target entities, user IDs, and client IP addresses"),
    ("password_resets", "Cryptographic token storage for self-service user password recovery workflows")
]
