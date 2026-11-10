#!/bin/bash
# Scenario tests for the /connect endpoint.
#
# They call the panel the way the Android client does and check the JSON answers
# against the contract in ANALYSIS.md (section 6.4.1).
#
# Needs: a MySQL/MariaDB server the panel can reach, the tables from
# sql/xyto-update.sql, php (to serve the panel) and python3 (client simulator).
#
# Usage:
#   PANEL_URL=http://127.0.0.1:8080 bash tools/connect_scenarios.sh
#
# The tests create and delete keys directly in the database, so pass credentials
# through the environment: DB_HOST DB_PORT DB_USER DB_PASS DB_NAME

set -u

PANEL_URL="${PANEL_URL:-http://127.0.0.1:8080}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-jqdmcnvd_shaii}"

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SIM="python3 $HERE/connect_client_sim.py"
S1="11111111-1111-1111-1111-111111111111"
S2="22222222-2222-2222-2222-222222222222"

mysql_do() {
  if [ -n "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -N -B "$DB_NAME" -e "$1"
  else
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -N -B "$DB_NAME" -e "$1"
  fi
}

FAILED=0
reset() { mysql_do "delete from keys_code where user_key like 'TEST-%';" >/dev/null 2>&1; }
run()  {
  desc="$1"; key="$2"; ser="$3"
  printf '%-46s ' "$desc"
  out="$($SIM "$PANEL_URL" CODMGR "$key" "$ser" | head -2 | tr '\n' ' ')"
  echo "$out"
  LAST_RESULT="$out"
}
expect_ok()   { run "$1" "$2" "$3"; echo "$LAST_RESULT" | grep -q "LOGIN OK"   || FAILED=1; }
expect_fail() { run "$1" "$2" "$3"; echo "$LAST_RESULT" | grep -q "LOGIN FAILED" || FAILED=1; }

echo "panel: $PANEL_URL"
echo

reset
echo "--- S1 valid key, device already bound"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-VALID',30,date_add(now(),interval 30 day),2,'$S1',1,'tester');" >/dev/null
expect_ok "valid key, same serial" TEST-VALID "$S1"

echo "--- S2 valid key, new serial (device slot added)"
expect_ok "valid key, new serial" TEST-VALID "$S2"
echo "   devices column now: $(mysql_do "select devices from keys_code where user_key='TEST-VALID';")"

echo "--- S3 fresh key (expired_date NULL)"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-FRESH',7,NULL,1,NULL,1,'tester');" >/dev/null
expect_ok "fresh key, first login" TEST-FRESH "$S1"
echo "   expired_date now: $(mysql_do "select expired_date from keys_code where user_key='TEST-FRESH';")"

echo "--- S4 same key again (expiry set -> isBefore path)"
expect_ok "fresh key, second login" TEST-FRESH "$S1"

echo "--- S5 expired key"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-EXPIRED',1,date_sub(now(),interval 2 day),1,NULL,1,'tester');" >/dev/null
expect_fail "expired key" TEST-EXPIRED "$S1"

echo "--- S6 unknown key"
expect_fail "unknown key" TEST-NOPE "$S1"

echo "--- S7 max devices reached"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-FULL',30,date_add(now(),interval 30 day),1,'$S1',1,'tester');" >/dev/null
expect_fail "max devices reached" TEST-FULL "$S2"

echo "--- S8 suspended key"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-SUSP',30,date_add(now(),interval 30 day),1,NULL,0,'tester');" >/dev/null
expect_fail "suspended key" TEST-SUSP "$S1"

echo "--- S9 maintenance mode ON"
mysql_do "update onoff set status='on' where id=1;" >/dev/null
expect_fail "maintenance on" TEST-VALID "$S1"
mysql_do "update onoff set status='off' where id=1;" >/dev/null

echo "--- S10 mod tables missing (modname + mod_settings dropped)"
mysql_do "rename table modname to modname_tmp, mod_settings to mod_settings_tmp;" >/dev/null
expect_ok "mod tables missing" TEST-VALID "$S1"
mysql_do "rename table modname_tmp to modname, mod_settings_tmp to mod_settings;" >/dev/null

echo "--- S11 key with a dash/underscore key format (alpha_dash)"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-a_b-C9',30,date_add(now(),interval 30 day),1,NULL,1,'tester');" >/dev/null
expect_ok "key with underscore" TEST-a_b-C9 "$S1"

echo "--- S12 Feature table missing"
mysql_do "insert into keys_code (game,user_key,duration,expired_date,max_devices,devices,status,registrator) values ('CODMGR','TEST-FEAT',30,date_add(now(),interval 30 day),1,NULL,1,'tester');" >/dev/null
mysql_do "rename table Feature to Feature_tmp;" >/dev/null
expect_ok "Feature table missing" TEST-FEAT "$S1"
mysql_do "rename table Feature_tmp to Feature;" >/dev/null

echo "--- S13 keys_code table missing (model query)"
mysql_do "rename table keys_code to keys_code_tmp;" >/dev/null
expect_fail "keys_code table missing" TEST-ANY "$S1"
mysql_do "rename table keys_code_tmp to keys_code;" >/dev/null

echo "--- S14 _ftext table missing"
mysql_do "rename table _ftext to _ftext_tmp;" >/dev/null
expect_ok "_ftext table missing" TEST-FEAT "$S1"
mysql_do "rename table _ftext_tmp to _ftext;" >/dev/null

reset
echo
if [ "$FAILED" -eq 0 ]; then
  echo "ALL SCENARIOS PASSED"
else
  echo "SOME SCENARIOS FAILED"
fi
exit "$FAILED"
