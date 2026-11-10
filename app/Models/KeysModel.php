<?php

namespace App\Models;

use CodeIgniter\Model;

class KeysModel extends Model
{
    protected $table      = 'keys_code';
    protected $primaryKey = 'id_keys';
    protected $allowedFields = ['game', 'user_key', 'duration', 'expired_date', 'max_devices', 'devices', 'status', 'registrator'];

    protected $useTimestamps = true;

    public function deleteKey($user_key, string $username)
    {
        $result = false;

        $key = $this->where('user_key', $user_key)->where('registrator', $username)
            ->get()
            ->getRowObject();

        if ($key != null) {
            $result = $this->delete($key->id_keys);
        }

        return $result;
    }

    public function deleteKeysAll()
    {
        $user = (new UserModel())->getUser();
        if (! $user) {
            return false;
        }
        return $this->where('registrator', $user->username)->delete();
    }

    public function getKeys($key = false, $where = 'user_key')
    {
        return $this->where($where, $key)
            ->get()
            ->getRowObject();
    }

    public function getKeysGame($where)
    {
        return $this->where($where)
            ->get()
            ->getRowObject();
    }

    public function API_getKeys()
    {
        $userModel = new UserModel();
        $user = $userModel->getUser();

        if (! $user) {
            return ['data' => []];
        }
        $where = ['registrator' => $user->username];

        $formatters = [
            'status' => function ($value) {
                return ($value ? "Active" : "Inactive");
            },
            'duration' => function ($value) {
                if ($value == 1) {
                    return "$value Day";
                } else {
                    $days = $value / 24;
                    return "$days Days";
                }
            },
            'devices' => function ($value) {
                if ($value) {
                    $e = explode(',', reduce_multiples($value, ",", true));
                }
                return $value ? count($e) : 0;
            },
            'expired' => function ($value) {
                $time = new \CodeIgniter\I18n\Time;
                return $value ? $time::parse($value)->toLocalizedString('d MMM yy - H:m') : '';
            },
        ];

        return datatables_json(
            $this->table,
            $where,
            ['id_keys', 'game', 'user_key', 'duration', 'expired_date', 'max_devices', 'devices', 'registrator'],
            $formatters,
            'id_keys as id, game, user_key, duration, expired_date as expired, max_devices, devices, status, registrator',
            ['id' => 'id_keys', 'expired' => 'expired_date', 'duration' => 'duration'],
            'id_keys'
        );
    }
}
