import os
import re

# Mapping of file patterns to their required permission
permissions_map = {
    'crm/tenants.php': 'manage_tenants',
    'crm/tenant_detail.php': 'manage_tenants',
    'crm/subscriptions.php': 'manage_subscriptions',
    'crm/coupons.php': 'manage_subscriptions',
    'finance/invoices.php': 'manage_billing',
    'finance/payments.php': 'manage_billing',
    'finance/taxes.php': 'manage_billing',
    'settings/general.php': 'manage_settings',
    'settings/api_keys.php': 'manage_settings',
    'settings/package_features.php': 'manage_settings',
    'settings/admins.php': 'manage_admins',
    'settings/roles.php': 'manage_roles',
    'crm/tickets.php': 'manage_support',
    'crm/ticket_detail.php': 'manage_support',
}

# Any other file in logs gets 'view_logs'
# Any other file in settings gets 'manage_settings'
# Any other file in finance gets 'manage_billing'

def get_permission(filepath):
    rel_path = filepath.split('/prolynweb/p/')[-1]
    
    if rel_path in permissions_map:
        return permissions_map[rel_path]
    
    if rel_path.startswith('logs/'):
        return 'view_logs'
    if rel_path.startswith('settings/'):
        return 'manage_settings'
    if rel_path.startswith('finance/'):
        return 'manage_billing'
    
    return None

base_dir = '/Applications/XAMPP/xamppfiles/htdocs/prolynweb/p'
dirs_to_check = ['crm', 'finance', 'logs', 'settings', 'content']

for d in dirs_to_check:
    dir_path = os.path.join(base_dir, d)
    if not os.path.isdir(dir_path):
        continue
        
    for root, _, files in os.walk(dir_path):
        for file in files:
            if file.endswith('.php'):
                filepath = os.path.join(root, file)
                permission = get_permission(filepath)
                
                if permission:
                    with open(filepath, 'r') as f:
                        content = f.read()
                    
                    if 'Auth::requireModule' not in content:
                        # Find require_once '../config/header.php' or similar
                        # and insert Auth::requireModule immediately after
                        match = re.search(r'(require_once.*?header\.php[\'"];?)', content)
                        if match:
                            replacement = f"{match.group(1)}\nAuth::requireModule('{permission}');"
                            content = content[:match.start()] + replacement + content[match.end():]
                            
                            with open(filepath, 'w') as f:
                                f.write(content)
                            print(f"Added Auth::requireModule('{permission}') to {filepath}")
