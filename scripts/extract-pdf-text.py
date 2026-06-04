#!/usr/bin/env python3
import fitz
import json
import os
import re

pdf_path = os.path.join(os.path.dirname(__file__), '..', 'files', 'ປຶ້ມສາທະຍາຍທັມ.pdf')
output_dir = os.path.join(os.path.dirname(__file__), '..', 'public', 'assets', 'sathaya-tham-2021')

os.makedirs(output_dir, exist_ok=True)

doc = fitz.open(pdf_path)
total_pages = doc.page_count

pages_data = []
text_index = []

def fix_lao_text(text):
    text = re.sub(r'(?<=[\u0E81-\u0EAE]) (?=[\u0EB9\u0EB8])', '', text)
    text = re.sub(r'(?<=[\u0E81-\u0EAE]) (?=[\u0EC8\u0EC9])', '\u0EB9', text)
    text = re.sub(r'\u0EAA \u0E95', '\u0EAA\u0EB9\u0E95', text)
    text = re.sub(r'\u0E9E \u0EA1', '\u0E9E\u0EB9\u0EA1', text)
    text = text.replace('\u0E84\u0EB2\u0E99\u0EB2', '\u0E84\u0EB3\u0E99\u0EB3')
    return text

import re

lao_digit = re.compile(r'^[\u0E50-\u0E590-9.,/]+$')

def is_multicolumn(words):
    left_words = [w for w in words if w[0] < 350]
    right_words = [w for w in words if w[0] > 450]
    if len(right_words) < 5:
        return False
    left_y = set(round(w[1]) for w in left_words)
    right_y = set(round(w[1]) for w in right_words)
    overlap = left_y & right_y
    if len(overlap) < 5:
        return False
    digit_count = sum(1 for w in right_words if lao_digit.match(w[4].strip()))
    return digit_count / len(right_words) >= 0.8

def build_text_from_words(words):
    rows = {}
    for w in words:
        y = round(w[1])
        if y not in rows:
            rows[y] = []
        rows[y].append((w[0], w[4]))
    lines = []
    for y in sorted(rows.keys()):
        row = sorted(rows[y], key=lambda t: t[0])
        lines.append(' '.join(t[1] for t in row))
    return '\n'.join(lines)

for i in range(total_pages):
    page = doc[i]
    words = page.get_text("words")
    
    if is_multicolumn(words):
        text = fix_lao_text(build_text_from_words(words).strip())
    else:
        text = fix_lao_text(page.get_text("text").strip())
    
    word_positions = []
    for w in words:
        word_positions.append({
            "w": w[4],
            "x0": round(w[0], 1),
            "y0": round(w[1], 1),
            "x1": round(w[2], 1),
            "y1": round(w[3], 1)
        })
    
    page_data = {
        "page": i + 1,
        "text": text,
        "words": word_positions
    }
    pages_data.append(page_data)
    
    first_line = text[:100].replace('\n', ' ').strip()
    text_index.append({
        "page": i + 1,
        "preview": first_line
    })

doc.close()

with open(os.path.join(output_dir, 'pages.json'), 'w', encoding='utf-8') as f:
    json.dump(pages_data, f, ensure_ascii=False, indent=None)

with open(os.path.join(output_dir, 'index.json'), 'w', encoding='utf-8') as f:
    json.dump(text_index, f, ensure_ascii=False, indent=None)

book_info = {
    "title": "ປຶ້ມຄູ່ມືສາທະຍາຍທັມ",
    "year": 2021,
    "totalPages": total_pages,
    "pdfFile": "/assets/sathaya-tham-2021/ປຶ້ມສາທະຍາຍທັມ-2021.pdf"
}

with open(os.path.join(output_dir, 'book.json'), 'w', encoding='utf-8') as f:
    json.dump(book_info, f, ensure_ascii=False, indent=2)

print(f"Done! {total_pages} pages extracted.")
