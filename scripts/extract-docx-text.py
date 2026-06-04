#!/usr/bin/env python3
import docx
import json
import os
import re
import math

docx_path = os.path.join(os.path.dirname(__file__), '..', 'files', 'ແກ້ກັມ 2020.docx')
output_dir = os.path.join(os.path.dirname(__file__), '..', 'public', 'assets', 'kaekam-2020')

os.makedirs(output_dir, exist_ok=True)

doc = docx.Document(docx_path)

paragraphs = []
for p in doc.paragraphs:
    t = p.text.strip()
    if t:
        style = p.style.name if p.style else ''
        paragraphs.append({'text': t, 'style': style})

total_chars = sum(len(p['text']) for p in paragraphs)
print(f"Total paragraphs: {len(paragraphs)}")
print(f"Total characters: {total_chars}")

target_chars_per_page = 2000
total_pages = max(1, math.ceil(total_chars / target_chars_per_page))

pages_data = []
text_index = []

chars_per_page = math.ceil(total_chars / total_pages)

current_text = []
current_count = 0
page_num = 1

for p in paragraphs:
    text_len = len(p['text']) + 1
    if current_count + text_len > chars_per_page * 1.5 and current_count > 0:
        page_text = '\n'.join(current_text)
        pages_data.append({
            "page": page_num,
            "text": page_text,
            "words": []
        })
        first_line = page_text[:100].replace('\n', ' ').strip()
        text_index.append({
            "page": page_num,
            "preview": first_line
        })
        page_num += 1
        current_text = [p['text']]
        current_count = text_len
    else:
        current_text.append(p['text'])
        current_count += text_len

if current_text:
    page_text = '\n'.join(current_text)
    pages_data.append({
        "page": page_num,
        "text": page_text,
        "words": []
    })
    first_line = page_text[:100].replace('\n', ' ').strip()
    text_index.append({
        "page": page_num,
        "preview": first_line
    })

with open(os.path.join(output_dir, 'pages.json'), 'w', encoding='utf-8') as f:
    json.dump(pages_data, f, ensure_ascii=False, indent=None)

with open(os.path.join(output_dir, 'index.json'), 'w', encoding='utf-8') as f:
    json.dump(text_index, f, ensure_ascii=False, indent=None)

book_info = {
    "title": "ແກ້ກັມ 2020",
    "year": 2020,
    "totalPages": len(pages_data),
    "type": "docx"
}

with open(os.path.join(output_dir, 'book.json'), 'w', encoding='utf-8') as f:
    json.dump(book_info, f, ensure_ascii=False, indent=2)

print(f"Done! {len(pages_data)} pages extracted from DOCX.")
 