#!/usr/bin/env python3
"""Faithful simulator of VARIANT4's Login() (src/main/jni/Includes/Jni_Stuff.h).

Mimics nlohmann::json semantics, because the Android client is strict about
JSON types and any mismatch aborts the login:

  result["status"] == true   -> must be the boolean true
  result["data"]["token"]    -> .get<std::string>()  -> must be a JSON string
  result["data"]["rng"]      -> .get<time_t>()       -> must be a JSON number
  result["data"]["EXP"]      -> .get<std::string>()  -> must be a JSON string
  token == md5("<game>-<key>-<serial>-Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E")
  rng + 30 > time(0)

Anything that throws in C++ lands in the catch(json::exception) branch, and
the user sees the raw response body as the error message.

Usage:
  python3 tools/connect_client_sim.py https://panel.example.com CODMGR <key> <serial>
"""
import hashlib
import json
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

STATIC_WORD = "Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E"


def main() -> int:
    base = sys.argv[1].rstrip("/")
    game = sys.argv[2]
    key = sys.argv[3]
    serial = sys.argv[4]

    body = urllib.parse.urlencode({"game": game, "user_key": key, "serial": serial}).encode()
    req = urllib.request.Request(
        base + "/connect",
        data=body,
        headers={"Content-Type": "application/x-www-form-urlencoded"},
    )
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            raw = resp.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as exc:
        raw = exc.read().decode("utf-8", "replace")
        print(f"HTTP {exc.code}")
        print("BODY:", raw[:600])
        return 10
    except Exception as exc:  # noqa: BLE001
        print("TRANSPORT ERROR:", exc)
        return 11

    try:
        result = json.loads(raw)
    except Exception as exc:  # noqa: BLE001
        print("json::parse_error:", exc)
        print("BODY:", raw[:600])
        return 2

    if result.get("status") is not True:
        reason = result.get("reason")
        if not isinstance(reason, str):
            print("json::type_error: reason is", type(reason).__name__, "->", repr(reason))
            print("BODY:", raw[:600])
            return 3
        print("LOGIN FAILED, reason =", reason)
        return 6

    data = result.get("data")
    if not isinstance(data, dict):
        print("json::type_error: data is", type(data).__name__)
        print("BODY:", raw[:600])
        return 4
    for field in ("token", "rng", "EXP"):
        if field not in data:
            print(f"json::out_of_range: data.{field} missing")
            print("BODY:", raw[:600])
            return 5
    token = data["token"]
    rng = data["rng"]
    exp = data["EXP"]
    if not isinstance(token, str):
        print(f"json::type_error: token is {type(token).__name__} -> {token!r}")
        return 7
    if not isinstance(rng, int) or isinstance(rng, bool):
        print(f"json::type_error: rng is {type(rng).__name__} -> {rng!r}")
        return 8
    if not isinstance(exp, str):
        print(f"json::type_error: EXP is {type(exp).__name__} -> {exp!r}")
        return 9

    if rng + 30 <= int(time.time()):
        print("RNG STALE:", rng, "-> bValid stays false")
        return 12

    auth = f"{game}-{key}-{serial}-{STATIC_WORD}"
    expected = hashlib.md5(auth.encode()).hexdigest()
    if token != expected:
        print("TOKEN MISMATCH")
        print("  panel :", token)
        print("  client:", expected)
        return 13

    print(f"LOGIN OK  token={token}  EXP={exp}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
