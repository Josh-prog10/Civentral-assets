/**
 * CIVENTRAL Audit Export Utility
 * Shared across: data-changes, user-activities, login-history
 * Supports: PDF (print popup), Excel (.xls), CSV
 */
window.CivAuditExport = {

  /**
   * Open a clean print popup showing only the data table.
   * User can Ctrl+P or Save as PDF from browser dialog.
   */
  printTable(title, headers, rows) {
    const dateStr = new Date().toLocaleString('en-US', {
      timeZone: 'Asia/Manila',
      dateStyle: 'medium',
      timeStyle: 'short'
    });

    const headerHtml = headers.map(h => `<th>${this._esc(h)}</th>`).join('');
    const bodyHtml = rows.map(row =>
      `<tr>${row.map(cell => `<td>${this._esc(cell ?? '')}</td>`).join('')}</tr>`
    ).join('');

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>${this._esc(title)}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 9pt; color: #000; background: #fff; padding: 16px; }
    .report-header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
    .report-header h1 { font-size: 14pt; color: #0f172a; font-weight: bold; }
    .report-header p { font-size: 8pt; color: #475569; margin-top: 3px; }
    .meta { display: flex; justify-content: space-between; font-size: 8pt; color: #64748b; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    thead { background-color: #0f172a; color: #fff; }
    thead th { padding: 7px 8px; font-size: 8pt; text-align: left; font-weight: bold; letter-spacing: 0.04em; white-space: nowrap; }
    tbody tr { border-bottom: 1px solid #e2e8f0; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 6px 8px; font-size: 8pt; vertical-align: top; word-break: break-word; max-width: 200px; }
    .footer { margin-top: 14px; font-size: 7.5pt; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    @media print {
      body { padding: 8px; }
      @page { margin: 10mm; size: landscape; }
    }
  </style>
</head>
<body>
  <div class="report-header">
    <h1>CIVENTRAL &mdash; ${this._esc(title)}</h1>
    <p>Caloocan Municipal Portal &bull; Audit Log Report</p>
  </div>
  <div class="meta">
    <span>Total Records: <strong>${rows.length}</strong></span>
    <span>Generated: ${dateStr}</span>
  </div>
  <table>
    <thead><tr>${headerHtml}</tr></thead>
    <tbody>${bodyHtml || '<tr><td colspan="' + headers.length + '" style="text-align:center;padding:20px;color:#94a3b8;">No records to display.</td></tr>'}</tbody>
  </table>
  <div class="footer">CIVENTRAL Audit System &bull; This is a system-generated report &bull; For official use only</div>
  <script>window.onload = function(){ window.print(); };<\/script>
</body>
</html>`;

    const win = window.open('', '_blank', 'width=1100,height=750,scrollbars=yes');
    if (!win) {
      alert('Please allow popups for this site to use PDF export.');
      return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
  },

  /**
   * Download a real Excel-compatible .xls file with visible data.
   * Uses HTML table wrapped in Excel XML namespace — opens natively in Excel.
   */
  downloadExcel(filename, title, headers, rows) {
    const headerHtml = headers.map(h => `<th style="background:#0f172a;color:#fff;font-weight:bold;padding:6px 8px;border:1px solid #334155;">${this._esc(h)}</th>`).join('');
    const bodyHtml = rows.map((row, i) => {
      const bg = i % 2 === 0 ? '#ffffff' : '#f8fafc';
      const cells = row.map(cell => `<td style="border:1px solid #e2e8f0;padding:5px 8px;background:${bg};">${this._esc(cell ?? '')}</td>`).join('');
      return `<tr>${cells}</tr>`;
    }).join('');

    const dateStr = new Date().toLocaleString('en-US', { timeZone: 'Asia/Manila' });

    const excelHtml = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="UTF-8">
  <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
    <x:Name>${this._esc(title.substring(0,31))}</x:Name>
    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
  </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
</head>
<body>
  <p style="font-family:Arial;font-size:11pt;font-weight:bold;color:#0f172a;margin-bottom:4px;">CIVENTRAL &mdash; ${this._esc(title)}</p>
  <p style="font-family:Arial;font-size:9pt;color:#64748b;margin-bottom:12px;">Generated: ${dateStr} &nbsp;&bull;&nbsp; Total Records: ${rows.length}</p>
  <table border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-family:Arial;font-size:9pt;">
    <thead><tr>${headerHtml}</tr></thead>
    <tbody>${bodyHtml || `<tr><td colspan="${headers.length}" style="text-align:center;padding:16px;color:#94a3b8;">No records.</td></tr>`}</tbody>
  </table>
</body>
</html>`;

    const blob = new Blob(['\ufeff' + excelHtml], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_${new Date().toISOString().split('T')[0]}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  },

  /**
   * Download a plain CSV file.
   */
  downloadCSV(filename, headers, rows) {
    let csv = headers.map(h => `"${h}"`).join(',') + '\r\n';
    rows.forEach(row => {
      csv += row.map(cell => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',') + '\r\n';
    });
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  },

  _esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
};
