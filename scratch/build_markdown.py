# -*- coding: utf-8 -*-
"""
SmartGov Market - Master Documentation Generator
Generates:
1. SMARTGOV_MARKET_PROJECT_DOCUMENTATION.md
2. SMARTGOV_MARKET_PROJECT_DOCUMENTATION.docx
3. SMARTGOV_MARKET_PROJECT_DOCUMENTATION.pdf
"""

import os
import sys

# Add scratch to sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

import doc_content
import doc_chapters_1_3
import doc_chapters_4_5
import doc_chapters_6_9
import doc_appendices

from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

ROOT_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MD_PATH = os.path.join(ROOT_DIR, "SMARTGOV_MARKET_PROJECT_DOCUMENTATION.md")
DOCX_PATH = os.path.join(ROOT_DIR, "SMARTGOV_MARKET_PROJECT_DOCUMENTATION.docx")
PDF_PATH = os.path.join(ROOT_DIR, "SMARTGOV_MARKET_PROJECT_DOCUMENTATION.pdf")

print(f"Target Outputs:\n  MD:   {MD_PATH}\n  DOCX: {DOCX_PATH}\n  PDF:  {PDF_PATH}\n")

# ==============================================================================
# 1. MARKDOWN BUILDER
# ==============================================================================
def build_markdown():
    print("Generating Markdown Documentation...")
    lines = []

    # Title & Metadata
    lines.append(f"# {doc_content.METADATA['title']}")
    lines.append(f"## {doc_content.METADATA['subtitle']}")
    lines.append(f"### {doc_content.METADATA['doc_type']}\n")
    lines.append("---\n")

    lines.append("## Project Metadata & Academic Details")
    lines.append(f"- **Student Name:** {doc_content.METADATA['student_name']}")
    lines.append(f"- **Roll / Registration No.:** {doc_content.METADATA['roll_no']}")
    lines.append(f"- **Course / Subject:** {doc_content.METADATA['course']}")
    lines.append(f"- **Department:** {doc_content.METADATA['department']}")
    lines.append(f"- **Institution:** {doc_content.METADATA['institution']}")
    lines.append(f"- **Project Supervisor:** {doc_content.METADATA['supervisor']}")
    lines.append(f"- **Academic Year:** {doc_content.METADATA['academic_year']}\n")
    lines.append("---\n")

    # Certificate
    lines.append("## CERTIFICATE\n")
    lines.append(doc_content.PRELIMINARY['certificate'])
    lines.append("\n---\n")

    # Declaration
    lines.append("## DECLARATION\n")
    lines.append(doc_content.PRELIMINARY['declaration'])
    lines.append("\n---\n")

    # Acknowledgement
    lines.append("## ACKNOWLEDGEMENT\n")
    lines.append(doc_content.PRELIMINARY['acknowledgement'])
    lines.append("\n---\n")

    # Abstract
    lines.append("## ABSTRACT\n")
    lines.append(doc_content.PRELIMINARY['abstract'])
    lines.append("\n---\n")

    # Table of Contents Outline
    lines.append("## TABLE OF CONTENTS\n")
    lines.append("- **Preliminary Pages**")
    lines.append("  - Certificate")
    lines.append("  - Declaration")
    lines.append("  - Acknowledgement")
    lines.append("  - Abstract")
    lines.append("  - List of Figures")
    lines.append("  - List of Tables")
    lines.append("  - List of Abbreviations")
    lines.append("- **Chapter 1: Introduction**")
    lines.append("  - 1.1 Background")
    lines.append("  - 1.2 Introduction to SmartGov Market")
    lines.append("  - 1.3 Problem Statement")
    lines.append("  - 1.4 Motivation")
    lines.append("  - 1.5 Objectives (General & Specific)")
    lines.append("  - 1.6 Scope of the Project")
    lines.append("  - 1.7 Limitations of the System")
    lines.append("  - 1.8 Significance of the Project")
    lines.append("  - 1.9 Organization of the Report")
    lines.append("- **Chapter 2: Literature Review / Existing System**")
    lines.append("  - 2.1 Introduction")
    lines.append("  - 2.2 Existing E-Governance Systems")
    lines.append("  - 2.3 Existing E-Commerce Systems")
    lines.append("  - 2.4 Problems in Existing/Traditional Systems")
    lines.append("  - 2.5 Proposed SmartGov Market System")
    lines.append("  - 2.6 Comparison Between Existing and Proposed Systems")
    lines.append("  - 2.7 Related Technologies / Concepts")
    lines.append("- **Chapter 3: System Analysis and Requirements**")
    lines.append("  - 3.1 System Analysis")
    lines.append("  - 3.2 Functional Requirements (30 Implemented Requirements)")
    lines.append("  - 3.3 Non-Functional Requirements (Security, Performance, Usability, Reliability, Maintainability)")
    lines.append("  - 3.4 Hardware Requirements")
    lines.append("  - 3.5 Software Requirements")
    lines.append("  - 3.6 User Characteristics")
    lines.append("  - 3.7 System Constraints")
    lines.append("  - 3.8 Feasibility Study (Technical, Economic, Operational, Schedule)")
    lines.append("- **Chapter 4: System Design**")
    lines.append("  - 4.1 System Architecture")
    lines.append("  - 4.2 Overall Architecture Diagram")
    lines.append("  - 4.3 User Roles and Permissions Matrix")
    lines.append("  - 4.4 Use Case Diagram")
    lines.append("  - 4.5 Use Case Descriptions (6 Detailed Use Cases)")
    lines.append("  - 4.6 Data Flow Diagrams (Context Level 0, Level 1 E-Gov, Level 1 E-Commerce)")
    lines.append("  - 4.7 Entity Relationship (ER) Model")
    lines.append("  - 4.8 Database Design & Data Dictionary (25 Normalized Tables)")
    lines.append("  - 4.9 Class and Module Design")
    lines.append("  - 4.10 Sequence Diagrams (Registration, Licensing, Order Processing)")
    lines.append("  - 4.11 Activity Diagrams (Licensing Lifecycle, Order Fulfillment)")
    lines.append("- **Chapter 5: System Implementation**")
    lines.append("  - 5.1 Development Environment")
    lines.append("  - 5.2 Technologies Used")
    lines.append("  - 5.3 Project Folder Structure")
    lines.append("  - 5.4 Database Connection & Dynamic Resilience (Ports 3307/3306)")
    lines.append("  - 5.5 Authentication, Session Hardening & RBAC")
    lines.append("  - 5.6 User / Citizen Module Implementation")
    lines.append("  - 5.7 Vendor Module Implementation")
    lines.append("  - 5.8 E-Commerce Marketplace Implementation")
    lines.append("  - 5.9 E-Governance Module Implementation")
    lines.append("  - 5.10 Administrator Module Implementation")
    lines.append("  - 5.11 Payment System Implementation (Demo Gateway)")
    lines.append("  - 5.12 Security Implementation (SQLi, XSS, CSRF, File Upload Defense)")
    lines.append("  - 5.13 UI/UX Design and Bilingual Localization (i18n)")
    lines.append("- **Chapter 6: User Interface and Screenshots (22 Detailed Figures)**")
    lines.append("- **Chapter 7: Testing and Quality Assurance**")
    lines.append("  - 7.1 Testing Introduction")
    lines.append("  - 7.2 Testing Strategy")
    lines.append("  - 7.3 Unit Testing")
    lines.append("  - 7.4 Integration Testing")
    lines.append("  - 7.5 System Testing")
    lines.append("  - 7.6 User Acceptance Testing (UAT)")
    lines.append("  - 7.7 Functional Test Cases Matrix (22 Executed Scenarios)")
    lines.append("  - 7.8 Validation and Error Handling")
    lines.append("  - 7.9 Security Testing")
    lines.append("  - 7.10 Test Results Summary")
    lines.append("- **Chapter 8: Results and Discussion**")
    lines.append("  - 8.1 Results")
    lines.append("  - 8.2 E-Governance Results")
    lines.append("  - 8.3 E-Commerce Results")
    lines.append("  - 8.4 Administrative Results")
    lines.append("  - 8.5 User Experience Observations")
    lines.append("  - 8.6 Discussion of Architectural Decisions")
    lines.append("  - 8.7 Advantages of the Platform")
    lines.append("  - 8.8 Challenges Encountered")
    lines.append("- **Chapter 9: Conclusion and Future Enhancements**")
    lines.append("  - 9.1 Conclusion")
    lines.append("  - 9.2 Future Enhancements")
    lines.append("- **References**")
    lines.append("- **Appendices (A to H) & Documentation Verification Summary**\n")
    lines.append("---\n")

    # List of Figures
    lines.append("## LIST OF FIGURES\n")
    for s in doc_chapters_6_9.CHAPTER_6['screens']:
        lines.append(f"- **{s['fig']}:** {s['name']} (`{s['route']}`)")
    lines.append("\n---\n")

    # List of Tables
    lines.append("## LIST OF TABLES\n")
    lines.append("- **Table 2.1:** Comparative Feature Matrix Between Traditional Municipalities, Generic E-Commerce, and SmartGov Market")
    lines.append("- **Table 4.1:** User Roles and RBAC Permission Scope Matrix")
    lines.append("- **Table 4.2:** Complete Database Entity Dictionary (25 Normalized Tables)")
    lines.append("- **Table 7.1:** Functional Test Execution Matrix (22 Test Cases)")
    lines.append("\n---\n")

    # List of Abbreviations
    lines.append("## LIST OF ABBREVIATIONS\n")
    lines.append("| Abbreviation | Full Expansion |")
    lines.append("| :--- | :--- |")
    for abbr, exp in doc_content.ABBREVIATIONS:
        lines.append(f"| **{abbr}** | {exp} |")
    lines.append("\n---\n")

    # Chapter 1
    lines.append(f"# {doc_chapters_1_3.CHAPTER_1['title']}\n")
    for sec in doc_chapters_1_3.CHAPTER_1['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")

    # Chapter 2
    lines.append(f"# {doc_chapters_1_3.CHAPTER_2['title']}\n")
    for sec in doc_chapters_1_3.CHAPTER_2['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")
        if sec['num'] == '2.6':
            lines.append("### Table 2.1: Comparison Between Existing and Proposed Systems\n")
            lines.append("| Feature / Metric | Traditional Municipal Office | Generic E-Commerce (e.g. Daraz) | Standalone E-Gov Portal | Proposed SmartGov Market |")
            lines.append("| :--- | :--- | :--- | :--- | :--- |")
            lines.append("| **Merchant Registration** | Physical Paper Forms & Queues | Online Self-Service (Tax ID Only) | Online Form (Isolated) | Multi-Step Digital KYC + GIS Coordinates |")
            lines.append("| **Document Verification** | Manual Paper Inspection | Internal Marketplace Audit | Manual Officer Review | Granular Digital Viewer with Officer Audit |")
            lines.append("| **Business Licensing** | Physical Paper Certificate | None Issued | PDF Certificate Download | Cryptographic License (LIC-YYYY-XXXXXX) + QR |")
            lines.append("| **Public License Verification** | Impossible in Real-Time | Not Applicable | Static Verification Database | Real-Time Public QR Verification (verify.php) |")
            lines.append("| **Marketplace Listing** | None (Physical Bazaar) | Open to Any Tax-Registered Seller | None (Services Only) | Restricted Exclusively to Verified Licensees |")
            lines.append("| **Ordering & Stock Management** | Manual Cash Transactions | Atomic Online Cart & Inventory | None | Atomic DB Transactions with Stock Protection |")
            lines.append("| **Payment Methods** | Cash Only | Card, Wallets, Cash on Delivery | Bank Deposit Slip Upload | Native COD + Simulated Digital Wallets |")
            lines.append("| **Civic Grievance Redressal** | Formal Paper Petition | Marketplace Seller Dispute Only | Text Form without Geospatial Pin | Integrated GIS Map + Photo Evidence Redressal |")
            lines.append("| **Verified Customer Reviews** | Word of Mouth | Open Reviews (Vulnerable to Spam) | None | Restricted Strictly to Delivered Order Buyers |")
            lines.append("| **Localization (i18n)** | Official Language Documents | Single / Generic Language | Usually Local Language Only | Real-Time Bilingual Toggle (English & Nepali) |")
            lines.append("| **Audit Trail & Governance** | Paper Archives (Subject to Loss) | Private Internal Database Logs | Basic Server Logs | Searchable Immutable Audit Log (audit_logs) |\n")

    # Chapter 3
    lines.append(f"# {doc_chapters_1_3.CHAPTER_3['title']}\n")
    for sec in doc_chapters_1_3.CHAPTER_3['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")

    # Chapter 4
    lines.append(f"# {doc_chapters_4_5.CHAPTER_4['title']}\n")
    for sec in doc_chapters_4_5.CHAPTER_4['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")
        if sec['num'] == '4.3':
            lines.append("### Table 4.1: User Roles and Permissions Matrix\n")
            lines.append("| Module / Permission | Public / Guest | Citizen / Customer | Local Vendor | Government Officer | System Administrator |")
            lines.append("| :--- | :---: | :---: | :---: | :---: | :---: |")
            lines.append("| **Browse Marketplace Catalog** | Full | Full | Full | Full | Full |")
            lines.append("| **Search & Filter Products** | Full | Full | Full | Full | Full |")
            lines.append("| **Public QR License Verification** | Full | Full | Full | Full | Full |")
            lines.append("| **User Account Registration** | Yes | Yes (Citizen) | Yes (Vendor) | Admin Created | Pre-Configured |")
            lines.append("| **Manage Shopping Cart** | No | Full | Full (Customer Mode)| No | Full (Testing) |")
            lines.append("| **Place Orders & Checkout** | No | Full | Full (Customer Mode)| No | Full (Testing) |")
            lines.append("| **Track Order Delivery Timeline** | No | Own Orders | Own Store Orders | No | All System Orders |")
            lines.append("| **Submit Verified Product Review** | No | Delivered Orders Only | No | No | Moderation Only |")
            lines.append("| **Lodge Public Grievance (GIS Map)** | No | Full | Full | Full | Full |")
            lines.append("| **Apply for Municipal Services** | No | Full | Full | No | Full |")
            lines.append("| **Upload Business Legal Documents**| No | No | Full (Own Business)| No | Admin Override |")
            lines.append("| **View & Print Digital License** | No | No | Full (Own License) | Full (All Vendors) | Full (All Vendors) |")
            lines.append("| **Request License Renewal** | No | No | Full (Annual) | No | Admin Override |")
            lines.append("| **Product CRUD & Stock Management**| No | No | Full (Verified Only)| No | Admin Moderation |")
            lines.append("| **Fulfill Customer Orders** | No | No | Own Items Only | No | Admin Override |")
            lines.append("| **Review Vendor Applications Queue**| No | No | No | Full | Full |")
            lines.append("| **Verify / Reject Vendor Files** | No | No | No | Full | Full |")
            lines.append("| **Approve & Issue Digital License**| No | No | No | Full | Full |")
            lines.append("| **Investigate & Resolve Grievances**| No | No | No | Assigned Complaints | All Complaints |")
            lines.append("| **Process Municipal Service Apps** | No | No | No | Assigned Department| All Departments |")
            lines.append("| **Real-Time Analytics (Chart.js)** | No | No | Sales Dashboard | Inspection Metrics | Executive Master Console |")
            lines.append("| **Manage Users & Role Assignments** | No | No | No | No | Full |")
            lines.append("| **Inspect Security Audit Trail** | No | No | No | No | Full |")
            lines.append("| **Export Analytical CSV Reports** | No | No | No | Officer Reports | Master CSV Exports |\n")

    # Chapter 5
    lines.append(f"# {doc_chapters_4_5.CHAPTER_5['title']}\n")
    for sec in doc_chapters_4_5.CHAPTER_5['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")

    # Chapter 6
    lines.append(f"# {doc_chapters_6_9.CHAPTER_6['title']}\n")
    lines.append(f"{doc_chapters_6_9.CHAPTER_6['intro']}\n")
    for s in doc_chapters_6_9.CHAPTER_6['screens']:
        lines.append(f"### {s['fig']}: {s['name']}")
        lines.append(f"- **System Route:** `{s['route']}`")
        lines.append(f"- **Permitted User Roles:** {s['access']}")
        lines.append(f"- **Functional Description:** {s['desc']}\n")
        lines.append(f"```text\n{s['placeholder']}\n```\n")

    # Chapter 7
    lines.append(f"# {doc_chapters_6_9.CHAPTER_7['title']}\n")
    for sec in doc_chapters_6_9.CHAPTER_7['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")
        if sec['num'] == '7.7':
            lines.append("### Table 7.1: Functional Test Execution Matrix\n")
            lines.append("| Test ID | Functional Module | Test Scenario | Input Data | Expected System Result | Actual Result Observed | Status |")
            lines.append("| :--- | :--- | :--- | :--- | :--- | :--- | :---: |")
            for tid, mod, scen, inp, exp, act, stat in doc_chapters_6_9.TEST_CASES:
                lines.append(f"| **{tid}** | {mod} | {scen} | {inp} | {exp} | {act} | **{stat}** |")
            lines.append("\n")

    # Chapter 8
    lines.append(f"# {doc_chapters_6_9.CHAPTER_8['title']}\n")
    for sec in doc_chapters_6_9.CHAPTER_8['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")

    # Chapter 9
    lines.append(f"# {doc_chapters_6_9.CHAPTER_9['title']}\n")
    for sec in doc_chapters_6_9.CHAPTER_9['sections']:
        lines.append(f"## {sec['num']} {sec['title']}\n")
        lines.append(f"{sec['content']}\n")

    # References
    lines.append("# REFERENCES\n")
    for idx, ref in enumerate(doc_chapters_6_9.REFERENCES, 1):
        lines.append(f"{idx}. {ref}")
    lines.append("\n---\n")

    # Appendices
    lines.append("# APPENDICES\n")
    for k, v in doc_appendices.APPENDICES.items():
        lines.append(f"## {v['title']}\n")
        lines.append(f"{v['content']}\n")

    # Verification Summary
    lines.append("# DOCUMENTATION VERIFICATION SUMMARY\n")
    vs = doc_appendices.VERIFICATION_SUMMARY
    lines.append(f"- **Number of Major Modules Documented:** {vs['modules_documented']}")
    lines.append(f"- **Number of Database Tables Documented:** {vs['database_tables']}")
    lines.append(f"- **Number of User Roles Identified:** {vs['user_roles']} (Admin, Officer, Vendor, Customer)")
    lines.append(f"- **Number of Architectural Diagrams Created:** {vs['diagrams_created']}")
    lines.append(f"- **Number of Test Cases Executed:** {vs['test_cases']} (20 PASS, 2 PARTIALLY PASS, 0 FAIL)\n")

    lines.append("### Confirmed Implemented Features (22 Features):")
    for f in vs['features_confirmed']:
        lines.append(f"1. [x] {f}")

    lines.append("\n### Partially Implemented Features (2 Features):")
    for f in vs['features_partially_implemented']:
        lines.append(f"1. [!] {f}")

    lines.append("\n### Features Identified as Future Enhancements (6 Features):")
    for f in vs['features_future_enhancements']:
        lines.append(f"1. [*] {f}")

    lines.append("\n### Important Technical Findings & Inspection Notes:")
    for f in vs['important_issues_discovered']:
        lines.append(f"- {f}")

    with open(MD_PATH, "w", encoding="utf-8") as f:
        f.write("\n".join(lines))
    print(f"Markdown saved successfully to {MD_PATH} (Length: {len(lines)} lines)")

# Run markdown build
build_markdown()
