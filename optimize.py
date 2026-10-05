import os
import re

directories = [
    r'c:\laragon\www\kiron-backend\resources\views\template1',
    r'c:\laragon\www\kiron-backend\resources\views\template2',
    r'c:\laragon\www\kiron-backend\resources\views\template3',
    r'c:\laragon\www\kiron-backend\resources\views\template4',
    r'c:\laragon\www\kiron-backend\resources\views\template5',
]

def fix_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # 1. FontAwesome: defer loading
    fa_regex = re.compile(r'<link[^>]*href="[^"]*font-awesome[^"]*"[^>]*>')
    
    def repl_fa(match):
        tag = match.group(0)
        if 'media="print"' in tag or 'preload' in tag:
            return tag
        # Add media="print" onload="this.media='all'"
        tag = tag.replace('>', ' media="print" onload="this.media=\'all\'">')
        return tag

    content = fa_regex.sub(repl_fa, content)

    # 2. Add loading="lazy", width="800", height="800" to img tags
    img_regex = re.compile(r'<img\s+([^>]+)>', re.IGNORECASE)

    def repl_img(match):
        attrs = match.group(1)
        
        # Don't lazy load if eager or fetchpriority=high
        needs_lazy = 'loading=' not in attrs and 'fetchpriority="high"' not in attrs
        needs_width = 'width=' not in attrs
        needs_height = 'height=' not in attrs
        
        new_attrs = attrs
        if needs_lazy:
            new_attrs += ' loading="lazy"'
        if needs_width:
            new_attrs += ' width="800"'
        if needs_height:
            new_attrs += ' height="800"'
            
        return f'<img {new_attrs.strip()}>'

    content = img_regex.sub(repl_img, content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {filepath}")

for d in directories:
    for root, dirs, files in os.walk(d):
        for file in files:
            if file.endswith('.blade.php'):
                fix_file(os.path.join(root, file))

print("Done.")
