import os
import re

base_dir = '/Applications/XAMPP/xamppfiles/htdocs/prolynweb/p/'

for root, dirs, files in os.walk(base_dir):
    for f in files:
        if not f.endswith('.php'):
            continue
        path = os.path.join(root, f)
        # We only care about files in subdirectories (excluding p/ itself and maybe dist/)
        rel_path = os.path.relpath(path, base_dir)
        if '/' not in rel_path or rel_path.startswith('dist/') or rel_path.startswith('classes/') or rel_path.startswith('config/') or rel_path.startswith('assets/') or rel_path.startswith('functions/'):
            continue
            
        folder_prefix = os.path.dirname(rel_path) + '/'
        
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        modified = False
        
        # 1. Fix window.location.href='filename.php...' to include folder_prefix
        # Match window.location.href='something.php...' where something doesn't have a slash
        def replace_href(m):
            url = m.group(1)
            # if url already has the folder prefix, or starts with http, ignore
            if '/' not in url and not url.startswith('http'):
                return f"window.location.href='{folder_prefix}{url}'"
            return m.group(0)
            
        new_content = re.sub(r"window\.location\.href='([^']+)'", replace_href, content)
        new_content = re.sub(r'window\.location\.href="([^"]+)"', lambda m: f'window.location.href="{folder_prefix}{m.group(1)}"' if '/' not in m.group(1) and not m.group(1).startswith('http') and not m.group(1).startswith('<?=') else m.group(0), new_content)
        
        if new_content != content:
            modified = True
            content = new_content
            
        # 2. Fix <form method="POST"> or <form action="" to include full path
        def replace_form(m):
            # Check if action is present
            tag = m.group(0)
            if 'action="' not in tag:
                # no action, add action
                return tag.replace('<form', f'<form action="{rel_path}"')
            return tag
            
        new_content = re.sub(r'<form\b[^>]*method=["\']POST["\'][^>]*>', replace_form, content)
        
        # Also fix any <form action="filename.php" ...>
        def replace_form_action(m):
            action_url = m.group(1)
            if '/' not in action_url and action_url.endswith('.php'):
                return f'action="{folder_prefix}{action_url}"'
            return m.group(0)
        
        new_content = re.sub(r'action="([^"]+)"', replace_form_action, new_content)
        
        if new_content != content:
            modified = True
            content = new_content
            
        if modified:
            with open(path, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"Fixed {rel_path}")

