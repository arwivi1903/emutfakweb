import os
import re

base_dir = '/Applications/XAMPP/xamppfiles/htdocs/prolynweb/p/'

for root, dirs, files in os.walk(base_dir):
    for f in files:
        if not f.endswith('.php'):
            continue
        path = os.path.join(root, f)
        rel_path = os.path.relpath(path, base_dir)
        if '/' not in rel_path or rel_path.startswith('dist/') or rel_path.startswith('classes/') or rel_path.startswith('config/') or rel_path.startswith('assets/') or rel_path.startswith('functions/'):
            continue
            
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        new_content = re.sub(r'action=""', f'action="{rel_path}"', content)
        
        # Also fix any <form method="POST"> that was added but had no action
        def add_action(m):
            tag = m.group(0)
            if 'action=' not in tag:
                return tag.replace('<form', f'<form action="{rel_path}"')
            return tag
            
        new_content = re.sub(r'<form\b[^>]*method=["\']POST["\'][^>]*>', add_action, new_content)
            
        if new_content != content:
            with open(path, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f"Fixed {rel_path}")

