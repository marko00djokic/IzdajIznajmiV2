#!/usr/bin/env python3
"""Build an editable ODT from the PIR Markdown for LibreOffice DOCX/PDF export."""

from __future__ import annotations

import re
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED, ZIP_STORED

from lxml import etree
from PIL import Image


HERE = Path(__file__).resolve().parent
SOURCE = HERE / "pir-verzija-0-2.md"
OUTPUT = HERE / "pir-za-mentorski-pregled.odt"
NS = {
    "office": "urn:oasis:names:tc:opendocument:xmlns:office:1.0",
    "style": "urn:oasis:names:tc:opendocument:xmlns:style:1.0",
    "text": "urn:oasis:names:tc:opendocument:xmlns:text:1.0",
    "table": "urn:oasis:names:tc:opendocument:xmlns:table:1.0",
    "draw": "urn:oasis:names:tc:opendocument:xmlns:drawing:1.0",
    "fo": "urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0",
    "svg": "urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0",
    "xlink": "http://www.w3.org/1999/xlink",
    "manifest": "urn:oasis:names:tc:opendocument:xmlns:manifest:1.0",
}


def q(prefix: str, name: str) -> str:
    return f"{{{NS[prefix]}}}{name}"


def child(parent, prefix: str, name: str, attrs=None, text=None):
    element = etree.SubElement(parent, q(prefix, name))
    for key, value in (attrs or {}).items():
        element.set(q(*key.split(":", 1)), str(value))
    if text is not None:
        element.text = text
    return element


def plain(value: str) -> str:
    value = re.sub(r"\[([^]]+)\]\([^)]+\)", r"\1", value)
    return re.sub(r"\*\*|\*|`|<br\s*/?>", "", value)


def add_inline(parent, value: str, notes: dict[str, str], used: set[str]):
    token = re.compile(r"\[\^(\d+)\]|\[([^]]+)\]\(([^)]+)\)|`([^`]+)`|\*\*([^*]+)\*\*|\*([^*]+)\*")
    pos = 0

    def add_text(target, content):
        if not content:
            return
        if len(target):
            target[-1].tail = (target[-1].tail or "") + content
        else:
            target.text = (target.text or "") + content

    for match in token.finditer(value):
        add_text(parent, value[pos:match.start()])
        number, label, href, code, bold, italic = match.groups()
        if number:
            if number not in used:
                note = child(parent, "text", "note", {"text:note-class": "footnote", "text:id": f"ftn{number}"})
                child(note, "text", "note-citation", text=number)
                body = child(note, "text", "note-body")
                p = child(body, "text", "p", {"text:style-name": "Footnote"})
                p.text = plain(notes[number])
                used.add(number)
            else:
                child(parent, "text", "span", {"text:style-name": "Super"}, number)
        elif label:
            link = child(parent, "text", "a", {"xlink:href": href, "xlink:type": "simple"}, label)
        else:
            style = "Code" if code else "Bold" if bold else "Italic"
            child(parent, "text", "span", {"text:style-name": style}, code or bold or italic)
        pos = match.end()
    add_text(parent, value[pos:])


def paragraph(root, value: str, style: str, notes, used):
    p = child(root, "text", "p", {"text:style-name": style})
    add_inline(p, value.replace("<br>", " ").replace("<br/>", " "), notes, used)
    return p


