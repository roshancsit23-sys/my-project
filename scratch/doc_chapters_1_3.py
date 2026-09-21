# -*- coding: utf-8 -*-
"""
SmartGov Market Documentation - Chapters 1 to 3
"""

CHAPTER_1 = {
    "title": "CHAPTER 1: INTRODUCTION",
    "sections": [
        {
            "num": "1.1",
            "title": "Background",
            "content": """Electronic Governance (E-Governance) and Electronic Commerce (E-Commerce) represent two of the most transformative digital paradigms of the modern era. E-Governance refers to the deployment of Information and Communication Technologies (ICT) by governmental agencies to enhance administrative efficiency, foster transparency, streamline public service delivery, and empower citizens with direct civic participation. Simultaneously, E-Commerce encompasses the digital transaction of goods and services over computer networks, eliminating geographic barriers and democratizing market access for businesses of all scales.

Historically, these two domains have developed within distinct institutional and technological silos. Municipal e-governance initiatives typically focus on regulatory compliance, digital tax collection, building permit approvals, and civic grievance resolution. Conversely, commercial online marketplaces focus strictly on inventory aggregation, consumer engagement, digital payments, and logistics fulfillment. This functional partition forces local merchants to interact with governmental regulatory boards through disjointed bureaucratic procedures while separately navigating commercial platforms that offer zero municipal verification or institutional credibility.

The convergence of E-Governance and E-Commerce introduces a symbiotic digital framework. When local governance authorities digitize merchant licensing and couple it directly with a municipal digital marketplace, the administrative barrier between regulatory oversight and local economic vitality is dissolved. Local merchants receive formal legitimacy through authenticated digital licensing, municipal authorities maintain real-time visibility into commercial activity, and citizens gain access to a trusted marketplace where all participating vendors are verified public entities."""
        },
        {
            "num": "1.2",
            "title": "Introduction to SmartGov Market",
            "content": """SmartGov Market (locally operationalized and branded under the title "HATIYA - Digital Governance & Marketplace Platform", referencing the traditional weekly community market gathering) is a full-stack, integrated web application engineered to bridge municipal governance and digital commerce into a single unified platform. 

SmartGov Market is not a mere marketplace clone, nor is it simply a municipal static noticeboard. Rather, it represents an integrated digital ecosystem where municipal regulatory oversight directly underpins commercial transaction legitimacy. Within this platform, local vendors register and submit multi-stage business applications accompanied by digital copies of official government credentials (such as PAN/VAT registrations, citizenship identification cards, and municipal operating permits). Municipal Government Officers review and verify these credentials through an administrative workflow, culminating in the issuance of an official, cryptographically traceable Digital Business License (bearing the standard serial format LIC-YYYY-XXXXXX).

Each digital business license is equipped with an authenticated Quick Response (QR) code linked to a real-time public verification portal (verify.php). Critically, the platform enforces strict architectural boundaries: only vendors possessing an active, verified digital license are granted listing and sales privileges within the marketplace. Citizens and customers are provided with a modern, glassmorphic marketplace interface where they can browse authenticated local products, manage carts, place atomic orders with stock reservation, select delivery addresses, and execute transactions using Cash on Delivery (COD) or simulated national digital wallet gateways (eSewa, Khalti, and ConnectIPS). Furthermore, citizens can lodge location-aware public grievances (CMP-YYYY-XXXXXX) mapped on an interactive Geographic Information System (GIS) Leaflet map, track service applications, and receive real-time dashboard notifications."""
        },
        {
            "num": "1.3",
            "title": "Problem Statement",
            "content": """Contemporary local economic and municipal administrative systems suffer from several critical shortcomings:

1. Fragmented Business Registration and Licensing: Traditional municipal business licensing relies on physical paperwork, in-person queueing, and manual file reviews. This leads to substantial processing delays, administrative overhead, and the absence of a unified, publicly verifiable registry of operating licenses.
2. Inability to Verify Merchant Legitimacy: On conventional open commercial platforms, consumers frequently encounter fraudulent sellers, counterfeit merchandise, and misleading business representations due to the absence of verifiable governmental accreditation.
3. Lack of Transparent Grievance Mechanisms: Citizens facing consumer exploitation, substandard product quality, or service deficiencies encounter cumbersome municipal complaint procedures lacking real-time progress tracking, evidence archiving, or geographic mapping.
4. Economic Disadvantage for Small Local Merchants: Micro, small, and medium enterprises (MSMEs) in local municipalities often lack the technical resources to establish standalone digital storefronts, leaving them vulnerable to predatory intermediate commercial aggregators.
5. Ineffective Administrative Oversight: Municipal authorities lack real-time data analytics regarding local commercial activity, registered vendors, market revenue flows, and consumer complaint hotspots."""
        },
        {
            "num": "1.4",
            "title": "Motivation",
            "content": """The development of SmartGov Market was driven by the imperative to modernize municipal governance while actively stimulating local commerce. In many developing economies, the local government represents the most immediate tier of civic trust. By leveraging this institutional trust as the foundation for digital commerce, several transformative advantages emerge:

- Empowering Municipalities: Equipping local ward and municipal offices with web-based tools to process business applications, inspect digital documents, enforce statutory compliance, and issue instant digital licenses.
- Cultivating Consumer Confidence: Assuring citizens that every vendor listed on the platform is an officially vetted, legally compliant merchant with physical operating coordinates.
- Streamlining Civic Redressal: Integrating public complaints directly into the municipal officer dashboard with geographic coordinates to expedite dispute resolution.
- Technical Advancement: Demonstrating how modular PHP architecture, relational database normalization, dynamic client-side scripting, and geographic information systems can solve real-world governance challenges without prohibitive proprietary software licensing costs."""
        },
        {
            "num": "1.5",
            "title": "Objectives of the Project",
            "content": """The project is guided by overarching general objectives complemented by specific technical and functional targets:

1.5.1 General Objective:
To design, engineer, test, and document a robust, secure, and integrated web-based platform that harmonizes municipal regulatory licensing (E-Governance) with a verified commercial retail marketplace (E-Commerce) for local municipalities.

1.5.2 Specific Objectives:
- To implement a multi-tiered Role-Based Access Control (RBAC) system supporting Administrator, Government Officer, Vendor, and Citizen/Customer roles.
- To develop an automated vendor licensing lifecycle featuring multi-step digital document submission, officer document inspection, correction requests, and instant cryptographic license generation (LIC-YYYY-XXXXXX).
- To construct a public, mobile-accessible QR verification engine (verify.php) enabling instant authentication of merchant licensing status (VALID, EXPIRED, SUSPENDED).
- To engineer a full-featured e-commerce marketplace incorporating product categorization, keyword search, inventory stock management, persistent shopping carts, and atomic order placement transactions.
- To integrate a multi-method payment architecture supporting native Cash on Delivery (COD) alongside an academic simulated gateway interface for eSewa, Khalti, and ConnectIPS.
- To incorporate an interactive Leaflet.js / OpenStreetMap GIS mapping module to visually plot verified vendor store locations and public grievance coordinates.
- To implement a dual-scope citizen grievance redressal system (CMP-YYYY-XXXXXX) with evidence file attachment and municipal officer dispatch.
- To enforce verified-purchaser product reviews, ensuring that feedback is restricted exclusively to customers who have taken delivery of the item.
- To integrate bilingual localization (i18n) supporting seamless switching between English and Nepali languages.
- To establish a comprehensive security audit logging framework (audit_logs) tracking user activities, IP addresses, and state alterations across the application."""
        },
        {
            "num": "1.6",
            "title": "Scope of the Project",
            "content": """The scope of SmartGov Market encompasses the complete functional pipeline from municipal vendor onboarding to end-user retail fulfillment:

- User Onboarding & Authentication: Secure citizen registration, vendor onboarding, session management, BCRYPT password hashing, and password recovery.
- Administrative Governance: Centralized administrative and municipal officer dashboards for inspecting applications, approving or rejecting documents, managing municipal service directories, and analyzing visual Chart.js metrics.
- Licensing Subsystem: Automated creation and printable presentation of official business certificates complete with machine-readable QR codes and validity periods.
- E-Commerce Engine: Vendor product catalog management, image uploads, stock inventory decrementing, customer cart operations, checkout processing, order status tracking (Pending Payment, Paid, Processing, Shipped, Delivered, Cancelled), and customer transaction logs.
- Public Complaints & Municipal Services: Citizen application for official municipal services (permits, recommendations), grievance filing with photo/PDF attachment, and status tracking.
- Visual & Geographic Tools: Leaflet GIS map visualization for stores and complaints; dynamic Chart.js dashboards for sales revenue, vendor distribution, and complaint categories."""
        },
        {
            "num": "1.7",
            "title": "Limitations of the System",
            "content": """Based on the actual implementation of the project, the following realistic boundaries and limitations are explicitly noted:

1. Simulated Payment Gateway: In accordance with academic demonstration and local testing constraints, integrations with eSewa, Khalti, and ConnectIPS are implemented as simulated demo gateways (demo-payment.php). While state transitions and transaction IDs (DEMO-XXXXXXXX) are stored atomically in the database, no real financial debits occur against live bank accounts.
2. Localized SMS/Email Dispatch: In-app notifications are delivered dynamically to authenticated user dashboards; however, external third-party SMS/SMTP gateway dispatch requires active carrier credentials and API credits, which are not bound in this local deployment.
3. Server Architecture: The current release is architected as a modular monolithic PHP web application running under Apache/PHP with MariaDB in a local environment (such as XAMPP), rather than a distributed cloud microservices infrastructure.
4. Document Verification Automation: Document inspection (PAN/VAT cards, identity certificates) is performed manually by human municipal officers via the administrative document viewer rather than through automated optical character recognition (OCR) or live national ID database API sync."""
        },
        {
            "num": "1.8",
            "title": "Significance of the Project",
            "content": """SmartGov Market provides distinct, tangible benefits across all stakeholder groups:

- For Citizens / Customers: Guarantees peace of mind by ensuring every merchant on the platform is government-verified; provides direct access to municipal services and public grievance channels; delivers a seamless, localized shopping experience with transparent order tracking.
- For Local Vendors: Drastically accelerates the business licensing process; provides an official digital business license with a printable certificate and QR verification code; offers an immediate digital storefront to reach local consumers without intermediate aggregator commission fees.
- For Municipal Officers: Replaces physical paper queues with an organized digital inspection inbox; allows granular inspection and remarking on compliance documents; automates license generation; provides geographic visibility over complaints.
- For System Administrators: Grants comprehensive oversight over system users, vendor statuses, marketplace transactions, security audit logs, and instant CSV analytical report generation."""
        },
        {
            "num": "1.9",
            "title": "Organization of the Report",
            "content": """This report is systematically organized into nine chapters and supplementary appendices:

- Chapter 1: Introduction establishes the background, concept, problem statement, objectives, scope, limitations, and significance of the SmartGov Market platform.
- Chapter 2: Literature Review & Existing System surveys traditional municipal procedures and standalone e-commerce systems, detailing their deficiencies and presenting a rigorous comparative matrix.
- Chapter 3: System Analysis and Requirements details the functional and non-functional requirements, user personas, hardware/software specifications, and multi-dimensional feasibility study.
- Chapter 4: System Design provides comprehensive architectural designs, including system architecture diagrams, RBAC matrices, Use Case diagrams, Data Flow Diagrams (DFDs), Entity-Relationship (ER) models, complete 25-table database schemas, sequence diagrams, and activity diagrams.
- Chapter 5: System Implementation details the technical construction of the platform, including development stack, folder structure, database connection logic, authentication, security countermeasures, and module workflows.
- Chapter 6: User Interface and Screenshots presents visual layouts and screen documentation for over twenty distinct operational interfaces.
- Chapter 7: Testing and Quality Assurance details testing strategies, unit/integration verification, and a comprehensive functional test case matrix documenting actual pass/fail statuses.
- Chapter 8: Results and Discussion analyzes the operational achievements of the platform across e-governance, commerce, and administrative domains, alongside challenges encountered.
- Chapter 9: Conclusion and Future Enhancements synthesizes key conclusions and identifies clear technical trajectories for future enhancements.
- References & Appendices provide academic citations, complete database schemas, source code listings, setup instructions, and user manuals."""
        }
    ]
}

