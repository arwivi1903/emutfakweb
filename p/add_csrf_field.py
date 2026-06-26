import os
import re

base_dir = '/Applications/XAMPP/xamppfiles/htdocs/prolynweb/p'

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
    
    if 'csrfTokenField()' in content:
        return False
        
    # Find all <form ... method="POST" ...>
    # Note: method="POST" or method="post"
    modified = False
    
    # We will use regex substitution to insert the CSRF token right after the form opening tag
    def replacer(match):
        nonlocal modified
        modified = True
        return match.group(0) + '\n                            <?= csrfTokenField() ?>'

    # pattern to match <form ... > where method is post
    pattern = re.compile(r'<form[^>]*method=[\'"]post[\'"][^>]*>', re.IGNORECASE)
    
    new_content = pattern.sub(replacer, content)
    
    if modified:
        with open(filepath, 'w') as f:
            f.write(new_content)
        print(f"Added CSRF token to {filepath}")
        return True
    return False

for root, _, files in os.walk(base_dir):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            process_file(filepath)
            