def build_styles():
    root = etree.Element(q("office", "document-styles"), nsmap=NS)
    root.set(q("office", "version"), "1.2")
    fonts = child(root, "office", "font-face-decls")
    child(fonts, "style", "font-face", {"style:name": "Times New Roman", "svg:font-family": "Times New Roman"})
    styles = child(root, "office", "styles")

    def pstyle(name, parent="Standard", size="12pt", before="0cm", after="0.12cm", extra=None):
        el = child(styles, "style", "style", {"style:name": name, "style:family": "paragraph", "style:parent-style-name": parent} if name != "Standard" else {"style:name": name, "style:family": "paragraph"})
        props = {"fo:margin-top": before, "fo:margin-bottom": after, "fo:line-height": "150%", "fo:text-align": "justify"}
        props.update(extra or {})
        child(el, "style", "paragraph-properties", props)
        child(el, "style", "text-properties", {"style:font-name": "Times New Roman", "fo:font-size": size})
        return el

    pstyle("Standard")
    pstyle("H1", size="14pt", before="0.45cm", after="0.25cm", extra={"fo:keep-with-next": "always", "fo:text-align": "start"})
    pstyle("H2", before="0.3cm", after="0.18cm", extra={"fo:keep-with-next": "always", "fo:text-align": "start"})
    pstyle("Title", size="16pt", before="0.7cm", after="0.4cm", extra={"fo:text-align": "center"})
    pstyle("Cover", size="12pt", after="0.28cm", extra={"fo:text-align": "center"})
    pstyle("Caption", size="12pt", after="0.22cm", extra={"fo:text-align": "start", "fo:keep-together": "always"})
    pstyle("TableText", size="12pt", after="0cm", extra={"fo:text-align": "start"})
    pstyle("Footnote", size="10pt", after="0.06cm", extra={"fo:line-height": "115%"})
    pstyle("Image", after="0.1cm", extra={"fo:text-align": "center", "fo:keep-with-next": "always"})
    pstyle("Break", after="0cm", extra={"fo:break-before": "page"})
    for name, props in {"Bold": {"fo:font-weight": "bold"}, "Italic": {"fo:font-style": "italic"}, "Code": {"fo:font-family": "Liberation Mono", "fo:font-size": "10pt"}, "Super": {"style:text-position": "super 58%"}}.items():
        e = child(styles, "style", "style", {"style:name": name, "style:family": "text"})
        child(e, "style", "text-properties", props)
    for name in ("H1", "H2"):
        target = styles.xpath(f'./style:style[@style:name="{name}"]/style:text-properties', namespaces=NS)[0]
        target.set(q("fo", "font-weight"), "bold")
    auto = child(root, "office", "automatic-styles")
    page = child(auto, "style", "page-layout", {"style:name": "PL1"})
    # Writer adds the 0.5 cm footer area to the exported DOCX bottom margin.
    child(page, "style", "page-layout-properties", {"fo:page-width": "21cm", "fo:page-height": "29.7cm", "style:print-orientation": "portrait", "fo:margin-top": "2.5cm", "fo:margin-bottom": "2cm", "fo:margin-left": "2.5cm", "fo:margin-right": "2.5cm"})
    masters = child(root, "office", "master-styles")
    master = child(masters, "style", "master-page", {"style:name": "Standard", "style:page-layout-name": "PL1"})
    footer = child(master, "style", "footer")
    fp = child(footer, "text", "p", {"text:style-name": "Cover"})
    child(fp, "text", "page-number", {"text:select-page": "current"}, "1")
    return etree.tostring(root, xml_declaration=True, encoding="UTF-8")


