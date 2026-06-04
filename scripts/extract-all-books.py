#!/usr/bin/env python3
import os, sys, json, math, re, subprocess, tempfile, shutil

files_dir = os.path.join(os.path.dirname(__file__), '..', 'files')
assets_dir = os.path.join(os.path.dirname(__file__), '..', 'public', 'assets')

def slugify(name):
    name = re.sub(r'\.[^.]+$', '', name)
    name = re.sub(r'[\s_()\[\]{}]+', '-', name)
    name = re.sub(r'[^a-zA-Z0-9\u0E80-\u0EFF-]', '', name)
    name = re.sub(r'-+', '-', name)
    name = name.strip('-').lower()[:60]
    return name or 'book'

def extract_pdf(pdf_path, slug, year):
    import fitz
    out_dir = os.path.join(assets_dir, slug)
    os.makedirs(out_dir, exist_ok=True)

    doc = fitz.open(pdf_path)
    total_pages = doc.page_count

    re_lao_digit = re.compile(r'^[\u0E50-\u0E590-9.,/]+$')

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
        digit_count = sum(1 for w in right_words if re_lao_digit.match(w[4].strip()))
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

    def fix_lao_text(text):
        text = re.sub(r'(?<=[\u0E81-\u0EAE]) (?=[\u0EB9\u0EB8])', '', text)
        text = re.sub(r'(?<=[\u0E81-\u0EAE]) (?=[\u0EC8\u0EC9])', '\u0EB9', text)
        text = re.sub(r'\u0EAA \u0E95', '\u0EAA\u0EB9\u0E95', text)
        text = re.sub(r'\u0E9E \u0EA1', '\u0E9E\u0EB9\u0EA1', text)
        text = text.replace('\u0E84\u0EB2\u0E99\u0EB2', '\u0E84\u0EB3\u0E99\u0EB3')
        return text
 
    pages_data = []
    text_index = []
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
    doc.close()

    with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
        json.dump(pages_data, f, ensure_ascii=False, indent=None)
    with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
        json.dump(text_index, f, ensure_ascii=False, indent=None)

    # Copy PDF to assets
    shutil.copy2(pdf_path, os.path.join(out_dir, os.path.basename(pdf_path)))

    # Generate cover from page 1
    mat_d = fitz.Matrix(150/72, 150/72)
    pix = doc[0].get_pixmap(matrix=mat_d)
    pix.save(os.path.join(out_dir, 'cover.png'))

    title = os.path.splitext(os.path.basename(pdf_path))[0]
    book_info = {"title": title, "year": year, "totalPages": total_pages, "type": "pdf"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)

    doc.close()
    return total_pages

