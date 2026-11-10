#!/usr/bin/env python3
"""Static migration contract check. Does not replace API/database runtime tests."""
from pathlib import Path
import json
import re
import sys

root = Path(__file__).resolve().parents[1]
check = lambda ok, description: print(('PASS' if ok else 'FAIL') + ': ' + description) or ok
passed = []
manifest = json.loads((root / 'composer.json').read_text())
passed.append(check(manifest['require']['php'] == '^8.2', 'PHP 8.2+ requirement'))
passed.append(check(manifest['require']['codeigniter4/framework'] == '^4.7.5', 'Modern CodeIgniter declared'))
front = (root / 'public/index.php').read_text()
passed.append(check('Boot::bootWeb($paths)' in front, 'CodeIgniter 4.7 front controller'))
route = (root / 'app/Config/Routes.php').read_text()
for item in ["'connect', 'Connect::index'", "'register', 'Auth::register'", "'keys'", "'admin'"]:
    passed.append(check(item in route, f'Legacy route {item} exists'))
connect = (root / 'app/Controllers/Connect.php').read_text()
for item in ["getPost('game')", "getPost('user_key')", "getPost('serial')", "'status' => true", "'token'", "'rng'", "'EXP'"]:
    passed.append(check(item in connect, f'Connect contract field {item} retained'))
for filename in ['.env', 'composer.lock', 'vendor/codeigniter4/framework/system/Boot.php', 'Newkeys.txt']:
    passed.append(check(not (root / filename).exists(), f'Bundled sensitive/stale file excluded: {filename}'))
passed.append(check((root / '.env.example').exists(), 'Example configuration provided'))
if not all(passed):
    sys.exit(1)
print(f'PASS: {len(passed)} static compatibility assertions. No runtime behavior was exercised.')
