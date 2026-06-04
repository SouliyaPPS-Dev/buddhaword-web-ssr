#!/usr/bin/env python3
import os, sys, json, math, re, tempfile, subprocess, shutil

def clean_text(text):
    text = re.sub(r'[\u200b\u200c\u200d\u200e\u200f\u2060\u2061\u2062\u2063\u2064\ufeff\u00ad]', '', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def slugify(name):
    name = re.sub(r'\.[^.]+$', '', name)
    name = re.sub(r'[\s_()\[\]{}]+', '-', name)
    name = re.sub(r'[^a-zA-Z0-9\u0E80-\u0EFF-]', '', name)
    name = re.sub(r'-+', '-', name)
    name = name.strip('-').lower()[:60]
    return name or 'book'

def fix_lao_text(text):
    if not text:
        return text

    # 1. Remove zero-width and invisible chars
    text = re.sub(r'[\u200b\u200c\u200d\u200e\u200f\u2060\u2061\u2062\u2063\u2064\ufeff\u00ad]', '', text)

    # 2. Remove spaces between Lao syllable components
    C = '[\u0E81-\u0EAE\u0EB0-\u0EBE]'
    V_upper = '[\u0EB4-\u0EB9\u0EBC\u0EBD]'
    V_leading = '[\u0EC0-\u0EC4]'
    TONE = '[\u0EC8-\u0ECB]'
    TAIL = '[\u0E87\u0E94\u0E99\u0EA1\u0E81\u0E8D\u0EA7\u0EA3\u0EA5]'

    # Leading vowel + space + consonant/vowel → rejoin
    text = re.sub(r'(%s) +(?=%s|%s)' % (V_leading, C, V_leading), r'\1', text)
    # Consonant + space + upper vowel → rejoin
    text = re.sub(r'(?<=%s) +(?=%s)' % (C, V_upper), '', text)
    # Consonant + space + tone mark → rejoin
    text = re.sub(r'(?<=%s) +(?=%s)' % (C, TONE), '', text)
    # Upper vowel + space + consonant/tail → rejoin
    text = re.sub(r'(?<=%s) +(?=%s|%s)' % (V_upper, C, TAIL), '', text)
    # Consonant + space + ຣ ລ ວ (common tail consonants) → rejoin
    text = re.sub(r'(?<=%s) +(?=[\u0EA3\u0EA5\u0EA7])' % C, '', text)
    # Consonant + space + ອ (vowel role) → rejoin
    text = re.sub(r'(?<=[\u0E81-\u0EBD]) +(?=\u0EAD)', '', text)
    # ອ (vowel) + space + common final consonant → rejoin
    text = re.sub(r'(?<=\u0EAD) +(?=[\u0E99\u0EA1\u0E87\u0E94\u0E9A\u0E81\u0EA7\u0E8D])', '', text)

    # 3. Fix common split Lao word patterns
    pairs = [
        ('\u0EAA \u0E95', '\u0EAA\u0EB9\u0E95'),
        ('\u0E9E \u0EA1', '\u0E9E\u0EB9\u0EA1'),
        ('\u0EAA \u0E96', '\u0EAA\u0EB0\u0E96'),
        ('\u0E9B \u0EBE', '\u0E9B\u0EBE'),
        ('\u0E88 \u0EB0', '\u0E88\u0EB0'),
        ('\u0E97 \u0EB5\u0E99', '\u0E97\u0EB5\u0EC8\u0E99'),
    ]
    for old, new in pairs:
        text = text.replace(old, new)

    # 4. Fix specific word: ຄານາ → ຄຳນຳ
    text = text.replace('\u0E84\u0EB2\u0E99\u0EB2', '\u0E84\u0EB3\u0E99\u0EB3')

    # 5. Re-attach detached vowel ື and ຸ (common PDF split)
    text = re.sub(r'(?<=%s) (?=\u0EB7)' % C, '', text)
    text = re.sub(r'(?<=%s) (?=\u0EB9)' % C, '', text)

    # 6. Flexible spacing: ensure space after sentence punctuation
    text = re.sub(r'([.!?]) +', r'\1 ', text)

    # 7. Normalize whitespace
    text = re.sub(r'\s+', ' ', text)
    return text.strip()


def validate_lao_text(text):
    if not text:
        return {"has_lao": False, "warnings": ["No text extracted"]}
    lao_chars = sum(1 for c in text if '\u0E80' <= c <= '\u0EFF')
    total_chars = sum(1 for c in text if not c.isspace())
    if total_chars == 0:
        return {"has_lao": False, "warnings": ["No extractable characters"]}
    lao_ratio = lao_chars / total_chars
    warnings = []
    if lao_ratio < 0.3 and lao_chars > 0:
        warnings.append("Low Lao character ratio ({:.0f}%)".format(lao_ratio * 100))
    if lao_chars == 0 and total_chars > 10:
        warnings.append("No Lao characters detected in text")
    return {"has_lao": lao_chars > 0, "lao_ratio": round(lao_ratio, 2), "warnings": warnings}


def convert_pdf(file_path, out_dir, title):
    import fitz
    os.makedirs(out_dir, exist_ok=True)
    doc = fitz.open(file_path)
    total_pages = doc.page_count
    re_lao_digit = re.compile(r'^[\u0E50-\u0E590-9.,/]+$')

    def is_multicolumn(words):
        left_words = [w for w in words if w[0] < 350]
        right_words = [w for w in words if w[0] > 450]
        if len(right_words) < 5: return False
        left_y = set(round(w[1]) for w in left_words)
        right_y = set(round(w[1]) for w in right_words)
        overlap = left_y & right_y
        if len(overlap) < 5: return False
        digit_count = sum(1 for w in right_words if re_lao_digit.match(w[4].strip()))
        return digit_count / len(right_words) >= 0.8

    def build_text_from_words(words):
        rows = {}
        for w in words:
            y = round(w[1])
            rows.setdefault(y, []).append((w[0], w[4]))
        lines = []
        for y in sorted(rows.keys()):
            row = sorted(rows[y], key=lambda t: t[0])
            lines.append(' '.join(t[1] for t in row))
        return '\n'.join(lines)

    pages_data = []
    text_index = []
    all_warnings = set()
    total_lao_chars = 0
    total_extracted = 0
    for i in range(total_pages):
        page = doc[i]
        words = page.get_text("words")
        if is_multicolumn(words):
            text = clean_text(fix_lao_text(build_text_from_words(words).strip()))
        else:
            text = clean_text(fix_lao_text(page.get_text("text").strip()))
        word_positions = [{"w": w[4], "x0": round(w[0], 1), "y0": round(w[1], 1), "x1": round(w[2], 1), "y1": round(w[3], 1)} for w in words]
        pages_data.append({"page": i + 1, "text": text, "words": word_positions})
        first_line = text[:100].replace('\n', ' ').strip()
        text_index.append({"page": i + 1, "preview": first_line})
        quality = validate_lao_text(text)
        all_warnings.update(quality.get("warnings", []))
        total_lao_chars += sum(1 for c in text if '\u0E80' <= c <= '\u0EFF')
        total_extracted += sum(1 for c in text if not c.isspace())

    with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
        json.dump(pages_data, f, ensure_ascii=False, indent=None)
    with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
        json.dump(text_index, f, ensure_ascii=False, indent=None)

    # Generate cover from page 1
    mat_d = fitz.Matrix(150/72, 150/72)
    pix = doc[0].get_pixmap(matrix=mat_d)
    pix.save(os.path.join(out_dir, 'cover.png'))

    book_info = {"title": title, "year": 2026, "totalPages": total_pages, "type": "pdf"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)

    doc.close()
    return total_pages, list(all_warnings)

def convert_docx(file_path, out_dir, title):
    import docx
    os.makedirs(out_dir, exist_ok=True)
    doc = docx.Document(file_path)
    paragraphs = []
    for p in doc.paragraphs:
        t = clean_text(p.text)
        if t:
            paragraphs.append(t)

    total_chars = sum(len(p) for p in paragraphs)
    target_chars_per_page = 2000
    total_pages = max(1, math.ceil(total_chars / target_chars_per_page))
    chars_per_page = math.ceil(total_chars / total_pages)

    pages_data = []
    text_index = []
    current_text = []
    current_count = 0
    page_num = 1

    for text in paragraphs:
        text_len = len(text) + 1
        if current_count + text_len > chars_per_page * 1.5 and current_count > 0:
            page_text = clean_text('\n'.join(current_text))
            pages_data.append({"page": page_num, "text": page_text, "words": []})
            first_line = page_text[:100].replace('\n', ' ').strip()
            text_index.append({"page": page_num, "preview": first_line})
            page_num += 1
            current_text = [text]
            current_count = text_len
        else:
            current_text.append(text)
            current_count += text_len

    if current_text:
        page_text = clean_text('\n'.join(current_text))
        pages_data.append({"page": page_num, "text": page_text, "words": []})
        first_line = page_text[:100].replace('\n', ' ').strip()
        text_index.append({"page": page_num, "preview": first_line})

    with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
        json.dump(pages_data, f, ensure_ascii=False, indent=None)
    with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
        json.dump(text_index, f, ensure_ascii=False, indent=None)

    book_info = {"title": title, "year": 2026, "totalPages": len(pages_data), "type": "docx"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)

    return len(pages_data)

def convert_doc(file_path, out_dir, title):
    os.makedirs(out_dir, exist_ok=True)
    tmp_txt = os.path.join(tempfile.mkdtemp(), 'output.txt')
    full_text = ""
    try:
        subprocess.run(['textutil', '-convert', 'txt', '-output', tmp_txt, file_path], check=True, capture_output=True, timeout=120)
        with open(tmp_txt, 'r', encoding='utf-8', errors='replace') as f:
            full_text = f.read()
    except:
        pass
    finally:
        if os.path.exists(tmp_txt):
            os.unlink(tmp_txt)
            os.rmdir(os.path.dirname(tmp_txt))

    if not full_text.strip():
        book_info = {"title": title, "year": 2026, "totalPages": 0, "type": "docx"}
        with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
            json.dump(book_info, f, ensure_ascii=False, indent=2)
        with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
            json.dump([], f)
        with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
            json.dump([], f)
        return 0

    paragraphs = [clean_text(p) for p in full_text.split('\n') if clean_text(p)]
    total_chars = sum(len(p) for p in paragraphs)
    total_pages = max(1, math.ceil(total_chars / 2000))
    chars_per_page = math.ceil(total_chars / total_pages)

    pages_data = []
    text_index = []
    current_text = []
    current_count = 0
    page_num = 1
    for text in paragraphs:
        text_len = len(text) + 1
        if current_count + text_len > chars_per_page * 1.5 and current_count > 0:
            page_text = clean_text('\n'.join(current_text))
            pages_data.append({"page": page_num, "text": page_text, "words": []})
            first_line = page_text[:100].replace('\n', ' ').strip()
            text_index.append({"page": page_num, "preview": first_line})
            page_num += 1
            current_text = [text]
            current_count = text_len
        else:
            current_text.append(text)
            current_count += text_len
    if current_text:
        page_text = clean_text('\n'.join(current_text))
        pages_data.append({"page": page_num, "text": page_text, "words": []})
        first_line = page_text[:100].replace('\n', ' ').strip()
        text_index.append({"page": page_num, "preview": first_line})

    with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
        json.dump(pages_data, f, ensure_ascii=False, indent=None)
    with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
        json.dump(text_index, f, ensure_ascii=False, indent=None)

    book_info = {"title": title, "year": 2026, "totalPages": len(pages_data), "type": "docx"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)
    return len(pages_data)

if __name__ == '__main__':
    if len(sys.argv) < 4:
        print(json.dumps({"success": False, "error": "Usage: convert-book.py <file_path> <slug> <out_dir> [title]"}))
        sys.exit(1)

    file_path = sys.argv[1]
    slug = sys.argv[2]
    out_dir = sys.argv[3]
    title = sys.argv[4] if len(sys.argv) > 4 else slug

    if not os.path.exists(file_path):
        print(json.dumps({"success": False, "error": f"File not found: {file_path}"}))
        sys.exit(1)

    ext = os.path.splitext(file_path)[1].lower()

    try:
        quality_warnings = []
        if ext == '.pdf':
            total_pages, quality_warnings = convert_pdf(file_path, out_dir, title)
            book_type = 'pdf'
        elif ext == '.docx':
            total_pages = convert_docx(file_path, out_dir, title)
            book_type = 'docx'
        elif ext == '.doc':
            total_pages = convert_doc(file_path, out_dir, title)
            book_type = 'docx'
        else:
            print(json.dumps({"success": False, "error": f"Unsupported format: {ext}"}))
            sys.exit(1)

        quality = {"warnings": quality_warnings} if quality_warnings else {}
        print(json.dumps({
            "success": True,
            "slug": slug,
            "title": title,
            "type": book_type,
            "totalPages": total_pages,
            "quality": quality
        }, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}, ensure_ascii=False))
        sys.exit(1)
  