def clean_text(text):
    text = re.sub(r'[\u200b\u200c\u200d\u200e\u200f\u2060\u2061\u2062\u2063\u2064\ufeff\u00ad]', '', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_docx(docx_path, slug, year):
    import docx
    out_dir = os.path.join(assets_dir, slug)
    os.makedirs(out_dir, exist_ok=True)

    doc = docx.Document(docx_path)
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

    title = os.path.splitext(os.path.basename(docx_path))[0]
    book_info = {"title": title, "year": year, "totalPages": len(pages_data), "type": "docx"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)

    return len(pages_data)

def extract_doc(doc_path, slug, year):
    # Convert .doc to .txt using macOS textutil, then split into pages
    out_dir = os.path.join(assets_dir, slug)
    os.makedirs(out_dir, exist_ok=True)

    tmp_txt = os.path.join(tempfile.mkdtemp(), 'output.txt')
    try:
        subprocess.run(['textutil', '-convert', 'txt', '-output', tmp_txt, doc_path], check=True, capture_output=True, timeout=120)
        with open(tmp_txt, 'r', encoding='utf-8', errors='replace') as f:
            full_text = f.read()
    except Exception as e:
        print(f"  textutil failed: {e}")
        full_text = ""
    finally:
        if os.path.exists(tmp_txt):
            os.unlink(tmp_txt)
            os.rmdir(os.path.dirname(tmp_txt))

    if not full_text.strip():
        print(f"  WARNING: Could not extract text from .doc file")
        # Still create the asset dir so it shows in listing
        title = os.path.splitext(os.path.basename(doc_path))[0]
        book_info = {"title": title, "year": year, "totalPages": 0, "type": "docx"}
        with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
            json.dump(book_info, f, ensure_ascii=False, indent=2)
        with open(os.path.join(out_dir, 'pages.json'), 'w', encoding='utf-8') as f:
            json.dump([], f)
        with open(os.path.join(out_dir, 'index.json'), 'w', encoding='utf-8') as f:
            json.dump([], f)
        return 0

    paragraphs = [clean_text(p) for p in full_text.split('\n') if clean_text(p)]
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

    title = os.path.splitext(os.path.basename(doc_path))[0]
    book_info = {"title": title, "year": year, "totalPages": len(pages_data), "type": "docx"}
    with open(os.path.join(out_dir, 'book.json'), 'w', encoding='utf-8') as f:
        json.dump(book_info, f, ensure_ascii=False, indent=2)

    return len(pages_data)

def file_year(filename):
    import datetime
    mtime = os.path.getmtime(filename)
    dt = datetime.datetime.fromtimestamp(mtime)
    return dt.year

def main():
    # Get existing books
    books_path = os.path.join(assets_dir, 'books.json')
    existing = {}
    existing_slugs = set()
    if os.path.exists(books_path):
        with open(books_path, 'r', encoding='utf-8') as f:
            existing_books = json.load(f)
        for b in existing_books:
            existing[b['file']] = b
            existing_slugs.add(b['slug'])

    # Find all files in /files/
    all_files = sorted(os.listdir(files_dir))
    new_books = []
    updated_any = False

    for fname in all_files:
        fpath = os.path.join(files_dir, fname)
        if not os.path.isfile(fpath):
            continue

        ext = os.path.splitext(fname)[1].lower()
        rel_path = os.path.join('files', fname)

        # Already processed
        if rel_path in existing:
            new_books.append(existing[rel_path])
            print(f"SKIP (exists): {fname}")
            continue

        slug = slugify(fname)
        if slug in existing_slugs:
            slug = slug + '-' + str(len([k for k in existing_slugs if k.startswith(slug)]))
        year = file_year(fpath)

        print(f"\nPROCESSING: {fname} ({ext}) -> slug={slug}")

        try:
            if ext == '.pdf':
                pages = extract_pdf(fpath, slug, year)
                print(f"  OK: {pages} pages extracted (PDF)")
            elif ext == '.docx':
                pages = extract_docx(fpath, slug, year)
                print(f"  OK: {pages} pages extracted (DOCX)")
            elif ext == '.doc':
                pages = extract_doc(fpath, slug, year)
                print(f"  OK: {pages} pages extracted (DOC via textutil)")
            else:
                print(f"  SKIP: unsupported format {ext}")
                continue

            entry = {
                "slug": slug,
                "file": rel_path,
                "year": year,
                "totalPages": pages,
                "type": "pdf" if ext == '.pdf' else "docx"
            }
            new_books.append(entry)
            existing_slugs.add(slug)
            updated_any = True
        except Exception as e:
            print(f"  ERROR: {e}")
            # Still add a basic entry so it shows in listing
            entry = {
                "slug": slug,
                "file": rel_path,
                "year": year,
                "totalPages": 0,
                "type": "pdf" if ext == '.pdf' else "docx"
            }
            new_books.append(entry)
            existing_slugs.add(slug)
            updated_any = True

    with open(books_path, 'w', encoding='utf-8') as f:
        json.dump(new_books, f, ensure_ascii=False, indent=2)

    print(f"\n{'='*50}")
    print(f"Done! {len(new_books)} books total in books.json")

if __name__ == '__main__':
    main()
