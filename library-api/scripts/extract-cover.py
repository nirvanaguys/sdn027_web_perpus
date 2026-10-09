#!/usr/bin/env python3
"""Ekstrak gambar sampul (halaman depan) dari file ebook PDF/EPUB.

Usage:
    extract-cover.py <input_ebook> <output_image> [--width 600]

- PDF  : render halaman pertama menjadi PNG.
- EPUB : ambil gambar sampul dari metadata, atau file gambar pertama
         yang tampak seperti cover (cover.jpg/png, dst). Jika tidak ada
         gambar sama sekali, render halaman teks pertama menjadi PNG.

Output selalu PNG dengan lebar sesuai --width (proporsional).
"""
import sys
import os
import zipfile
import xml.etree.ElementTree as ET

WIDTH = 600


def parse_args(argv):
    src = argv[1] if len(argv) > 1 else None
    dst = argv[2] if len(argv) > 2 else None
    width = WIDTH
    for i, a in enumerate(argv):
        if a == "--width" and i + 1 < len(argv):
            try:
                width = max(200, min(1200, int(argv[i + 1])))
            except ValueError:
                pass
    return src, dst, width


def save_pixmap_cover(doc, dst, width):
    """Render halaman pertama dokumen menjadi PNG."""
    import pymupdf

    page = doc[0]
    zoom = width / page.rect.width
    # Batasi zoom agar tidak raksasa untuk halaman kecil.
    zoom = max(0.5, min(zoom, 3.0))
    pix = page.get_pixmap(matrix=pymupdf.Matrix(zoom, zoom))
    pix.save(dst)
    return True


def extract_pdf(src, dst, width):
    import pymupdf

    doc = pymupdf.open(src)
    if len(doc) == 0:
        raise SystemExit("PDF kosong, tidak ada halaman.")
    # 1) Jika halaman depan punya gambar full-page, pakai itu (lebih tajam).
    page = doc[0]
    imgs = page.get_images(full=True)
    if imgs:
        # Pilih gambar terbesar di halaman pertama.
        best = None
        best_area = 0
        for img in imgs:
            try:
                bbox_list = page.get_image_bbox(img)
                area = sum(b.width * b.height for b in bbox_list)
            except Exception:
                area = 0
            if area > best_area:
                best_area = area
                best = img
        page_area = page.rect.width * page.rect.height
        if best is not None and best_area > page_area * 0.5:
            xref = best[0]
            pix = pymupdf.Pixmap(doc, xref)
            if pix.n - pix.alpha > 3:  # CMYK -> RGB
                pix = pymupdf.Pixmap(pymupdf.csRGB, pix)
            pix.save(dst)
            doc.close()
            # Normalisasi lebar via PIL bila tersedia.
            normalize_width(dst, width)
            return True
    # 2) Render halaman pertama.
    ok = save_pixmap_cover(doc, dst, width)
    doc.close()
    return ok


def epub_cover_from_metadata(zf, opf_path):
    """Cari path gambar sampul dari OPF metadata."""
    try:
        data = zf.read(opf_path)
    except KeyError:
        return None
    ns = {"opf": "http://www.idpf.org/2007/opf"}
    try:
        root = ET.fromstring(data)
    except ET.ParseError:
        return None
    base = os.path.dirname(opf_path)

    def join(p):
        return (base + "/" + p).replace("\\", "/") if base else p

    # <meta name="cover" content="id"/> -> manifest item.
    cover_id = None
    for meta in root.findall(".//opf:meta", ns):
        if meta.get("name") == "cover":
            cover_id = meta.get("content")
            break
    if cover_id:
        for item in root.findall(".//opf:item", ns):
            if item.get("id") == cover_id:
                href = item.get("href")
                if href:
                    return join(href)
    # guide <reference type="cover">.
    for ref in root.findall(".//opf:reference", ns):
        if (ref.get("type") or "").lower() == "cover" and ref.get("href"):
            return join(ref.get("href"))
    # manifest item yang namanya mengandung "cover" dan berupa gambar.
    for item in root.findall(".//opf:item", ns):
        mt = item.get("media-type") or ""
        href = item.get("href") or ""
        iid = item.get("id") or ""
        if mt.startswith("image/") and ("cover" in href.lower() or "cover" in iid.lower()):
            return join(href)
    return None


def find_opf(zf):
    # Lewat container.xml dulu.
    try:
        container = zf.read("META-INF/container.xml").decode("utf-8", "ignore")
        root = ET.fromstring(container)
        for el in root.iter():
            if el.tag.endswith("rootfile"):
                fp = el.get("full-path")
                if fp:
                    return fp
    except (KeyError, ET.ParseError):
        pass
    for name in zf.namelist():
        if name.endswith(".opf"):
            return name
    return None


def extract_epub(src, dst, width):
    import pymupdf

    zf = zipfile.ZipFile(src)
    names = zf.namelist()
    images = [n for n in names if n.lower().endswith((".jpg", ".jpeg", ".png", ".webp"))]

    opf = find_opf(zf)
    ordered = []
    if opf:
        cover_path = epub_cover_from_metadata(zf, opf)
        if cover_path and cover_path in names:
            ordered.append(cover_path)
    # Kandidat nama file yang jelas-jelas cover.
    for n in images:
        low = n.lower()
        if "cover" in low or "sampul" in low or "title" in low:
            if n not in ordered:
                ordered.append(n)
    for n in images:
        if n not in ordered:
            ordered.append(n)

    for candidate in ordered:
        try:
            raw = zf.read(candidate)
            out = candidate.lower()
            if out.endswith(".webp"):
                # Konversi webp -> png via pymupdf bila bisa.
                pix = pymupdf.Pixmap(raw)
                pix.save(dst)
            else:
                with open(dst, "wb") as f:
                    f.write(raw)
            normalize_width(dst, width)
            zf.close()
            return True
        except Exception:
            continue

    # Tidak ada gambar: render dokumen (halaman teks pertama).
    zf.close()
    doc = pymupdf.open(src)
    if len(doc) == 0:
        raise SystemExit("EPUB kosong.")
    ok = save_pixmap_cover(doc, dst, width)
    doc.close()
    return ok


def normalize_width(dst, width):
    """Samakan lebar output (proporsional). Butuh Pillow; abaikan bila tak ada."""
    try:
        from PIL import Image
    except ImportError:
        return
    try:
        im = Image.open(dst)
        if im.width != width:
            h = round(im.height * width / im.width)
            im = im.resize((width, h), Image.LANCZOS)
            im.save(dst)
    except Exception:
        pass


def main():
    src, dst, width = parse_args(sys.argv)
    if not src or not dst:
        print(__doc__)
        return 2
    if not os.path.isfile(src):
        print(f"File tidak ditemukan: {src}", file=sys.stderr)
        return 1
    ext = os.path.splitext(src)[1].lower()
    try:
        if ext == ".pdf":
            extract_pdf(src, dst, width)
        elif ext == ".epub":
            extract_epub(src, dst, width)
        else:
            print(f"Format tidak didukung: {ext}", file=sys.stderr)
            return 1
    except SystemExit as e:
        print(str(e), file=sys.stderr)
        return 1
    except Exception as e:  # noqa: BLE001
        print(f"Gagal mengekstrak sampul: {e}", file=sys.stderr)
        return 1
    print(f"OK: {dst}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
