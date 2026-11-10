#!/usr/bin/env python3
"""Read-only smoke test for your deployed SHAI / ASTRAL catalog.
Optionally tests the licence protocol only when PANEL_TEST_KEY is supplied
in the caller's environment; never prints the key or full private response.
"""
import base64
import json
import os
import sys
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urlparse
from urllib.request import Request, urlopen

base = (sys.argv[1] if len(sys.argv) > 1 else 'https://shaii.x10.mx').rstrip('/')
if urlparse(base).scheme != 'https':
    raise SystemExit('FAIL: provide an HTTPS origin')

request = Request(base + '/loader/catalog', headers={'Accept': 'text/plain'})
try:
    with urlopen(request, timeout=15) as response:
        body = response.read(2_000_001)
        if len(body) > 2_000_000:
            raise ValueError('Catalog response exceeded 2 MB')
except (URLError, HTTPError) as error:
    raise SystemExit(f'FAIL: catalog HTTP request failed: {error}')

try:
    catalog = json.loads(base64.b64decode(body.strip(), validate=True))
    assert isinstance(catalog, dict)
    assert isinstance(catalog['DATA'], list)
    assert isinstance(catalog['META'], dict)
    required = {'NAME', 'DOWNLOAD', 'URL', 'ARCH', 'KEY', 'GAME', 'TYPE', 'STATUS'}
    for index, row in enumerate(catalog['DATA']):
        missing = required.difference(row)
        if missing:
            raise ValueError(f'entry {index} missing: {sorted(missing)}')
        if row['URL'] != base + '/connect':
            raise ValueError(f'entry {index} points at unexpected license endpoint')
        if row['ARCH'] not in {'32bit', '64bit'}:
            raise ValueError(f'entry {index} architecture unsupported by Android loader')
        if row['STATUS'] not in {'ACTIVE', 'MAINTENANCE'}:
            raise ValueError(f'entry {index} visibility invalid')
        if urlparse(row['DOWNLOAD']).scheme != 'https':
            raise ValueError(f'entry {index} non-HTTPS download URL')
except (KeyError, ValueError, AssertionError, json.JSONDecodeError) as error:
    raise SystemExit(f'FAIL: catalog response contract: {error}')

print(f"PASS: decoded catalog with {len(catalog['DATA'])} visible entries")

key = os.getenv('PANEL_TEST_KEY')
if key:
    game = os.getenv('PANEL_TEST_GAME', '')
    serial = os.getenv('PANEL_TEST_SERIAL', 'APEX_STAGING_TEST')
    if not game:
        raise SystemExit('FAIL: set PANEL_TEST_GAME when supplying PANEL_TEST_KEY')
    data = urlencode({'game': game, 'user_key': key, 'serial': serial}).encode('ascii')
    req = Request(base + '/connect', data=data, headers={
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json',
    })
    try:
        with urlopen(req, timeout=15) as response:
            result = json.load(response)
        if not isinstance(result.get('status'), bool):
            raise ValueError('status must be a boolean')
        if not result['status']:
            reason = str(result.get('reason', 'Not specified'))
            raise ValueError('test license rejected: ' + reason)
        fields = result.get('data', {})
        if not isinstance(fields.get('token'), str) or not isinstance(fields.get('rng'), int) or not isinstance(fields.get('EXP'), str):
            raise ValueError('valid license response missing token/rng/EXP')
        print('PASS: license accepted with expected response schema')
    except (HTTPError, URLError, ValueError, KeyError, json.JSONDecodeError) as error:
        raise SystemExit(f'FAIL: license validation contract: {error}')
else:
    print('SKIP: license validation, no PANEL_TEST_KEY supplied')
