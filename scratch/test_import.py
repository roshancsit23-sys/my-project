# -*- coding: utf-8 -*-
"""
SmartGov Market - Master Document Generator
Builds .md, .docx, and .pdf files.
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

print("All content modules successfully imported!")
