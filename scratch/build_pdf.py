# -*- coding: utf-8 -*-
"""
SmartGov Market - Academic PDF Generator
Builds SMARTGOV_MARKET_PROJECT_DOCUMENTATION.pdf using ReportLab Platypus.
"""

import os
import sys
import hashlib

# Fix for Python 3.8 Windows openssl_md5 usedforsecurity issue
_orig_md5 = hashlib.md5
hashlib.md5 = lambda *args, **kwargs: _orig_md5(*args, **{k: v for k, v in kwargs.items() if k != 'usedforsecurity'})

# Add scratch to sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

import doc_content
import doc_chapters_1_3
import doc_chapters_4_5
import doc_chapters_6_9
import doc_appendices

from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

ROOT_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PDF_PATH = os.path.join(ROOT_DIR, "SMARTGOV_MARKET_PROJECT_DOCUMENTATION.pdf")

# Custom Canvas for Two-Pass Dynamic Page Numbering ("Page X of Y") & Running Header
class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_elements(num_pages)
            super().showPage()
        super().save()

    def draw_page_elements(self, page_count):
        if self._pageNumber == 1:
            # Suppress headers/footers on cover page
            return

        self.saveState()
        self.setFont("Helvetica-Oblique", 8)
        self.setFillColor(colors.HexColor("#718096"))

        # Running Header (top margin)
        self.drawString(54, 842 - 36, "SmartGov Market - Integrated E-Governance and E-Commerce Platform")
        self.setStrokeColor(colors.HexColor("#E2E8F0"))
        self.setLineWidth(0.5)
        self.line(54, 842 - 42, 595 - 54, 842 - 42)

        # Running Footer (bottom margin)
        self.drawString(54, 36, "Project Documentation / Software Project Report")
        page_str = f"Page {self._pageNumber} of {page_count}"
        self.drawRightString(595 - 54, 36, page_str)
        self.line(54, 46, 595 - 54, 46)

        self.restoreState()

