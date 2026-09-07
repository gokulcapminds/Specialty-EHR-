$cssContent = @"

/* EXTRACTED CLASSES FROM JS */
.card-summary-box { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); flex-wrap: wrap; gap: 12px; }
.empty-state-box { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 24px; text-align: center; color: var(--text-secondary); }
.bg-tertiary { background-color: var(--bg-tertiary); }
.note-content-box { background-color: var(--bg-tertiary); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); font-size: 0.95rem; line-height: 1.5; }
.btn-primary-inline { background-color: var(--primary-color); color: #fff; font-weight: 700; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
.btn-remove-icon { border: none; background: none; color: #d9534f; cursor: pointer; font-size: 0.8rem; font-weight: 700; padding: 2px 6px; }
.signature-line { border-top: 1px solid #000; padding-top: 5px; width: 100%; }
.print-footer { border-top: none; text-align: left; margin-top: 10px; }
.print-footer-flex { border-top: none; text-align: left; margin-top: 10px; display: flex; flex-direction: column; justify-content: flex-end; }
.text-brand-blue { color: #0284c7; }
.text-danger-bold { color: #b00000; font-weight: bold; }
.text-primary-color { color: var(--text-primary); }
.text-primary-sm { color: var(--text-primary); font-size: 0.95rem; }
.text-primary-md { color: var(--text-primary); font-size: 1.05rem; }
.text-primary-base { color: var(--text-primary); font-size: 1rem; }
.text-secondary-sm { color: var(--text-secondary); font-size: 0.85rem; }
.label-secondary-block { color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 4px; }
.text-secondary-md { color: var(--text-secondary); font-size: 0.9rem; font-weight: 500; }
.text-danger-mt8 { color: var(--text-secondary); margin-top: 8px; font-weight: 600; color: #d9534f; }
.label-uppercase-sm { display: block; font-size: 0.78rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; }
.d-flex-gap-8 { display: flex; align-items: center; gap: 8px; }
.d-flex-col-gap-10-mt6 { display: flex; flex-direction: column; gap: 10px; margin-top: 6px; }
.d-flex-col-gap-16-left { display: flex; flex-direction: column; gap: 16px; text-align: left; }
.d-flex-col-gap-2 { display: flex; flex-direction: column; gap: 2px; }
.d-flex-col-gap-6 { display: flex; flex-direction: column; gap: 6px; }
.d-flex-gap-8-only { display: flex; gap: 8px; }
.header-box-sm { display: flex; justify-content: space-between; align-items: center; background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 4px; }
.header-box-md { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background-color: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 8px; }
.border-bottom-pb10 { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); }
.border-bottom-pb12 { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); }
.grid-2col-box { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: var(--bg-tertiary); padding: 14px; border-radius: 10px; }
.text-mono-bold { font-family: monospace; font-weight: 600; color: var(--text-primary); }
.text-xs { font-size: 0.75rem; }
.badge-primary-xs { font-size: 0.75rem; background: var(--primary-color); color: #fff; padding: 2px 8px; border-radius: 12px; font-weight: 600; }
.text-xs-italic { font-size: 0.75rem; color: var(--text-secondary); font-style: italic; }
.text-sm-alt { font-size: 0.82rem; color: var(--text-secondary); }
.text-sm { font-size: 0.85rem; }
.text-sm-secondary { font-size: 0.85rem; color: var(--text-secondary); }
.text-sm-secondary-mt6 { font-size: 0.85rem; color: var(--text-secondary); margin-top: 6px; }
.badge-tertiary-sm { font-size: 0.8rem; background: var(--bg-tertiary); color: var(--primary-color); }
.text-xs-secondary { font-size: 0.8rem; color: var(--text-secondary); }
.text-xs-secondary-italic { font-size: 0.8rem; color: var(--text-secondary); text-align: center; font-style: italic; }
.text-xs-primary-bold { font-size: 0.8rem; font-weight: 700; color: var(--text-primary); }
.text-sm-primary-bold-alt { font-size: 0.95rem; font-weight: 600; margin-bottom: 16px; color: var(--text-secondary); }
.text-lg-secondary { font-size: 1.1rem; color: var(--text-secondary); }
.text-lg-primary-bold { font-size: 1.1rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary); }
.text-base-primary-mb8 { font-size: 1rem; color: var(--text-primary); margin-bottom: 8px; }
.text-3xl-secondary { font-size: 3rem; color: var(--text-secondary); }
.font-weight-600 { font-weight: 600; }
.text-primary-semibold { font-weight: 600; color: var(--text-primary); }
.text-brand-bold { font-weight: 700; color: var(--primary-color); font-size: 0.95rem; }
.text-primary-bold-sm { font-weight: 700; color: var(--text-primary); font-size: 0.95rem; }
.badge-status { font-weight: 700; width: 180px; padding: 6px 10px; }
.text-brand-blue-bold { font-weight: bold; color: #0284c7; }
.grid-col-span-2 { grid-column: span 2; }
.h-45 { height: 45px; }
.signature-font { height: 45px; font-family: 'Courier New', monospace; font-size: 1.4rem; font-style: italic; color: #333; }
.modal-title-primary { margin: 0; font-size: 1.2rem; color: var(--text-primary); }
.mb-12 { margin-bottom: 12px; }
.mr-6 { margin-right: 6px; }
.mt-1rem-primary { margin-top: 1rem; color: var(--text-primary); }
.py-10 { padding: 10px 0; }
.btn-inline-flex { padding: 10px 16px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; }
.alert-primary-box { padding: 14px; background: rgba(2, 132, 199, 0.08); border: 1px solid var(--primary-color); border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.badge-sm-rounded { padding: 4px 8px; font-size: 0.85rem; border-radius: 6px; }
.btn-sm-inline { padding: 5px 12px; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
.p-6-12-sm { padding: 6px 12px; font-size: 0.8rem; }
.btn-success-sm { padding: 6px 12px; font-size: 0.8rem; background-color: #28a745; border-color: #28a745; }
.p-6-14-bold { padding: 6px 14px; font-weight: 600; }
.empty-state-dashed { text-align: center; padding: 48px; border: 1px dashed var(--border-color); border-radius: 12px; background: var(--bg-secondary); margin-top: 1.5rem; grid-column: 1 / -1; }
.p-2rem-center { text-align:center; padding: 2rem; }
.text-pre-wrap-min { white-space: pre-wrap; min-height: 80px; }
.w-30 { width: 30%; }
.w-30-danger { width: 30%; color: #b00000; font-weight: bold; }
"@

Add-Content -Path "c:\wamp64\www\pf_ehr\public\css\theme.css" -Value $cssContent

$mappings = @{
    'style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); flex-wrap: wrap; gap: 12px;"' = 'class="card-summary-box"'
    'style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 24px; text-align: center; color: var(--text-secondary);"' = 'class="empty-state-box"'
    'style="background-color: var(--bg-tertiary);"' = 'class="bg-tertiary"'
    'style="background-color: var(--bg-tertiary); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); font-size: 0.95rem; line-height: 1.5;"' = 'class="note-content-box"'
    'style="background-color: var(--primary-color); color: #fff; font-weight: 700; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;"' = 'class="btn-primary-inline"'
    'style="border: none; background: none; color: #d9534f; cursor: pointer; font-size: 0.8rem; font-weight: 700; padding: 2px 6px;"' = 'class="btn-remove-icon"'
    'style="border-top: 1px solid #000; padding-top: 5px; width: 100%;"' = 'class="signature-line"'
    'style="border-top: none; text-align: left; margin-top: 10px;"' = 'class="print-footer"'
    'style="border-top: none; text-align: left; margin-top: 10px; display: flex; flex-direction: column; justify-content: flex-end;"' = 'class="print-footer-flex"'
    'style="color: #0284c7;"' = 'class="text-brand-blue"'
    'style="color: #b00000; font-weight: bold;"' = 'class="text-danger-bold"'
    'style="color: var(--text-primary);"' = 'class="text-primary-color"'
    'style="color: var(--text-primary); font-size: 0.95rem;"' = 'class="text-primary-sm"'
    'style="color: var(--text-primary); font-size: 1.05rem;"' = 'class="text-primary-md"'
    'style="color: var(--text-primary); font-size: 1rem;"' = 'class="text-primary-base"'
    'style="color: var(--text-secondary); font-size: 0.85rem;"' = 'class="text-secondary-sm"'
    'style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 4px;"' = 'class="label-secondary-block"'
    'style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;"' = 'class="text-secondary-md"'
    'style="color: var(--text-secondary); margin-top: 8px; font-weight: 600; color: #d9534f;"' = 'class="text-danger-mt8"'
    'style="display: block; font-size: 0.78rem; color: var(--text-secondary); font-weight: 600; text-uppercase;"' = 'class="label-uppercase-sm"'
    'style="display: flex; align-items: center; gap: 8px;"' = 'class="d-flex-gap-8"'
    'style="display: flex; flex-direction: column; gap: 10px; margin-top: 6px;"' = 'class="d-flex-col-gap-10-mt6"'
    'style="display: flex; flex-direction: column; gap: 16px; text-align: left;"' = 'class="d-flex-col-gap-16-left"'
    'style="display: flex; flex-direction: column; gap: 2px;"' = 'class="d-flex-col-gap-2"'
    'style="display: flex; flex-direction: column; gap: 6px;"' = 'class="d-flex-col-gap-6"'
    'style="display: flex; gap: 8px;"' = 'class="d-flex-gap-8-only"'
    'style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 4px;"' = 'class="header-box-sm"'
    'style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background-color: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 8px;"' = 'class="header-box-md"'
    'style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);"' = 'class="border-bottom-pb10"'
    'style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);"' = 'class="border-bottom-pb12"'
    'style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: var(--bg-tertiary); padding: 14px; border-radius: 10px;"' = 'class="grid-2col-box"'
    'style="font-family: monospace; font-weight: 600; color: var(--text-primary);"' = 'class="text-mono-bold"'
    'style="font-size: 0.75rem;"' = 'class="text-xs"'
    'style="font-size: 0.75rem; background: var(--primary-color); color: #fff; padding: 2px 8px; border-radius: 12px; font-weight: 600;"' = 'class="badge-primary-xs"'
    'style="font-size: 0.75rem; color: var(--text-secondary); font-style: italic;"' = 'class="text-xs-italic"'
    'style="font-size: 0.82rem; color: var(--text-secondary);"' = 'class="text-sm-alt"'
    'style="font-size: 0.85rem;"' = 'class="text-sm"'
    'style="font-size: 0.85rem; color: var(--text-secondary);"' = 'class="text-sm-secondary"'
    'style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 6px;"' = 'class="text-sm-secondary-mt6"'
    'style="font-size: 0.8rem; background: var(--bg-tertiary); color: var(--primary-color);"' = 'class="badge-tertiary-sm"'
    'style="font-size: 0.8rem; color: var(--text-secondary);"' = 'class="text-xs-secondary"'
    'style="font-size: 0.8rem; color: var(--text-secondary); text-align: center; font-style: italic;"' = 'class="text-xs-secondary-italic"'
    'style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary);"' = 'class="text-xs-primary-bold"'
    'style="font-size: 0.95rem; font-weight: 600; margin-bottom: 16px; color: var(--text-secondary);"' = 'class="text-sm-primary-bold-alt"'
    'style="font-size: 1.1rem; color: var(--text-secondary);"' = 'class="text-lg-secondary"'
    'style="font-size: 1.1rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary);"' = 'class="text-lg-primary-bold"'
    'style="font-size: 1rem; color: var(--text-primary); margin-bottom: 8px;"' = 'class="text-base-primary-mb8"'
    'style="font-size: 3rem; color: var(--text-secondary);"' = 'class="text-3xl-secondary"'
    'style="font-weight: 600;"' = 'class="font-weight-600"'
    'style="font-weight: 600; color: var(--text-primary);"' = 'class="text-primary-semibold"'
    'style="font-weight: 700; color: var(--primary-color); font-size: 0.95rem;"' = 'class="text-brand-bold"'
    'style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;"' = 'class="text-primary-bold-sm"'
    'style="font-weight: 700; width: 180px; padding: 6px 10px;"' = 'class="badge-status"'
    'style="font-weight: bold; color: #0284c7;"' = 'class="text-brand-blue-bold"'
    'style="grid-column: span 2;"' = 'class="grid-col-span-2"'
    'style="height: 45px;"' = 'class="h-45"'
    'style="height: 45px; font-family: ''Courier New'', monospace; font-size: 1.4rem; font-style: italic; color: #333;"' = 'class="signature-font"'
    'style="margin: 0; font-size: 1.2rem; color: var(--text-primary);"' = 'class="modal-title-primary"'
    'style="margin-bottom: 12px;"' = 'class="mb-12"'
    'style="margin-right: 6px;"' = 'class="mr-6"'
    'style="margin-top: 1rem; color: var(--text-primary);"' = 'class="mt-1rem-primary"'
    'style="padding: 10px 0;"' = 'class="py-10"'
    'style="padding: 10px 16px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem;"' = 'class="btn-inline-flex"'
    'style="padding: 14px; background: rgba(2, 132, 199, 0.08); border: 1px solid var(--primary-color); border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;"' = 'class="alert-primary-box"'
    'style="padding: 4px 8px; font-size: 0.85rem; border-radius: 6px;"' = 'class="badge-sm-rounded"'
    'style="padding: 5px 12px; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;"' = 'class="btn-sm-inline"'
    'style="padding: 6px 12px; font-size: 0.8rem;"' = 'class="p-6-12-sm"'
    'style="padding: 6px 12px; font-size: 0.8rem; background-color: #28a745; border-color: #28a745;"' = 'class="btn-success-sm"'
    'style="padding: 6px 14px; font-weight: 600;"' = 'class="p-6-14-bold"'
    'style="text-align: center; padding: 48px; border: 1px dashed var(--border-color); border-radius: 12px; background: var(--bg-secondary); margin-top: 1.5rem; grid-column: 1 / -1;"' = 'class="empty-state-dashed"'
    'style="text-align:center; padding: 2rem;"' = 'class="p-2rem-center"'
    'style="white-space: pre-wrap; min-height: 80px;"' = 'class="text-pre-wrap-min"'
    'style="width: 30%;"' = 'class="w-30"'
    'style="width: 30%; color: #b00000; font-weight: bold;"' = 'class="w-30-danger"'
}

$appJsPath = "c:\wamp64\www\pf_ehr\public\js\app.js"
$content = Get-Content -Path $appJsPath -Raw

# We need to handle tags that already have a class and add our new class to it.
# E.g. class="some class" style="..." -> class="some class new-class"
# Or just replace style="..." if there's no class.

foreach ($key in $mappings.Keys) {
    $val = $mappings[$key]
    $className = $val -replace 'class="', '' -replace '"', ''
    
    # regex to find class="..." and style="..."
    # It's tricky to do correctly with regex in PS. Let's just do a simple replace first:
    # We can use a script block with regex to merge classes if both exist
    # A safer approach for now: replace $key with $val, then we might end up with class="..." class="..."
    # We can do a second pass to merge adjacent class attributes: class="a" class="b" -> class="a b"
    
    $content = $content.Replace($key, $val)
}

# Fix duplicate class attributes: class="badge-role" class="badge-tertiary-sm" -> class="badge-role badge-tertiary-sm"
# We can use regex replacement for this:
$content = [System.Text.RegularExpressions.Regex]::Replace($content, 'class="([^"]+)"\s*class="([^"]+)"', 'class="$1 $2"')
$content = [System.Text.RegularExpressions.Regex]::Replace($content, 'class="([^"]+)"\s*class="([^"]+)"', 'class="$1 $2"') # twice just in case

# What about text-uppercase in the CSS? We need to fix the invalid CSS 'text-uppercase;' in one style.
$content = $content -replace "text-uppercase;", "text-transform: uppercase;"

Set-Content -Path $appJsPath -Value $content -NoNewline