CHAPTER_2 = {
    "title": "CHAPTER 2: LITERATURE REVIEW / EXISTING SYSTEM",
    "sections": [
        {
            "num": "2.1",
            "title": "Introduction",
            "content": """The literature surrounding public administration and electronic commerce highlights a significant paradigm shift over the past two decades. As governments globally adopt 'Digital First' strategies, the definition of public service has expanded from passive informational websites to interactive transactional portals. Concurrently, digital commerce has matured from basic mail-order catalogs into hyper-connected retail marketplaces. This chapter examines existing paradigms in both sectors, evaluates traditional operational workflows, and establishes the theoretical and practical justification for the integrated SmartGov Market architecture."""
        },
        {
            "num": "2.2",
            "title": "Existing E-Governance Systems",
            "content": """Modern e-governance implementations are typically categorized into four delivery models: Government-to-Citizen (G2C), Government-to-Business (G2B), Government-to-Government (G2G), and Government-to-Employee (G2E). In developing countries, municipal G2C and G2B portals primarily deliver informational circulars, downloadable PDF application forms, tax payment portals, and rudimentary grievance filing forms. 

While platforms such as national single-window registries have streamlined company incorporation at the macro-level, municipal-level trade licensing remains largely disconnected. Local businesses frequently obtain municipal operational permits through manual physical submissions. Furthermore, public verification of these licenses is virtually non-existent: third parties have no immediate, mobile-accessible mechanism to verify whether a local vendor's operating certificate is valid, suspended, or forged."""
        },
        {
            "num": "2.3",
            "title": "Existing E-Commerce Systems",
            "content": """The global e-commerce landscape is dominated by large-scale enterprise marketplaces (such as Amazon, eBay, and regional aggregators like Daraz in South Asia). These platforms excel at catalog aggregation, search optimization, recommendation algorithms, customer loyalty incentives, and logistical fulfillment. 

However, enterprise marketplaces operate on commercial profit incentives that prioritize large national distributors and high-volume importers over localized community artisans and municipal producers. More critically, vendor onboarding on these platforms focuses solely on internal marketplace compliance (tax identification and banking credentials) without verifying local municipal operating legality or physical municipal zoning compliance. Consequently, consumer trust suffers when counterfeit goods or unregulated vendors infiltrate the marketplace."""
        },
        {
            "num": "2.4",
            "title": "Problems in Existing / Traditional Systems",
            "content": """A rigorous analysis of traditional municipal administration and conventional e-commerce reveals several structural deficiencies:

1. Information Asymmetry: Consumers have no mechanism to ascertain whether an online seller operates with valid municipal trade approval, certified hygiene standards, or verified physical operating premises.
2. Inefficient Licensing Overhead: Traditional vendor verification requires municipal officers to process paper archives, leading to misplaced files, delayed business openings, and lost municipal tax revenue.
3. Lack of Counterfeit Protection for Licenses: Paper certificates displayed on shop walls are easily falsified, altered, or presented long past their expiration date without public detection.
4. Disjointed Grievance Redressal: When consumers encounter fraudulent practices or defective products, traditional municipal consumer protection units require in-person formal petitions, discouraging citizen reporting.
5. High Barrier to Entry for MSMEs: Micro and small local enterprises find established commercial marketplaces cost-prohibitive due to commission fees, complex logistics requirements, and algorithmic bias favoring international imports."""
        },
        {
            "num": "2.5",
            "title": "Proposed SmartGov Market System",
            "content": """The SmartGov Market platform resolves these systemic problems by integrating municipal regulatory licensing directly into an accessible local e-commerce marketplace:

- Unified Onboarding Pipeline: Local businesses complete registration, upload compliance documentation, and pin their physical store coordinates on an interactive GIS map in a single digital session.
- Rigorous Officer Verification Workflow: Dedicated municipal officer portals present digital document viewers, remark fields, correction request mechanisms, and single-click approval protocols.
- Cryptographically Traceable Digital Licenses: Automated generation of standardized digital licenses (LIC-YYYY-XXXXXX) embedded with authenticated QR codes that link to a public, mobile-friendly verification endpoint.
- Exclusive Marketplace Listing Privilege: The marketplace catalog strictly excludes unapproved or suspended merchants. Only vendors holding a verified, active license can publish products, receive customer orders, and collect payments.
- Transparent Citizen Grievance Redressal: Integrated public complaint portal featuring Leaflet GIS geocoding, file evidence uploads, automated departmental dispatch, and real-time status updates."""
        },
        {
            "num": "2.6",
            "title": "Comparison Between Existing and Proposed Systems",
            "content": """To clearly illustrate the architectural and operational advantages of SmartGov Market, the table below provides a detailed multi-criteria comparative analysis against traditional municipal processes, generic commercial marketplaces, and standalone e-governance portals."""
        },
        {
            "num": "2.7",
            "title": "Related Technologies and Concepts",
            "content": """The conceptual design of SmartGov Market is informed by established software engineering and public administration frameworks:

- Relational Database Normalization: Structuring database entities into Boyce-Codd / Third Normal Form (3NF) to eliminate data redundancy, maintain referential integrity, and enforce strict foreign key constraints across commercial and civic tables.
- Role-Based Access Control (RBAC): Enforcing granular authorization boundaries where users are assigned roles with strictly defined permission scopes (Admin, Officer, Vendor, Customer).
- Geographic Information Systems (GIS) in Municipal Governance: Utilizing spatial geocoding and map visualizers (Leaflet.js with OpenStreetMap) to contextualize civic grievances and merchant distributions across municipal wards.
- Cryptographic Token Verification: Utilizing randomized unique tokens and formatted alphanumeric identifiers for secure public verification and password recovery without exposing internal database keys."""
        }
    ]
}

