<?php

function create_password($password, $enc = true)
{
    $optn = ['cost' => 11];
    $patt = "XquxmymXDtWRA66D";
    $hash = md5($patt . $password);
    $pass = password_hash($hash, PASSWORD_DEFAULT, $optn);
    return ($enc ? $pass : $hash);
}

function getName($user)
{
    if ($user->fullname) {
        return word_limiter($user->fullname, 1, '');
    } else {
        return $user->username;
    }
}

function getLevel($level = 0)
{
    switch ($level) {
        case '1':
            $a = 'Owner';
            break;
        case '2':
            $a = 'Admin';
            break;
        default:
            $a = 'Reseller';
            break;
    }
    return $a;
}

function setMessage($msg, $color = 'secondary')
{
    return [$msg, $color];
}

function getDevice($devices)
{
    $total = 0;
    $listDevice = "";
    if ($devices) {
        $clean_comma = reduce_multiples($devices, ",", true);
        $ex = explode(',', $clean_comma);
        $listDevice = "";
        foreach ($ex as $ld) {
            $listDevice .= "$ld\n";
        }
        $total = count($ex);
    }
    return (object) ['total' => $total, 'devices' => trim($listDevice)];
}

function setDevice($devicesPost, $max)
{
    if ($devicesPost) {
        $clean_enter = reduce_multiples($devicesPost, "\n", true);
        $ez = [''];
        $ef = array_unique(array_filter(preg_replace("/[^A-Za-z0-9]/", "", explode("\n", $clean_enter))));
        $ex = array_filter(array_merge($ez, $ef));
        foreach ($ex as $k => $item) {
            if ($k <= $max) {
                $result[] = trim($item);
            }
        }
        return implode(",", array_unique($result));
    }
}

function getPrice($price, $duration, $device_max)
{
    $priceReal = $price[$duration];
    $result = ($priceReal * $device_max);
    return ($result <= 0) ? false : $result;
}


function datatables_json(string $table, array $where = [], array $searchColumns = [], array $formatters = [], string $select = '*', array $orderMap = [], string $defaultOrder = '')
{
    $db      = db_connect();
    $request = service('request');

    $draw   = (int) ($request->getGet('draw') ?? 1);
    $search = trim((string) ($request->getGet('search')['value'] ?? ''));
    $start  = max(0, (int) ($request->getGet('start') ?? 0));
    $length = (int) ($request->getGet('length') ?? 10);

    $applyWhere = static function ($builder) use ($where) {
        foreach ($where as $col => $val) {
            $builder->where($col, $val);
        }
    };
    $applySearch = static function ($builder) use ($search, $searchColumns) {
        if ($search === '' || !$searchColumns) {
            return;
        }
        $builder->groupStart();
        foreach ($searchColumns as $col) {
            $builder->orLike($col, $search);
        }
        $builder->groupEnd();
    };

    $total = $db->table($table);
    $applyWhere($total);
    $recordsTotal = $total->countAllResults();

    $filtered = $db->table($table);
    $applyWhere($filtered);
    $applySearch($filtered);
    $recordsFiltered = $filtered->countAllResults();

    $data = $db->table($table);
    $applyWhere($data);
    $applySearch($data);
    $data->select($select);

    $orderIndex = (int) ($request->getGet('order')[0]['column'] ?? 0);
    $orderKey   = (string) ($request->getGet('columns')[$orderIndex]['data'] ?? '');
    $orderDir   = strtolower((string) ($request->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
    $orderBy    = $orderMap[$orderKey] ?? ($orderKey !== '' && in_array($orderKey, $searchColumns, true) ? $orderKey : $defaultOrder);
    if ($orderBy !== '') {
        $data->orderBy($orderBy, $orderDir);
    }
    if ($length > 0) {
        $data->limit($length, $start);
    }

    $rows = $data->get()->getResult();
    foreach ($rows as $row) {
        foreach ($formatters as $col => $callback) {
            $row->$col = $callback($row->$col ?? null, $row);
        }
    }

    return [
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $rows,
    ];
}
