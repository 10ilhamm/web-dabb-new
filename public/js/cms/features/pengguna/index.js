/* global $, jQuery */

/* ═══════════════════════════════════════════════════════════════════════════
   Export — Word / PDF / CSV / Excel / Copy / Print
   Shared helper: escape HTML
══════════════════════════════════════════════════════════════════════════════ */
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

/* ═══════════════════════════════════════════════════════════════════════════
   Read table data from DataTables DOM (clean — name/email via data attrs)
════════════════════════��═════════════════════════════════════════════════════ */
function readTableData(dt) {
    var headers = [], rows = [];
    dt.columns([0,1,2,3,4,5,6]).header().each(function(th){
        headers.push((th.innerText || th.textContent || "").trim());
    });
    dt.rows({ search: "applied" }).every(function(){
        var $node = $(dt.row(this).node()), $cells = $node.find("td"), row = [];
        for (var c = 0; c < 7; c++) {
            if (c === 1) {
                var $wrap = $cells.eq(c).find(".user-cell-wrap");
                if ($wrap.length) {
                    row.push($wrap.data("userName") || "");
                    row.push($wrap.data("userEmail") || "");
                } else {
                    row.push($cells.eq(c).clone().find("img,div.user-avatar").remove().end().text().trim());
                    row.push("");
                }
            } else {
                row.push($cells.eq(c).text().trim());
            }
        }
        rows.push(row);
    });
    return { headers: headers, rows: rows };
}

/* ═══════════════════════════════════════════════════════════════════════════
   Read locale
══════════════════════════════════════════════════════════════════════════════ */
function getLocale() {
    return $("html").attr("lang") || "id";
}
function isIndonesian() {
    return getLocale() === "id";
}

/* ═══════════════════════════════════════════════════════════════════════════
   Common text constants
══════════════════════════════════════════════════════════════════════════════ */
function i18nTitle() {
    return isIndonesian() ? "DAFTAR PENGGUNA SISTEM" : "SYSTEM USER LIST";
}
function i18nInstName() {
    return isIndonesian() ? "Depot Arsip Berkelanjutan Bandung" : "Continuing Archive Depot Bandung";
}
function i18nAddress() {
    return isIndonesian()
        ? "Jl. Ciwastra, Mekarjaya, Kec. Rancasari, Kota Bandung 40292, Jawa Barat. Indonesia"
        : "Jl. Ciwastra, Mekarjaya, Rancasari, Bandung 40292, West Java, Indonesia";
}
function i18nDate() {
    if (isIndonesian()) {
        return new Date().toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" });
    }
    return new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "2-digit" });
}

/* ═══════════════════════════════════════════════════════════════════════════
   Logo base64 — loaded async via hidden img element
══════════════════════════════════════════════════════════════════════════════ */
var _logoLoadCache = null;
function loadLogoBase64(callback) {
    if (_logoLoadCache) { callback(_logoLoadCache); return; }
    var img = new Image();
    img.onload = function(){
        try {
            var c = document.createElement("canvas");
            c.width = img.naturalWidth || 120; c.height = img.naturalHeight || 120;
            c.getContext("2d").drawImage(img, 0, 0);
            var d = c.toDataURL("image/png");
            _logoLoadCache = d;
            callback(d);
        } catch(e) { _logoLoadCache = ""; callback(""); }
    };
    img.onerror = function(){ _logoLoadCache = ""; callback(""); };
    img.src = "/image/logo_anri.png";
}

/* ═══════════════════════════════════════════════════════════════════════════
   Download helper
══════════════════════════════════════════════════════════════════════════════ */
function downloadBlob(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.style.display = "none";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function(){ URL.revokeObjectURL(url); }, 2000);
}