def build_pdf():
    print("Initializing PDF generation with ReportLab Platypus...")
    doc = SimpleDocTemplate(
        PDF_PATH,
        pagesize=A4,
        leftMargin=54,
        rightMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()

    # Custom Typography Styles
    style_cover_title = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=24,
        leading=30,
        textColor=colors.HexColor("#1A2B4C"),
        alignment=1, # Center
        spaceAfter=8
    )

    style_cover_sub = ParagraphStyle(
        'CoverSub',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=13,
        leading=18,
        textColor=colors.HexColor("#2B6CB0"),
        alignment=1,
        spaceAfter=15
    )

    style_ch_title = ParagraphStyle(
        'ChapterTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=16,
        leading=22,
        textColor=colors.HexColor("#1A2B4C"),
        spaceBefore=12,
        spaceAfter=14,
        keepWithNext=True
    )

    style_sec_title = ParagraphStyle(
        'SecTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=16,
        textColor=colors.HexColor("#2B6CB0"),
        spaceBefore=10,
        spaceAfter=6,
        keepWithNext=True
    )

    style_body = ParagraphStyle(
        'AcademicBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14,
        textColor=colors.HexColor("#2D3748"),
        spaceAfter=6,
        alignment=4 # Justify
    )

    style_table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white
    )

    style_table_cell = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=colors.HexColor("#2D3748")
    )

    style_callout = ParagraphStyle(
        'CalloutText',
        parent=styles['Normal'],
        fontName='Helvetica-Oblique',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor("#2D3748")
    )

    story = []

    # ==========================================
    # COVER PAGE
    # ==========================================
    story.append(Spacer(1, 40))
    story.append(Paragraph(doc_content.METADATA['title'], style_cover_title))
    story.append(Paragraph(doc_content.METADATA['subtitle'], style_cover_sub))
    story.append(Paragraph(f"<b>{doc_content.METADATA['doc_type']}</b>", ParagraphStyle('DocType', parent=style_cover_sub, fontSize=11, textColor=colors.HexColor("#718096"), spaceAfter=30)))

    # Submission Box Table
    sub_box = [
        [Paragraph("<b>ACADEMIC PROJECT SUBMISSION RECORD</b>", ParagraphStyle('H1', parent=style_cover_title, fontSize=10, textColor=colors.HexColor("#1A2B4C"), alignment=1))],
        [Paragraph("A Comprehensive Final Year Software Engineering Project Report Submitted in Partial Fulfillment of the Degree of Bachelor of Science in Computer Science & Information Technology (B.Sc. CSIT) / Bachelor of Computer Engineering.", style_callout)]
    ]
    t_sub_box = Table(sub_box, colWidths=[480])
    t_sub_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#F8FAFC")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#CBD5E0")),
        ('LINELEFT', (0,0), (0,-1), 3, colors.HexColor("#1A2B4C")),
        ('TOPPADDING', (0,0), (-1,-1), 10),
        ('BOTTOMPADDING', (0,0), (-1,-1), 10),
        ('LEFTPADDING', (0,0), (-1,-1), 15),
        ('RIGHTPADDING', (0,0), (-1,-1), 15),
    ]))
    story.append(t_sub_box)
    story.append(Spacer(1, 50))

    # Metadata Table
    meta_rows = [
        [Paragraph("<b>Candidate / Student Name:</b>", style_table_cell), Paragraph(doc_content.METADATA['student_name'], style_table_cell)],
        [Paragraph("<b>Roll / Registration No.:</b>", style_table_cell), Paragraph(doc_content.METADATA['roll_no'], style_table_cell)],
        [Paragraph("<b>Academic Program / Subject:</b>", style_table_cell), Paragraph(doc_content.METADATA['course'], style_table_cell)],
        [Paragraph("<b>Department:</b>", style_table_cell), Paragraph(doc_content.METADATA['department'], style_table_cell)],
        [Paragraph("<b>College / University:</b>", style_table_cell), Paragraph(doc_content.METADATA['institution'], style_table_cell)],
        [Paragraph("<b>Project Supervisor:</b>", style_table_cell), Paragraph(doc_content.METADATA['supervisor'], style_table_cell)],
        [Paragraph("<b>Academic Session / Year:</b>", style_table_cell), Paragraph(doc_content.METADATA['academic_year'], style_table_cell)]
    ]
    t_meta = Table(meta_rows, colWidths=[180, 300])
    t_meta.setStyle(TableStyle([
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ('LINEBELOW', (0,0), (-1,-1), 0.5, colors.HexColor("#EDF2F7"))
    ]))
    story.append(t_meta)
    story.append(PageBreak())

    # ==========================================
    # PRELIMINARY PAGES
    # ==========================================
    # Certificate
    story.append(Paragraph("CERTIFICATE OF APPROVAL", style_ch_title))
    for p in doc_content.PRELIMINARY['certificate'].split("\n\n"):
        story.append(Paragraph(p.replace("\n", "<br/>"), style_body))
    story.append(PageBreak())

    # Declaration
    story.append(Paragraph("CANDIDATE DECLARATION", style_ch_title))
    for p in doc_content.PRELIMINARY['declaration'].split("\n\n"):
        story.append(Paragraph(p.replace("\n", "<br/>"), style_body))
    story.append(PageBreak())

    # Acknowledgement
    story.append(Paragraph("ACKNOWLEDGEMENT", style_ch_title))
    for p in doc_content.PRELIMINARY['acknowledgement'].split("\n\n"):
        story.append(Paragraph(p.replace("\n", "<br/>"), style_body))
    story.append(PageBreak())

    # Abstract
    story.append(Paragraph("ABSTRACT", style_ch_title))
    for p in doc_content.PRELIMINARY['abstract'].split("\n\n"):
        story.append(Paragraph(p, style_body))
    story.append(PageBreak())

    # List of Abbreviations
    story.append(Paragraph("LIST OF ABBREVIATIONS", style_ch_title))
    abbr_rows = [[Paragraph("<b>Acronym</b>", style_table_header), Paragraph("<b>Full Technical Term</b>", style_table_header)]]
    for abbr, desc in doc_content.ABBREVIATIONS:
        abbr_rows.append([Paragraph(f"<b>{abbr}</b>", style_table_cell), Paragraph(desc, style_table_cell)])
    t_abbr = Table(abbr_rows, colWidths=[110, 370])
    t_abbr.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1A2B4C")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#F8FAFC")]),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#CBD5E0")),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_abbr)

    # Helper function to append chapters
    def append_pdf_chapter(chapter_dict):
        story.append(PageBreak())
        story.append(Paragraph(chapter_dict['title'], style_ch_title))
        story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#2B6CB0"), spaceBefore=2, spaceAfter=8))
        for sec in chapter_dict.get('sections', []):
            story.append(Paragraph(f"{sec['num']} {sec['title']}", style_sec_title))
            for p in sec['content'].strip().split("\n\n"):
                story.append(Paragraph(p.replace("\n", "<br/>"), style_body))

    # Chapter 1
    append_pdf_chapter(doc_chapters_1_3.CHAPTER_1)

    # Chapter 2
    append_pdf_chapter(doc_chapters_1_3.CHAPTER_2)

    # Insert Table 2.1
    story.append(Spacer(1, 8))
    story.append(Paragraph("<b>Table 2.1: Comparison Between Existing and Proposed Systems</b>", style_sec_title))
    comp_headers = [
        Paragraph("<b>Metric</b>", style_table_header),
        Paragraph("<b>Traditional Office</b>", style_table_header),
        Paragraph("<b>Generic E-Com</b>", style_table_header),
        Paragraph("<b>SmartGov Market</b>", style_table_header)
    ]
    comp_rows = [comp_headers]
    c_raw = [
        ("Merchant Registration", "Manual Paper Forms & Queues", "Self-Service (Tax ID Only)", "Multi-Step KYC + GIS Coordinates"),
        ("Document Verification", "Manual Physical Auditing", "Internal Marketplace Review", "Granular Digital Viewer & Checklist"),
        ("Business Licensing", "Paper Certificate (Forgible)", "None Issued", "Cryptographic Code (LIC-YYYY-XXXXXX)"),
        ("Public Verification", "Impossible in Real-Time", "Not Applicable", "Instant Mobile QR Portal (verify.php)"),
        ("Marketplace Listing", "None (Physical Stall)", "Open to Any Seller", "Strictly Restricted to Verified Licensees"),
        ("Ordering & Inventory", "Manual Cash Flow", "Atomic Cart & Decrement", "Atomic DB Transactions + Stock Protection"),
        ("Payment Processing", "Cash Only", "Cards, Wallets, COD", "Native COD + Simulated Wallet Gateways"),
        ("Civic Grievance", "Formal Paper Petition", "Seller Dispute Ticket", "Interactive Leaflet GIS Map + Photo Evidence"),
        ("Review Authenticity", "Word of Mouth", "Open (Vulnerable to Spam)", "Restricted to Verified Delivered Buyers"),
        ("Audit Trail", "Physical Archive Binders", "Private Proprietary Logs", "Searchable Immutable Audit Trail")
    ]
    for r in c_raw:
        comp_rows.append([
            Paragraph(f"<b>{r[0]}</b>", style_table_cell),
            Paragraph(r[1], style_table_cell),
            Paragraph(r[2], style_table_cell),
            Paragraph(r[3], style_table_cell)
        ])
    t_comp = Table(comp_rows, colWidths=[95, 120, 115, 150])
    t_comp.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1A2B4C")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#F8FAFC")]),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#CBD5E0")),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(t_comp)

    # Chapter 3
    append_pdf_chapter(doc_chapters_1_3.CHAPTER_3)

    # Chapter 4
    append_pdf_chapter(doc_chapters_4_5.CHAPTER_4)

    # Insert Table 4.1 in Chapter 4 (Roles)
    story.append(Spacer(1, 8))
    story.append(Paragraph("<b>Table 4.1: Role-Based Access Control (RBAC) Permissions Matrix</b>", style_sec_title))
    role_headers = [
        Paragraph("<b>Capability</b>", style_table_header),
        Paragraph("<b>Guest</b>", style_table_header),
        Paragraph("<b>Citizen</b>", style_table_header),
        Paragraph("<b>Vendor</b>", style_table_header),
        Paragraph("<b>Officer</b>", style_table_header),
        Paragraph("<b>Admin</b>", style_table_header)
    ]
    r_rows = [role_headers]
    r_data = [
        ("Browse Marketplace Catalog", "Full", "Full", "Full", "Full", "Full"),
        ("Public QR Verification", "Full", "Full", "Full", "Full", "Full"),
        ("Account Registration", "Yes", "Self", "Self", "Admin", "Master"),
        ("Manage Shopping Cart", "No", "Full", "Customer Mode", "No", "Testing"),
        ("Place Orders & Checkout", "No", "Full", "Customer Mode", "No", "Testing"),
        ("Track Order Delivery", "No", "Own", "Store Items", "No", "All"),
        ("Verified Product Review", "No", "Delivered", "No", "No", "Audit"),
        ("Lodge Public Grievance", "No", "GIS Map", "GIS Map", "GIS Map", "GIS Map"),
        ("Municipal Service Apps", "No", "Full", "Full", "No", "All"),
        ("Upload Legal Documents", "No", "No", "Own Store", "No", "Override"),
        ("Print Digital License", "No", "No", "Own Lic", "All", "All"),
        ("Product Catalog CRUD", "No", "No", "Verified", "No", "Moderate"),
        ("Fulfill Customer Orders", "No", "No", "Own Items", "No", "Override"),
        ("Inspect Vendor Queue", "No", "No", "No", "Full", "Full"),
        ("Verify / Reject Files", "No", "No", "No", "Full", "Full"),
        ("Approve & Issue License", "No", "No", "No", "Full", "Full"),
        ("Resolve Civic Grievances", "No", "No", "No", "Assigned", "All"),
        ("Real-Time Chart.js", "No", "No", "Sales", "KPI", "Master"),
        ("Audit Logs & CSV Export", "No", "No", "No", "CSV", "Full")
    ]
    for row in r_data:
        r_rows.append([
            Paragraph(f"<b>{row[0]}</b>", style_table_cell),
            Paragraph(row[1], style_table_cell),
            Paragraph(row[2], style_table_cell),
            Paragraph(row[3], style_table_cell),
            Paragraph(row[4], style_table_cell),
            Paragraph(row[5], style_table_cell)
        ])
    t_roles = Table(r_rows, colWidths=[180, 50, 60, 70, 60, 60])
    t_roles.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1A2B4C")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#F8FAFC")]),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#CBD5E0")),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(t_roles)

    # Chapter 5
    append_pdf_chapter(doc_chapters_4_5.CHAPTER_5)

    # Chapter 6 (Screenshots)
    story.append(PageBreak())
    story.append(Paragraph(doc_chapters_6_9.CHAPTER_6['title'], style_ch_title))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#2B6CB0"), spaceBefore=2, spaceAfter=8))
    story.append(Paragraph(doc_chapters_6_9.CHAPTER_6['intro'], style_body))

    for s in doc_chapters_6_9.CHAPTER_6['screens']:
        box_data = [
            [Paragraph(f"<b>📸 {s['placeholder']}</b>", ParagraphStyle('B1', parent=style_table_cell, fontName='Helvetica-Bold', textColor=colors.HexColor("#2B6CB0"), alignment=1))],
            [Paragraph(f"<b>Route:</b> {s['route']}  |  <b>Authorized Roles:</b> {s['access']}", ParagraphStyle('B2', parent=style_callout, alignment=1))]
        ]
        t_box = Table(box_data, colWidths=[480])
        t_box.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#FAFAFA")),
            ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#CBD5E0")),
            ('TOPPADDING', (0,0), (-1,-1), 8),
            ('BOTTOMPADDING', (0,0), (-1,-1), 8),
        ]))
        story.append(Spacer(1, 4))
        story.append(t_box)
        story.append(Paragraph(f"<b>{s['fig']}: {s['name']}</b>", ParagraphStyle('Cap', parent=style_sec_title, alignment=1, fontSize=9.5, spaceBefore=4, spaceAfter=2)))
        story.append(Paragraph(f"<b>Description:</b> {s['desc']}", style_body))
        story.append(Spacer(1, 6))

    # Chapter 7
    append_pdf_chapter(doc_chapters_6_9.CHAPTER_7)

    # Insert Table 7.1 (Test Cases)
    story.append(Spacer(1, 8))
    story.append(Paragraph("<b>Table 7.1: Functional Test Execution Matrix & Acceptance Results</b>", style_sec_title))
    tc_headers = [
        Paragraph("<b>ID</b>", style_table_header),
        Paragraph("<b>Module</b>", style_table_header),
        Paragraph("<b>Scenario & Input</b>", style_table_header),
        Paragraph("<b>Expected & Actual Result</b>", style_table_header),
        Paragraph("<b>Status</b>", style_table_header)
    ]
    tc_rows = [tc_headers]
    for tid, mod, scen, inp, exp, act, stat in doc_chapters_6_9.TEST_CASES:
        stat_color = "#16A34A" if stat == "PASS" else "#D97706"
        tc_rows.append([
            Paragraph(f"<b>{tid}</b>", style_table_cell),
            Paragraph(mod, style_table_cell),
            Paragraph(f"<b>Scenario:</b> {scen}<br/><b>Input:</b> {inp}", style_table_cell),
            Paragraph(f"<b>Exp:</b> {exp}<br/><b>Act:</b> {act}", style_table_cell),
            Paragraph(f"<font color='{stat_color}'><b>{stat}</b></font>", style_table_cell)
        ])
    t_tc = Table(tc_rows, colWidths=[40, 75, 175, 150, 40])
    t_tc.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1A2B4C")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#F8FAFC")]),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#CBD5E0")),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 3),
        ('RIGHTPADDING', (0,0), (-1,-1), 3),
    ]))
    story.append(t_tc)

    # Chapter 8
    append_pdf_chapter(doc_chapters_6_9.CHAPTER_8)

    # Chapter 9
    append_pdf_chapter(doc_chapters_6_9.CHAPTER_9)

    # References
    story.append(PageBreak())
    story.append(Paragraph("REFERENCES", style_ch_title))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#2B6CB0"), spaceBefore=2, spaceAfter=8))
    for idx, ref in enumerate(doc_chapters_6_9.REFERENCES, 1):
        story.append(Paragraph(f"[{idx}] {ref}", style_body))

    # Appendices
    for k, v in doc_appendices.APPENDICES.items():
        story.append(PageBreak())
        story.append(Paragraph(v['title'], style_ch_title))
        story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#2B6CB0"), spaceBefore=2, spaceAfter=8))
        for p in v['content'].strip().split("\n\n"):
            story.append(Paragraph(p.replace("\n", "<br/>"), style_body))

    # Verification Summary
    story.append(PageBreak())
    story.append(Paragraph("DOCUMENTATION VERIFICATION SUMMARY", style_ch_title))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#C53030"), spaceBefore=2, spaceAfter=8))

    vs = doc_appendices.VERIFICATION_SUMMARY
    sum_headers = [
        Paragraph("<b>Audit Metric</b>", style_table_header),
        Paragraph("<b>Quantity Observed</b>", style_table_header),
        Paragraph("<b>Status & Findings</b>", style_table_header)
    ]
    sum_rows = [
        sum_headers,
        [Paragraph("<b>Major Modules Documented</b>", style_table_cell), Paragraph(str(vs['modules_documented']), style_table_cell), Paragraph("14 Integrated Civic & E-Commerce Subsystems", style_table_cell)],
        [Paragraph("<b>Database Schema Tables</b>", style_table_cell), Paragraph(str(vs['database_tables']), style_table_cell), Paragraph("25 Normalized Tables (InnoDB, utf8mb4)", style_table_cell)],
        [Paragraph("<b>User Roles Identified</b>", style_table_cell), Paragraph(str(vs['user_roles']), style_table_cell), Paragraph("Admin, Officer, Vendor, Citizen/Customer", style_table_cell)],
        [Paragraph("<b>Architectural Diagrams</b>", style_table_cell), Paragraph(str(vs['diagrams_created']), style_table_cell), Paragraph("Architecture, RBAC, Use Case, DFDs, ERD, Sequences", style_table_cell)],
        [Paragraph("<b>Functional Test Cases</b>", style_table_cell), Paragraph(str(vs['test_cases']), style_table_cell), Paragraph("20 Confirmed PASS, 2 PARTIALLY PASS, 0 FAIL", style_table_cell)]
    ]
    t_sum = Table(sum_rows, colWidths=[150, 100, 230])
    t_sum.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1A2B4C")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#F8FAFC")]),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#CBD5E0")),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(t_sum)
    story.append(Spacer(1, 10))

    story.append(Paragraph("<b>Confirmed Implemented Features (22 Features):</b>", style_sec_title))
    for f in vs['features_confirmed']:
        story.append(Paragraph(f"• {f}", style_body))

    story.append(Spacer(1, 6))
    story.append(Paragraph("<b>Partially Implemented Features:</b>", style_sec_title))
    for f in vs['features_partially_implemented']:
        story.append(Paragraph(f"• {f}", style_body))

    story.append(Spacer(1, 6))
    story.append(Paragraph("<b>Future Enhancements:</b>", style_sec_title))
    for f in vs['features_future_enhancements']:
        story.append(Paragraph(f"• {f}", style_body))

    story.append(Spacer(1, 6))
    story.append(Paragraph("<b>Important Technical Findings:</b>", style_sec_title))
    for f in vs['important_issues_discovered']:
        story.append(Paragraph(f"• {f}", style_body))

    # Build PDF with custom NumberedCanvas
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF Document successfully created at: {PDF_PATH}")

if __name__ == "__main__":
    build_pdf()