def build_content(lines, notes):
    root = etree.Element(q("office", "document-content"), nsmap=NS)
    root.set(q("office", "version"), "1.2")
    auto = child(root, "office", "automatic-styles")
    table_style = child(auto, "style", "style", {"style:name": "TableBox", "style:family": "table-cell"})
    child(table_style, "style", "table-cell-properties", {"fo:border": "0.5pt solid #777777", "fo:padding": "0.08cm"})
    body = child(child(root, "office", "body"), "office", "text")
    used: set[str] = set()
    images: dict[str, bytes] = {}
    buffer: list[str] = []
    in_cover = True
    breaks = 0
    in_literature = False

    def flush():
        if buffer:
            value = " ".join(s.strip() for s in buffer).strip()
            style = "Cover" if in_cover else "Caption" if value.startswith(("**Слика ", "**Табела ")) else "Standard"
            paragraph(body, value, style, notes, used)
            buffer.clear()

    i = 0
    while i < len(lines):
        line = lines[i].rstrip()
        if line.startswith("# 12. ") and in_literature:
            for number, reference in notes.items():
                paragraph(body, f"[{number}] {plain(reference)}", "Standard", notes, used)
            in_literature = False
        if not line.strip():
            flush(); i += 1; continue
        if line.startswith("---"):
            flush(); breaks += 1
            if breaks >= 1:
                child(body, "text", "p", {"text:style-name": "Break"})
                in_cover = False
            i += 1; continue
        if line.startswith("# 11. "):
            in_literature = True
        if line.startswith("!["):
            flush()
            target = re.search(r"\]\(([^)]+)\)", line).group(1)
            picture = HERE / target.replace(".svg", ".png")
            if not picture.exists():
                raise FileNotFoundError(picture)
            width, height = Image.open(picture).size
            max_height = 12.2 if picture.stem == "postupak-rangiranja" else 15.8
            scale = min(15.8 / width, max_height / height)
            p = child(body, "text", "p", {"text:style-name": "Image"})
            frame = child(p, "draw", "frame", {"draw:name": picture.stem, "text:anchor-type": "as-char", "svg:width": f"{width*scale:.2f}cm", "svg:height": f"{height*scale:.2f}cm"})
            child(frame, "draw", "image", {"xlink:href": f"Pictures/{picture.name}", "xlink:type": "simple", "xlink:show": "embed", "xlink:actuate": "onLoad"})
            images[picture.name] = picture.read_bytes()
            i += 1; continue
        if line.startswith("|"):
            flush(); rows = []
            while i < len(lines) and lines[i].startswith("|"):
                cells = [c.strip() for c in lines[i].strip().strip("|").split("|")]
                if not all(re.fullmatch(r":?-+:?", c) for c in cells): rows.append(cells)
                i += 1
            table = child(body, "table", "table", {"table:name": f"Table{len(body)}"})
            cols = max(map(len, rows))
            for _ in range(cols): child(table, "table", "table-column")
            for row in rows:
                tr = child(table, "table", "table-row")
                for cell in row:
                    tc = child(tr, "table", "table-cell", {"table:style-name": "TableBox", "office:value-type": "string"})
                    paragraph(tc, cell, "TableText", notes, used)
            continue
        if line.startswith("#"):
            flush(); depth = len(line) - len(line.lstrip("#")); title = line[depth:].strip()
            if in_cover:
                if title != "Насловна страна":
                    paragraph(body, title, "Title" if depth == 1 else "Cover", notes, used)
            else:
                h = child(body, "text", "h", {"text:outline-level": "1" if depth == 1 else "2", "text:style-name": "H1" if depth == 1 else "H2"})
                add_inline(h, title, notes, used)
            i += 1; continue
        if line.startswith(("* ", "- ")) or re.match(r"\d+\. ", line):
            flush(); paragraph(body, ("• " + line[2:]) if line.startswith(("* ", "- ")) else line, "Standard", notes, used)
            i += 1; continue
        if line == "$$":
            flush(); i += 1; math = []
            while i < len(lines) and lines[i] != "$$": math.append(lines[i]); i += 1
            paragraph(body, " ".join(math).replace("\\", ""), "Standard", notes, used)
            i += 1; continue
        if in_cover:
            flush()
            paragraph(body, line, "Cover", notes, used)
        else:
            buffer.append(line)
        i += 1
    flush()
    return etree.tostring(root, xml_declaration=True, encoding="UTF-8"), images


def main():
    lines = SOURCE.read_text().splitlines()
    notes = {m.group(1): m.group(2) for line in lines if (m := re.match(r"^\[\^(\d+)\]:\s*(.+)$", line))}
    start = next(i for i, line in enumerate(lines) if line.startswith("## Насловна страна"))
    lines = [line for line in lines[start:] if not re.match(r"^\[\^\d+\]:", line)]
    content, images = build_content(lines, notes)
    manifest = etree.Element(q("manifest", "manifest"), nsmap={"manifest": NS["manifest"]})
    manifest.set(q("manifest", "version"), "1.2")
    for path, mime in [("/", "application/vnd.oasis.opendocument.text"), ("content.xml", "text/xml"), ("styles.xml", "text/xml")]:
        child(manifest, "manifest", "file-entry", {"manifest:full-path": path, "manifest:media-type": mime})
    for name in images:
        child(manifest, "manifest", "file-entry", {"manifest:full-path": f"Pictures/{name}", "manifest:media-type": "image/png"})
    with ZipFile(OUTPUT, "w") as z:
        z.writestr("mimetype", "application/vnd.oasis.opendocument.text", compress_type=ZIP_STORED)
        z.writestr("content.xml", content, compress_type=ZIP_DEFLATED)
        z.writestr("styles.xml", build_styles(), compress_type=ZIP_DEFLATED)
        z.writestr("META-INF/manifest.xml", etree.tostring(manifest, xml_declaration=True, encoding="UTF-8"), compress_type=ZIP_DEFLATED)
        for name, data in images.items(): z.writestr(f"Pictures/{name}", data, compress_type=ZIP_DEFLATED)
    print(OUTPUT)


if __name__ == "__main__":
    main()