/* ═══════════════════════════════════════════════════════════════════════════
   HTML table builder (used by Word / Print)
   Structure: rows[0]=No, rows[1]=Name, rows[2]=Email, rows[3]=Username,
              rows[4]=Role, rows[5]=Status, rows[6]=Joined (7 columns total)
══════════════════════════════════════════════════════════════════════════════ */
function buildTableHTML(dt) {
    var data = readTableData(dt);
    var colNames = [
        isIndonesian() ? "No" : "No",
        isIndonesian() ? "Nama" : "Name",
        isIndonesian() ? "Email" : "Email",
        isIndonesian() ? "Username" : "Username",
        isIndonesian() ? "Peran" : "Role",
        isIndonesian() ? "Status" : "Status",
        isIndonesian() ? "Bergabung" : "Joined",
    ];
    var colWidths = [32, 85, 120, 80, 75, 60, 68];

    var html = '<table style="width:100%;border-collapse:collapse;font-size:9.5pt;" border="1" cellpadding="6" cellspacing="0">';
    html += "<thead><tr style=\"background:#174E93;color:white;\">";
    colNames.forEach(function(name, i){
        var w = colWidths[i] ? "width:" + colWidths[i] + "px;" : "";
        html += '<th style="text-align:center;' + w + 'padding:6px 10px;font-size:9pt;">' + name + "</th>";
    });
    html += "</tr></thead><tbody>";

    data.rows.forEach(function(row, idx){
        var bg = idx % 2 === 0 ? "#ffffff" : "#f3f6f9";
        html += '<tr style="background:' + bg + ';">';
        html += '<td style="text-align:center;color:#6b7280;padding:5px 8px;font-size:8.5pt;">' + escapeHtml(row[0]) + "</td>";
        html += '<td style="padding:5px 10px;"><span style="font-weight:600;color:#111827;font-size:9pt;">' + escapeHtml(row[1] || "") + "</span></td>";
        html += '<td style="padding:5px 10px;"><span style="color:#6b7280;font-size:9pt;">' + escapeHtml(row[2] || "") + "</span></td>";
        html += '<td style="padding:5px 10px;"><span style="color:#374151;font-size:9pt;">' + escapeHtml(row[3]) + "</span></td>";
        html += '<td style="padding:5px 10px;"><span style="color:#374151;font-size:9pt;">' + escapeHtml(row[4]) + "</span></td>";
        html += '<td style="text-align:center;padding:5px 8px;"><span style="color:#374151;font-size:9pt;">' + escapeHtml(row[5]) + "</span></td>";
        html += '<td style="text-align:center;padding:5px 8px;"><span style="color:#374151;font-size:8.5pt;">' + escapeHtml(row[6]) + "</span></td>";
        html += "</tr>";
    });

    html += "</tbody></table>";
    return html;
}

/* ═══════════════════════════════════════════════════════════════════════════
   HTML header builder (used by Word / Print)
══════════════════════════════════════════════════════════════════════════════ */
function buildHeaderHTML() {
    var logoSrc = (document.querySelector('img[src*="logo_anri"]') || {}).src || "/image/logo_anri.png";
    return (
        '<div style="margin-bottom:14px;">' +
        '<table style="width:100%;border-collapse:collapse;margin-bottom:8px;">' +
        "<tr>" +
        '<td style="width:62px;vertical-align:middle;">' +
        '<img src="' + logoSrc + '" alt="ANRI" style="height:52px;width:auto;display:block;" />' +
        "</td>" +
        '<td style="vertical-align:middle;padding-left:12px;">' +
        '<div style="font-weight:bold;font-size:12.5pt;color:#174E93;line-height:1.3;">' +
        i18nInstName() +
        "<br/>" +
        '<span style="font-size:9.5pt;color:#374151;font-weight:normal;">DABB &mdash; CMS Management</span>' +
        "</div>" +
        '<div style="font-size:8pt;color:#6b7280;margin-top:3px;">' + i18nAddress() + "</div>" +
        '<div style="font-size:8pt;color:#6b7280;">' + i18nDate() + "</div>" +
        "</td>" +
        "</tr>" +
        "</table>" +
        "</div>"
    );
}

