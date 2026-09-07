import re
import os
import json

app_js_path = r'c:\wamp64\www\pf_ehr\public\js\app.js'
css_path = r'c:\wamp64\www\pf_ehr\public\css\theme.css'

with open(app_js_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Find all style="..." 
style_pattern = re.compile(r'style="([^"]+)"')
matches = style_pattern.findall(content)

unique_styles = list(set(matches))
print(f"Found {len(unique_styles)} unique styles.")

# For now, let's just print them to see what they are
for i, style in enumerate(unique_styles):
    print(f"Style {i}: {style}")