CHAPTER_3 = {
    "title": "CHAPTER 3: SYSTEM ANALYSIS AND REQUIREMENTS",
    "sections": [
        {
            "num": "3.1",
            "title": "System Analysis",
            "content": """System analysis constitutes the systematic investigation of user needs, administrative workflows, data flows, and technological constraints required to design a dependable integrated web platform. The analysis phase of SmartGov Market evaluated the interactions among four primary user groups: Citizens/Customers seeking reliable civic services and authentic goods; Local Merchants seeking digital licensing and direct commercial sales; Municipal Officers charged with document inspection and regulatory enforcement; and System Administrators responsible for platform integrity, security, and reporting."""
        },
        {
            "num": "3.2",
            "title": "Functional Requirements",
            "content": """Functional requirements define the core software capabilities, behavioral responses, and processing logic implemented within the SmartGov Market codebase. Based exclusively on the actual implementation, the functional requirements are categorized by functional module:

3.2.1 User & Authentication Management:
- FR-01: The system shall provide user registration for Citizens and Vendors with input validation (full name, unique email, phone number, physical address, municipality, district).
- FR-02: The system shall enforce BCRYPT password hashing on all user passwords prior to database persistence.
- FR-03: The system shall provide secure authentication (login/logout) with session fixation protection (session_regenerate_id) and role-based dashboard routing.
- FR-04: The system shall enforce automatic session timeout after 7,200 seconds (2 hours) of user inactivity.
- FR-05: The system shall provide self-service password recovery via secure, time-expiring cryptographic tokens (password_resets).

3.2.2 Vendor Onboarding & Digital Licensing (E-Governance):
- FR-06: The system shall provide a multi-step vendor registration form capturing business name, business sector, PAN/VAT number, registration number, address, and GIS map coordinates.
- FR-07: The system shall allow vendors to upload official compliance documents (PAN/VAT certificates, citizenship cards, municipal permits) restricted to PDF, JPG, and PNG formats under 5MB.
- FR-08: The system shall maintain vendor status states: 'Pending', 'Under Review', 'Correction Required', 'Approved', 'Rejected', and 'Suspended'.
- FR-09: The system shall restrict unapproved or suspended vendors from adding products or publishing catalog items to the marketplace.
- FR-10: The system shall provide Government Officers with an inspection queue to review applications, view uploaded files in a secure viewer, verify or reject individual files, and append officer remarks.
- FR-11: The system shall automatically generate a unique Digital Business License (LIC-YYYY-XXXXXX) with an annual expiration date upon officer approval of all mandatory documents.
- FR-12: The system shall generate a dynamic QR code for each issued license pointing to the public verification endpoint (verify.php).
- FR-13: The system shall provide a public verification page accessible to any user or mobile device to inspect license authenticity and status (VALID, EXPIRED, SUSPENDED).
- FR-14: The system shall enable vendors to request license renewals and permit officers to review and grant one-year validity extensions.

3.2.3 E-Commerce Marketplace & Order Management:
- FR-15: The system shall allow approved vendors to manage their product catalog (Create, Read, Update, Delete) including title, slug, description, price, stock quantity, primary image, and status.
- FR-16: The system shall enable consumers to browse verified products with category filtering, keyword search, price sorting, and vendor store filtering.
- FR-17: The system shall maintain persistent customer shopping carts tied to authenticated user sessions.
- FR-18: The system shall execute atomic database transactions for checkout, validating stock availability, recalculating item totals on the backend, deducting inventory quantities, generating a unique order number (ORD-YYYY-XXXXXX), and clearing the cart.
- FR-19: The system shall support Cash on Delivery (COD) and a simulated digital gateway portal for eSewa, Khalti, and ConnectIPS.
- FR-20: The system shall maintain customer order tracking displaying status progressions: 'Pending Payment', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Cancelled', and 'Refunded'.
- FR-21: The system shall permit vendors to update the fulfillment status of orders containing items from their catalog.
- FR-22: The system shall automatically restore product inventory levels upon order cancellation.
- FR-23: The system shall restrict product ratings and reviews (1 to 5 stars) strictly to customers who have an authenticated order with 'Delivered' status for that product.

3.2.4 Civic Services & Grievance Redressal (E-Governance):
- FR-24: The system shall display a municipal government services directory detailing departmental ownership, processing times, fee structures, and requirements.
- FR-25: The system shall allow citizens to apply digitally for municipal services (SRV-YYYY-XXXXXX) and attach supporting documentation.
- FR-26: The system shall allow citizens to lodge public grievances (CMP-YYYY-XXXXXX) with category selection (Vendor, Product, Delivery, Municipal Service), priority rating, written description, photographic/document evidence, and Leaflet GIS map pinning.
- FR-27: The system shall allow officers and administrators to view complaints on an interactive GIS map, assign complaints to departments, investigate issues, and record official resolutions.

3.2.5 Platform Administration & Security Auditing:
- FR-28: The system shall provide an Administrator Dashboard featuring real-time database metric counters and dynamic Chart.js visualizations (Vendors by Status, Products by Category, Complaints by Category).
- FR-29: The system shall log significant administrative, financial, and security actions into an immutable audit trail (audit_logs) capturing user ID, action name, target entity, entity ID, description, and client IP address.
- FR-30: The system shall provide instant CSV report generation for vendors, orders, and complaints."""
        },
        {
            "num": "3.3",
            "title": "Non-Functional Requirements",
            "content": """Non-functional requirements specify qualitative benchmarks, security constraints, and operational characteristics:

1. Security:
   - Password encryption using PHP's native BCRYPT algorithm with standard work factor.
   - 100% parameterization of SQL statements via PDO prepared queries to eliminate SQL injection vulnerabilities.
   - Synchronizer Token Pattern CSRF defense on all state-altering POST requests.
   - Multipurpose Internet Mail Extensions (MIME) validation and file extension whitelisting on file uploads.
   - Comprehensive XSS defense through automated HTML entity encoding (htmlspecialchars) and input sanitization.
   - Secure session handling with HttpOnly and SameSite cookie attributes.

2. Performance & Responsiveness:
   - Sub-second server response times (< 500ms) for standard catalog browsing and dashboard rendering under typical local network loads.
   - Database queries optimized with foreign key indexes and limit clauses.
   - Asynchronous AJAX handling for cart addition and interactive map rendering without page reloads.

3. Usability & User Experience:
   - Modern glassmorphic visual aesthetic with coherent typography, consistent spacing, and intuitive iconography (Font Awesome 6).
   - Fully responsive layout adapting dynamically to mobile smartphones, tablets, laptops, and desktop displays via Bootstrap 5 flexbox and grid systems.
   - Instant client-side bilingual language switching (English / Nepali) without requiring page refresh.

4. Reliability & Data Integrity:
   - Atomic database transactions (BEGIN TRANSACTION, COMMIT, ROLLBACK) for multi-table updates during order placement and payment execution.
   - Foreign key cascading rules and strict data type definitions preventing orphaned records.

5. Maintainability & Modularity:
   - Separation of concerns across configuration (config/), business middleware (includes/), backend API endpoints (api/), role modules (admin/, officer/, vendor/), and view templates."""
        },
        {
            "num": "3.4",
            "title": "Hardware Requirements",
            "content": """The hardware requirements for hosting and operating SmartGov Market are detailed below:

Development / Server Environment:
- Processor: Intel Core i3 / AMD Ryzen 3 or higher (2.0 GHz multi-core)
- Memory (RAM): Minimum 4 GB (8 GB recommended for concurrent database testing)
- Storage: Minimum 500 MB free disk space for application files, uploads, and database tables
- Network: Standard Network Interface Card (NIC) with TCP/IP protocol support

Client / User Environment:
- Device: Any standard desktop PC, laptop, tablet, or smartphone
- Display Resolution: Minimum 360x640 (mobile) up to 1920x1080 (Full HD desktop)
- Input: Touch screen, mouse, or keyboard"""
        },
        {
            "num": "3.5",
            "title": "Software Requirements",
            "content": """The software prerequisites for development, hosting, and client execution:

Server & Hosting Stack:
- Operating System: Microsoft Windows 10/11, Linux (Ubuntu/Debian/CentOS), or macOS
- Web Server: Apache HTTP Server 2.4+ (configured via XAMPP)
- Backend Language: PHP 8.0 or higher (PHP 8.2+ recommended with PDO, pdo_mysql, and fileinfo extensions enabled)
- Database Engine: MySQL 8.0+ or MariaDB 10.4+ with InnoDB engine and utf8mb4 character set

Client Stack:
- Web Browser: Google Chrome (v90+), Mozilla Firefox (v88+), Microsoft Edge (v90+), or Apple Safari (v14+)
- JavaScript: Client-side JavaScript execution enabled (for Leaflet GIS, Chart.js, and i18n switcher)"""
        },
        {
            "num": "3.6",
            "title": "User Characteristics",
            "content": """The platform addresses four user personas with diverse technical proficiencies:
- Citizens / Customers: General public possessing basic digital literacy; require simple search, intuitive shopping cart navigation, and transparent complaint submission.
- Vendors / Merchants: Local business owners possessing moderate computer literacy; require straightforward product addition forms, document uploaders, and clear licensing renewal notifications.
- Government Inspection Officers: Municipal staff with administrative computer experience; require clear document inspection queues, checklist verification tools, and remark fields.
- System Administrators: Advanced technical personnel requiring access to user management, database audit logs, system configurations, and CSV analytical exports."""
        },
        {
            "num": "3.7",
            "title": "System Constraints",
            "content": """The platform is subject to several design and deployment constraints:
- Must run efficiently within standard LAMP/WAMP local hosting environments (e.g. XAMPP) without requiring node-based runtime daemons.
- Must not depend on proprietary third-party paid software licenses.
- Payment gateway interactions must be modeled as clean simulated gateways for academic evaluation, preventing unintended financial charges during grading and demonstration.
- File uploads must be constrained to local disk directories (uploads/) with safe hex naming conventions."""
        },
        {
            "num": "3.8",
            "title": "Feasibility Study",
            "content": """A four-dimensional feasibility study was conducted prior to system development:

1. Technical Feasibility: The chosen technology stack—PHP 8.2, MariaDB, Bootstrap 5, Leaflet.js, and Chart.js—is mature, battle-tested, extensively documented, and supported by a vast global developer ecosystem. All required features (file uploads, geospatial mapping, transactional ordering, QR rendering) are fully achievable using these open-source tools. Technical feasibility is rated HIGH.

2. Economic Feasibility: The entire software stack is open source and freely redistributable under GPL/MIT licenses. Development was executed using existing personal computing equipment without incurring software licensing fees, domain expenses, or proprietary server costs. Economic feasibility is rated HIGH.

3. Operational Feasibility: The platform mirrors established municipal governance structures (Departments, Officers, Licenses) and popular commercial checkout workflows. Minimal training is required for municipal officers and vendors. Operational feasibility is rated HIGH.

4. Schedule Feasibility: Development was planned across structured sprint cycles (Database Schema Design, Authentication & RBAC, Licensing Module, E-Commerce Core, Civic Grievance & GIS, Security & Testing) and completed within the allocated academic semester schedule. Schedule feasibility is rated HIGH."""
        }
    ]
}