/* ═══════════════════════════════════════════════════════════════════════════
   Word export
══════════════════════════════════════════════════════════════════════════════ */
function exportToWord(dt) {
    var css = [
        "body{font-family:Arial,sans-serif;margin:25px 20px;font-size:10pt;color:#111827;}",
        "table{width:100%;border-collapse:collapse;}th,td{padding:5px 8px;border:1px solid #d1d5db;}",
        "@page{margin:20mm;}div{page-break-after:always;}",
    ].join("");

    var html = [
        '<html xmlns:o="urn:schemas-microsoft-com:office:office"',
        'xmlns:w="urn:schemas-microsoft-com:office:word"',
        'xmlns="http://www.w3.org/TR/REC-html40">',
        '<head><meta charset="utf-8"><title>DABB - Pengguna</title>',
        "<style>" + css + "</style></head><body>",
        buildHeaderHTML(),
        buildTableHTML(dt),
        "</body></html>",
    ].join("");

    var fname = isIndonesian()
        ? "Daftar-Pengguna-Sistem-Bandung-Sustainable-Archives-Depot.doc"
        : "System-User-List-Bandung-Sustainable-Archives-Depot.doc";
    downloadBlob(new Blob(["﻿" + html], { type: "application/msword" }), fname);
}

/* ═══════════════════════════════════════════════════════════════════════════
   PDF export (pdfMake — download() is the reliable API in v0.2.x)
══════════════════════════════════════════════════════════════════════════════ */
function exportToPDF(dt) {
    var data = readTableData(dt);

    var body = [
        [
            { text: "No",              fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Nama",            fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Email",           fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Username",        fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Peran",           fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Status",          fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
            { text: "Bergabung",       fontSize: 10, bold: true, color: "white", fillColor: "#174E93", alignment: "center" },
        ],
    ];

    data.rows.forEach(function(row, idx){
        var bg = idx % 2 === 0 ? "#ffffff" : "#f3f6f9";
        body.push([
            { text: row[0],       fontSize: 9, alignment: "center", fillColor: bg },
            { text: row[1] || "", fontSize: 9, bold: true,         fillColor: bg },
            { text: row[2] || "", fontSize: 9, color: "#6b7280",   fillColor: bg },
            { text: row[3] || "", fontSize: 9,                    fillColor: bg },
            { text: row[4] || "", fontSize: 9,                    fillColor: bg },
            { text: row[5] || "", fontSize: 9, alignment: "center",fillColor: bg },
            { text: row[6] || "", fontSize: 8, alignment: "center",fillColor: bg },
        ]);
    });

    var fname = isIndonesian()
        ? "Daftar-Pengguna-Sistem-Bandung-Sustainable-Archives-Depot.pdf"
        : "System-User-List-Bandung-Sustainable-Archives-Depot.pdf";

    loadLogoBase64(function(logoDataUrl){
        var headerBlock = {
            stack: [
                logoDataUrl
                    ? { image: logoDataUrl, width: 52, alignment: "center", margin: [0, 0, 0, 4] }
                    : { text: "", width: 52 },
                { text: i18nInstName(), fontSize: 13, bold: true, color: "#174E93", alignment: "center", margin: [0, 2, 0, 2] },
                { text: i18nAddress(), fontSize: 8, color: "#6b7280", alignment: "center", margin: [0, 0, 0, 2] },
                { text: i18nDate(), fontSize: 8, color: "#9ca3af", alignment: "center", margin: [0, 0, 0, 0] },
            ],
            margin: [0, 0, 0, 8],
            alignment: "center",
        };

        var docDef = {
            pageSize: "A4",
            pageOrientation: "landscape",
            pageMargins: [18, 18, 18, 18],
            defaultStyle: { font: "Roboto" },
            content: [
                headerBlock,
                {
                    canvas: [{ type: "line", x1: 0, y1: 0, x2: 805, y2: 0, lineWidth: 2, lineColor: "#174E93" }],
                    margin: [0, 0, 0, 6],
                },
                {
                    text: i18nTitle(),
                    fontSize: 11,
                    bold: true,
                    alignment: "center",
                    margin: [0, 0, 0, 8],
                },
                {
                    columns: [
                        {
                            width: 90,
                            stack: [
                                { text: "", lineHeight: 1 },
                            ],
                        },
                        {
                            width: "auto",
                            alignment: "center",
                            table: {
                                headerRows: 1,
                                widths: [20, 100, 140, 90, 80, 60, 70],
                                body: body,
                            },
                        },
                        { width: 90, text: "" },
                    ],
                },
            ],
            footer: function(page, count){
                return {
                    columns: [
                        { text: "DABB CMS — " + i18nDate(), fontSize: 7, color: "#9ca3af", margin: [18, 0, 0, 0] },
                        { text: page + " / " + count, fontSize: 7, color: "#9ca3af", alignment: "right", margin: [0, 0, 18, 0] },
                    ],
                    margin: [0, 4, 0, 0],
                };
            },
        };

        try {
            pdfMake.createPdf(docDef).download(fname);
        } catch(e2) {
            alert('PDF Error: ' + e2.message);
        }
    });
}


/* ══════════���════════════════════════════════════════════════════════════════
   CSV export
══════════════════════════════════════════════════════════════════════════════ */
function exportToCSV(dt) {
    var data = readTableData(dt);
    var lines = [data.headers.join(",")];
    data.rows.forEach(function(row){
        lines.push(
            '"' + (row[0]||"").replace(/"/g,'""') + '",' +
            '"' + (row[1]||"").replace(/"/g,'""') + '",' +
            '"' + (row[2]||"").replace(/"/g,'""') + '",' +
            '"' + (row[3]||"").replace(/"/g,'""') + '",' +
            '"' + (row[4]||"").replace(/"/g,'""') + '",' +
            '"' + (row[5]||"").replace(/"/g,'""') + '",' +
            '"' + (row[6]||"").replace(/"/g,'""') + '"'
        );
    });
    var content = [
        '"' + i18nInstName() + '"',
        '"' + i18nAddress() + '"',
        "",
    ].concat(lines).join("\n");

    var fname = isIndonesian()
        ? "Daftar-Pengguna-Sistem-Bandung-Sustainable-Archives-Depot.csv"
        : "System-User-List-Bandung-Sustainable-Archives-Depot.csv";
    downloadBlob(new Blob(["﻿" + content], { type: "text/csv;charset=utf-8" }), fname);
}

/* ═══════════════════════════════════════════════════════════════════════════
   Excel export — raw Office Open XML (no SheetJS)
   Generates a valid .xlsx as a zip of XML files
══════════════════════════════════════════════════════════════════════════════ */
function exportToExcel(dt) {
    var data = readTableData(dt);
    var isId = (getLocale() === "id");

    var colLetters = ["A","B","C","D","E","F","G"];
    var colWidths  = [9, 28, 38, 22, 18, 14, 20]; /* character widths in Excel units */
    var hdrNames   = isId
        ? ["No","Nama","Email","Username","Peran","Status","Bergabung"]
        : ["No","Name","Email","Username","Role","Status","Joined"];

    var totalRows = 1 + data.rows.length;

    /* ── [Content_Types].xml ───────────────────────────────────────────── */
    var ct = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
        '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
        '  <Default Extension="xml"  ContentType="application/xml"/>',
        '  <Override PartName="/xl/workbook.xml"             ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
        '  <Override PartName="/xl/sheet1.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>',
        '  <Override PartName="/xl/styles.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>',
        '  <Override PartName="/xl/sharedStrings.xml"       ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>',
        '</Types>'
    ].join("\n");

    /* ── _rels/.rels ───────────────────────────────────────────────────── */
    var rels = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
        '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>',
        '</Relationships>'
    ].join("\n");

    /* ── xl/_rels/workbook.xml.rels ─────────────────────────────────────── */
    var wbrels = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
        '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="sheet1.xml"/>',
        '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"    Target="styles.xml"/>',
        '  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>',
        '</Relationships>'
    ].join("\n");

    /* ── xl/workbook.xml ───────────────────────────────────────────────── */
    var sheetName = isId ? "Pengguna" : "Users";
    var wb = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"',
        '          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">',
        '  <sheets>',
        '    <sheet name="' + sheetName + '" sheetId="1" r:id="rId1"/>',
        '  </sheets>',
        '</workbook>'
    ].join("\n");

    /* ── xl/styles.xml ─────────────────────────────────────────────────── */
    var styles = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">',
        '  <fonts count="2">',
        '    <font><sz val="10"/><name val="Calibri"/></font>',
        '    <font><b/><sz val="11"/><color rgb="FFFFFF"/><name val="Calibri"/></font>',
        '  </fonts>',
        '  <fills count="5">',
        '    <fill><patternFill patternType="none"/></fill>',
        '    <fill><patternFill patternType="gray125"/></fill>',
        '    <fill><patternFill patternType="solid"><fgColor rgb="174E93"/></patternFill></fill>',
        '    <fill><patternFill patternType="solid"><fgColor rgb="F3F6F9"/></patternFill></fill>',
        '  </fills>',
        '  <borders count="3">',
        '    <border><top/><bottom/><left/><right/></border>',
        '    <border>',
        '      <top style="thin"><color rgb="174E93"/></top>',
        '      <bottom style="thin"><color rgb="174E93"/></bottom>',
        '      <left style="thin"><color rgb="174E93"/></left>',
        '      <right style="thin"><color rgb="174E93"/></right>',
        '    </border>',
        '    <border>',
        '      <top style="thin"><color rgb="D1D5DB"/></top>',
        '      <bottom style="thin"><color rgb="D1D5DB"/></bottom>',
        '      <left style="thin"><color rgb="D1D5DB"/></left>',
        '      <right style="thin"><color rgb="D1D5DB"/></right>',
        '    </border>',
        '  </borders>',
        '  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>',
        '  <cellXfs count="5">',
        '    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>',
        '    <xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>',
        '    <xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>',
        '    <xf numFmtId="0" fontId="0" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>',
        '    <xf numFmtId="0" fontId="0" fillId="3" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>',
        '  </cellXfs>',
        '</styleSheet>'
    ].join("\n");

    /* ── xl/sharedStrings.xml ──────────────────────────────────────────── */
    var ssList = [];
    var ssMap  = {};

    function getSS(text) {
        text = String(text == null ? "" : text);
        if (ssMap[text] !== undefined) return ssMap[text];
        var idx = ssList.length;
        ssList.push(text);
        ssMap[text] = idx;
        return idx;
    }

    /* Pre-load headers into shared strings */
    for (var hi = 0; hi < hdrNames.length; hi++) getSS(hdrNames[hi]);

    /* Pre-load data values */
    for (var ri = 0; ri < data.rows.length; ri++) {
        var row = data.rows[ri];
        for (var cc = 0; cc < 7; cc++) getSS(row[cc] != null ? row[cc] : "");
    }

    var ssCount  = ssList.length;
    var ssUnique = ssList.length;
    var ssXml    = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' + ssCount + '" uniqueCount="' + ssUnique + '">'
    ];
    for (var si = 0; si < ssList.length; si++) {
        ssXml.push('  <si><t xml:space="preserve">' + escXml(ssList[si]) + '</t></si>');
    }
    ssXml.push('</sst>');
    var sharedStrings = ssXml.join("\n");

    /* ── xl/sheet1.xml ─────────────────────────────────────────────────── */
    var colWXml = "";
    for (var wi = 0; wi < 7; wi++) {
        colWXml += '<col min="' + (wi+1) + '" max="' + (wi+1) + '" width="' + colWidths[wi] + '" customWidth="1"/>';
    }

    var sheetXml = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">',
        '  <sheetViews><sheetView workbookViewId="0"/></sheetViews>',
        '  <cols>' + colWXml + '</cols>',
        '  <sheetData>',
    ].join("\n");

    /* Row 1: header */
    sheetXml += '<row r="1" spans="1:7">';
    for (var ci = 0; ci < 7; ci++) {
        sheetXml += '<c r="' + colLetters[ci] + '1" s="0" t="s"><v>' + getSS(hdrNames[ci]) + '</v></c>';
    }
    sheetXml += '</row>';

    /* Rows 2+: data */
    for (var ri2 = 0; ri2 < data.rows.length; ri2++) {
        var rowNum = ri2 + 2;
        var row    = data.rows[ri2];
        var isEven = (ri2 % 2 === 1);
        var sLeft  = isEven ? "3" : "1";  /* even=row3=gray, odd=row2=white */
        var sCtr   = isEven ? "4" : "2";

        sheetXml += '<row r="' + rowNum + '" spans="1:7">';
        for (var cc2 = 0; cc2 < 7; cc2++) {
            var sv    = (row[cc2] != null ? row[cc2] : "");
            var style = (cc2 === 0 || cc2 === 5 || cc2 === 6) ? sCtr : sLeft;
            sheetXml += '<c r="' + colLetters[cc2] + rowNum + '" s="' + style + '" t="s"><v>' + getSS(sv) + '</v></c>';
        }
        sheetXml += '</row>';
    }

    sheetXml += [
        '  </sheetData>',
        '</worksheet>'
    ].join("\n");

    /* ── Assemble ZIP ────────────────────────────────────────────────────── */
    function escXml(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    /* Use JSZip if available (loaded in page), otherwise use manual DEFLATE */
    var entries = {
        "[Content_Types].xml":                        ct,
        "_rels/.rels":                               rels,
        "xl/workbook.xml":                           wb,
        "xl/_rels/workbook.xml.rels":               wbrels,
        "xl/styles.xml":                            styles,
        "xl/sharedStrings.xml":                      sharedStrings,
        "xl/sheet1.xml":                             sheetXml,
    };

    if (typeof JSZip !== "undefined") {
        var zip = new JSZip();
        for (var k in entries) zip.file(k, entries[k]);
        zip.generateAsync({ type: "blob", mimeType: "application/octet-stream", compression: "DEFLATE" })
            .then(function(blob) {
                var fname = isId
                    ? "Daftar-Pengguna-Sistem-Bandung-Sustainable-Archives-Depot.xlsx"
                    : "System-User-List-Bandung-Sustainable-Archives-Depot.xlsx";
                downloadBlob(blob, fname);
            })
            .catch(function(e) { alert("ZIP error: " + e.message); });
    } else {
        alert("JSZip library not loaded. Please refresh the page.");
    }
}

/* ═══════════════════════════════════════════════════════════════════════════
   Copy to clipboard
══════════════════════════════════════════════════════════════════════════════ */
function exportToCopy(dt) {
    var data = readTableData(dt);
    var lines = [data.headers.join("\t")];
    data.rows.forEach(function(row){ lines.push(row.join("\t")); });
    if (navigator.clipboard) navigator.clipboard.writeText(lines.join("\n")).catch(function(){});
}

/* ═══════════════════════════════════════════════════════════════════════════
   Print
══════════════════════════════════════════════════════════════════════════════ */
function exportToPrint(dt) {
    var w = window.open("", "_blank");
    if (!w) return;
    w.document.write([
        '<!DOCTYPE html><html><head><meta charset="utf-8"><title>DABB - Pengguna</title>',
        "<style>",
        "body{font-family:Arial,sans-serif;margin:20px 18px;font-size:10pt;color:#111827;}",
        "table{width:100%;border-collapse:collapse;margin-top:8px;}",
        "th,td{padding:5px 8px;border:1px solid #d1d5db;}",
        "th{background:#174E93;color:white;font-size:8.5pt;text-align:center;padding:6px 8px;}",
        "tr:nth-child(even){background:#f3f6f9;}",
        "@media print{@page{size:A4 landscape;margin:15mm;}}",
        "</style>",
        "</head><body>",
        buildHeaderHTML(),
        buildTableHTML(dt),
        "</body></html>",
    ].join(""));
    w.document.close();
    w.print();
}

/* ═══════════════════════════════════════════════════════════════════════════
   DataTables init
══════════════════════════════════════════════════════════════════════════════ */
$(function () {
    if (!$("#tablePengguna").length) return;

    var i18n = window.penggunaI18n || {};

    /* Custom filter: role column 3, status column 4 */
    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== "tablePengguna") return true;
        var roleVal = ($("#filter-role").val() || "").toLowerCase();
        var statusVal = ($("#filter-status").val() || "").toLowerCase();
        var roleCell = (data[3] || "").toLowerCase();
        var statusCell = (data[4] || "").toLowerCase();
        if (roleVal && roleCell.indexOf(roleVal) === -1) return false;
        if (statusVal) {
            if (statusVal === "verified" && statusCell.indexOf("verif") === -1 && statusCell.indexOf("terverifik") === -1) return false;
            if (statusVal === "pending" && statusCell.indexOf("pending") === -1 && statusCell.indexOf("menunggu") === -1 && statusCell.indexOf("belum") === -1) return false;
        }
        return true;
    });

    var table = $("#tablePengguna").DataTable({
        columnDefs: [{ orderable: false, targets: [0, 6] }],
        order: [[5, "desc"]],
        language: {
            search: "",
            searchPlaceholder: window.LaravelDT?.dtSearchPlaceholder || i18n.dtSearchPlaceholder || "",
            lengthMenu: "_MENU_",
            info:        window.LaravelDT?.dtInfo        || "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty:   window.LaravelDT?.dtInfoEmpty   || "No entries",
            infoFiltered:window.LaravelDT?.dtInfoFiltered || "(filtered from _MAX_ total entries)",
            zeroRecords: window.LaravelDT?.dtZeroRecords || "No matching records found",
            paginate: {
                first: "&laquo;",
                previous: "&lsaquo;",
                next: "&rsaquo;",
                last: "&raquo;",
            },
        },
        dom:
            '<"dt-top-row"<"dataTables_length"l><"dt-top-right"fB>>' +
            "t" +
            '<"dt-bottom-row"<"dataTables_info"i><"dataTables_paginate"p>>',
        buttons: [
            {
                extend: "collection",
                text:
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' +
                    "</svg> " + (i18n.btnExport || "Export"),
                className: "btn-export-dropdown",
                buttons: [
                    { text: i18n.btnCopy  || "Copy",   action: function(e, dt){ exportToCopy(dt);  } },
                    { text: i18n.btnCsv   || "CSV",    action: function(e, dt){ exportToCSV(dt);   } },
                    { text: i18n.btnExcel || "Excel",  action: function(e, dt){ exportToExcel(dt); } },
                    { text: i18n.btnWord  || "Word",   action: function(e, dt){ exportToWord(dt);   } },
                    { text: i18n.btnPdf   || "PDF",    action: function(e, dt){ exportToPDF(dt); } },
                    { text: i18n.btnPrint || "Print",  action: function(e, dt){ exportToPrint(dt); } },
                ],
            },
        ],
        initComplete: function () {
            var $tr = $("#tablePengguna_wrapper .dt-top-right");
            if ($tr.length && !$tr.find(".btn-add-user").length && i18n.urlCreate) {
                $tr.append(
                    '<a class="btn-add-user" href="' + i18n.urlCreate + '">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>' +
                    "</svg>" +
                    "<span>" + (i18n.btnAddUser || "Tambah") + "</span></a>"
                );
            }
        },
    });

    $("#filter-role, #filter-status").on("change", function () {
        table.draw();
    });
});
