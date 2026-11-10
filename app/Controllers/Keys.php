<?php

namespace App\Controllers;

use App\Models\HistoryModel;
use App\Models\KeysModel;
use App\Models\UserModel;
use Config\Services;

class Keys extends BaseController
{
    protected $userModel, $model, $user, $userId, $time, $game_list, $duration, $price, $bulk_key;
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->user = $this->userModel->getUser();
        $this->model = new KeysModel();
        $this->time = new \CodeIgniter\I18n\Time;
        $user = $this->user;
        $this->userId = session()->get('userid');
$this->game_list = \App\Libraries\GameTypes::BASE;
        $this->duration = [            
            1 => '1 day, $1 per device',
            2 => '2 days, $2 per device',
            3 => '3 days, $3 per device',
            7 => '7 days, $7 per device',
            15 => '15 days, $15 per device',
            30 => '30 days, $30 per device',
            60 => '60 days, $60 per device',
            90 => '90 days, $90 per device',
            239976 => 'Unlimited days, $1000 per device'
        ];
        
        $this->price = [
           1 => 1,
           2 => 2,
           3 => 3,
           7 => 7,
           15 => 15,
           30 => 30,
           60 => 60,
           90 => 90,
           239976 => 1000,
        ];
     if(in_array((int)($user->level ?? 0), [1, 2], true))
     {
        $this->bulk_key = [
           1 => '1 Key',
           4 => '4 Keys',
           8 => '8 Keys',
           10 => '10 Keys',
           20 => '20 Keys',
           50 => '50 Keys',
           100 => '100 Keys',
           200 => '200 Keys',
           500 => '500 Keys',
           1000 => '1000 Keys',
        ];
     } else {
        $this->bulk_key = [
           1 => '1 Key',
           4 => '4 Keys',
           8 => '8 Keys',
        ];
     }
    }
    public function index()
    {
        $model = $this->model;
        $user = $this->user;

        $keys = $model->where('registrator', $user->username)->findAll();
        $data = [
            'title' => 'Keys',
            'user' => $user,
            'keylist' => $keys,
            'time' => $this->time,
        ];
        return view('Keys/list', $data);
    }
    
public function resetDeviceKey()
{
    $model = $this->model;
    $model->set('devices', NULL)->where('registrator', $this->user->username)->update();
    return redirect()->to('keys');
}

    
public function deleteallkeygen(){
    $user = $this->user;
    $model = $this->model;
    $keygen = $user->username;
    $data = $model->where('registrator', $keygen)->delete();
    return redirect()->back()->with('msgSuccess', 'success');
}

public function Unusedkeygen(){
    echo  date('Y-m-d H:i:s');
    $model=$this->model;
    $keygenuser = $this->user->username;
    $keys=$model->where('expired_date ='.null)->where('registrator =', $keygenuser)->delete();
    return redirect()->back()->with('msgSuccess', 'success');
}
public function deleteEXPkeygen() {
    $user = $this->user;
    $model = $this->model;
    $usernamekeygen = $user->username;

    $model->where('registrator', $usernamekeygen)->where('expired_date <', date('Y-m-d H:i:s'))->delete();

    return redirect()->back()->with('msgSuccess', 'success');
}

public function deleteBLOCKKeys() {
    $user = $this->user;
    $model = $this->model;
    $usernameBL = $user->username;
    
    $data = $model->where('status', '0')->where('registrator', $usernameBL)->delete();

    if ($data) {
        return redirect()->back()->with('msgSuccess', 'success');
    } else {
        return redirect()->back()->with('msgError', 'Failed to delete keys');
    }
}

public function deleteEXPKeys() {
    $user = $this->user;
    $model = $this->model;
    $usernameexp = $user->username;

    $model->where('registrator', $usernameexp)->where('expired_date <', date('Y-m-d H:i:s'))->delete();

    return redirect()->back()->with('msgSuccess', 'success');
}

public function download_all_Keys()
{
    $keys = $this->model->select('user_key')->where('registrator', $this->user->username)->findAll();
    $data = implode("\n", array_map(static function ($row) { return $row['user_key']; }, $keys));
    return $this->response->download('Allkeys.txt', $data === '' ? '' : $data . "\n")
        ->setFileName('Allkeys.txt');
}

public function download_new_Keys()
{
    return $this->download_all_Keys();
}

public function alterKeys(){
    $model=$this->model;
    $user = $this->user;
    $data = $model->where('registrator =', $user->username)->where('expired_date <',  date('Y-m-d H:i:s'))->delete();

    return redirect()->back()->with('msgSuccess', 'success');
}
public function deleteKeys(){
    $user = $this->user;
    $model=$this->model;
    $username = $user->username;
    $data=$model->where('registrator =', $username)->delete();
    return redirect()->back()->with('msgSuccess', 'success');
}
public function startDate(){
    echo  date('Y-m-d H:i:s');
    $model=$this->model;
    $user = $this->user;
    $keys=$model->where('expired_date ='.null)->where('registrator =', $user->username)->delete();
    return redirect()->back()->with('msgSuccess', 'success');
}
    public function api_get_keys()
    {
        $model = $this->model;
        return $this->response->setJSON($model->API_getKeys());
    }
    public function api_key_reset()
    {
        sleep(1);
        $model = $this->model;
        $keys = $this->request->getGet('userkey');
        $reset = $this->request->getGet('reset');
        $db_key = $model->where('registrator', $this->user->username)->getKeys($keys);
        $rules = [];
        if ($db_key) {
            $total = $db_key->devices ? explode(',', $db_key->devices) : [];
            $rules = ['devices_total' => count($total), 'devices_max' => (int) $db_key->max_devices];
            $user = $this->user;
            if ($db_key->devices and $reset) {
                if ($db_key->registrator == $user->username) {
                    $model->set('devices', NULL)
                        ->where('user_key', $keys)
                        ->where('registrator', $user->username)
                        ->update();
                        $model->set('expired_date', NULL)
                        ->where('user_key', $keys)
                        ->where('registrator', $user->username)
                        ->update();
                    $rules = ['reset' => true, 'devices_total' => 0, 'devices_max' => $db_key->max_devices];
                }
            } else {
            }
        }
        $data = [
            'registered' => $db_key ? true : false,
            'keys' => $keys,
        ];
        $real_response = array_merge($data, $rules);
        return $this->response->setJSON($real_response);
    }
    
    public function api_key_delete()
    {
        

        $keys = $this->request->getGet('userkey');

        
       if($this->model->deleteKey($keys, $this->user->username))
       {
            return $this->response->setJSON(['success' => true,'message'=>'Data Deleted Successfully']);
       }
       else{
        return $this->response->setJSON(['success' => false,'message'=>'Key Not Found']);
       }
    }
    
    
    
    public function edit_key($key = false)
    {
        if ($this->request->getPost())
        return $this->edit_key_action();
        $msgDanger = "The user key no longer exists.";
        if ($key) {
            $dKey = $this->model->getKeys($key, 'id_keys');
            $user = $this->user;
            if ($dKey) {
                if ($dKey->registrator == $user->username) {
                    $validation = Services::validation();
                    $data = [
                        'title' => 'Key',
                        'user' => $user,
                        'key' => $dKey,
                        'game_list' => $this->game_list,
                        'time' => $this->time,
                        'key_info' => getDevice($dKey->devices),
                        'messages' => setMessage('Please carefuly edit information'),
                        'validation' => $validation,
                    ];
                    return view('Keys/key_edit', $data);
                } else {
                    $msgDanger = "Restricted to this user key.";
                }
            }
        }
        return redirect()->to('keys')->with('msgDanger', $msgDanger);
    }
    private function edit_key_action()
    {
        $keys = $this->request->getPost('id_keys');
        $user = $this->user;
        $dKey = $this->model->getKeys($keys, 'id_keys');
        $game = implode(",", array_keys($this->game_list));
        if (!$dKey) {
            $msgDanger = "The user key no longer exists~";
        } else {
            if ($dKey->registrator == $user->username) {
                $form_reseller = [
                    'status' => [
                        'label' => 'status',
                        'rules' => 'required|integer|in_list[0,1]',
                        'erros' => [
                            'integer' => 'Invalid {field}.',
                            'in_list' => 'Choose between list.'
                        ]
                    ]
                ];
                $form_admin = [
                    'id_keys' => [
                        'label' => 'keys',
                        'rules' => 'required|is_not_unique[keys_code.id_keys]|numeric',
                        'errors' => [
                            'is_not_unique' => 'Invalid keys.'
                        ],
                    ],
                    'game' => [
                        'label' => 'Games',
                        'rules' => "required|alpha_numeric_space|in_list[$game]",
                        'errors' => [
                            'alpha_numeric_space' => 'Invalid characters.'
                        ],
                    ],
                    'user_key' => [
                        'label' => 'User keys',
                        'rules' => "required|is_unique[keys_code.user_key,user_key,$dKey->user_key]",
                        'errors' => [
                            'is_unique' => '{field} has been taken.'
                        ],
                    ],
                    'duration' => [
                        'label' => 'duration',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid day {field}.'
                        ]
                    ],
                    'max_devices' => [
                        'label' => 'devices',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid max of {field}.'
                        ]
                    ],
                    'registrator' => [
                        'label' => 'registrator',
                        'rules' => 'permit_empty|alpha_numeric_space|min_length[4]'
                    ],
                    'expired_date' => [
                        'label' => 'expired',
                        'rules' => 'permit_empty|valid_date[Y-m-d H:i:s]',
                        'errors' => [
                            'valid_date' => 'Invalid {field} date.',
                        ]
                    ],
                    'devices' => [
                        'label' => 'device list',
                        'rules' => 'permit_empty'
                    ]
                ];
                $form_owner = [
                    'id_keys' => [
                        'label' => 'keys',
                        'rules' => 'required|is_not_unique[keys_code.id_keys]|numeric',
                        'errors' => [
                            'is_not_unique' => 'Invalid keys.'
                        ],
                    ],
                    'game' => [
                        'label' => 'Games',
                        'rules' => "required|alpha_numeric_space|in_list[$game]",
                        'errors' => [
                            'alpha_numeric_space' => 'Invalid characters.'
                        ],
                    ],
                    'user_key' => [
                        'label' => 'User keys',
                        'rules' => "required|is_unique[keys_code.user_key,user_key,$dKey->user_key]",
                        'errors' => [
                            'is_unique' => '{field} has been taken.'
                        ],
                    ],
                    'duration' => [
                        'label' => 'duration',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid day {field}.'
                        ]
                    ],
                    'max_devices' => [
                        'label' => 'devices',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid max of {field}.'
                        ]
                    ],
                    'registrator' => [
                        'label' => 'registrator',
                        'rules' => 'permit_empty|alpha_numeric_space|min_length[4]'
                    ],
                    'expired_date' => [
                        'label' => 'expired',
                        'rules' => 'permit_empty|valid_date[Y-m-d H:i:s]',
                        'errors' => [
                            'valid_date' => 'Invalid {field} date.',
                        ]
                    ],
                    'devices' => [
                        'label' => 'device list',
                        'rules' => 'permit_empty'
                    ]
                ];
                if ($user->level == 1) {
                    // Owner: full form rules.
                    $form_rules = $form_owner;
                    $devices = $this->request->getPost('devices');
                    $max_devices = $this->request->getPost('max_devices');

                    $data_saves = [
                        'game' => $this->request->getPost('game'),
                        'user_key' => $this->request->getPost('user_key'),
                        'duration' => $this->request->getPost('duration'),
                        'max_devices' => $max_devices,
                        'status' => $this->request->getPost('status'),
                        'registrator' => $user->username,
                        'expired_date' => $this->request->getPost('expired_date') ?: NULL,
                        'devices' => setDevice($devices, $max_devices),
                    ];
                } elseif ($user->level == 2) {
                    // Admin: reduced form rules.
                    $form_rules = $form_admin;
                    $devices = $this->request->getPost('devices');
                    $max_devices = $this->request->getPost('max_devices');
                    $data_saves = [
                        'game' => $this->request->getPost('game'),
                        'user_key' => $this->request->getPost('user_key'),
                        'duration' => $this->request->getPost('duration'),
                        'max_devices' => $max_devices,
                        'status' => $this->request->getPost('status'),
                        'registrator' => $user->username,
                        'expired_date' => $this->request->getPost('expired_date') ?: NULL,
                        'devices' => setDevice($devices, $max_devices),
                    ];
                } else {
                    $form_rules = $form_reseller;
                    $data_saves = ['status' => $this->request->getPost('status')];
                }
                if (!$this->validate($form_rules)) {
                    return redirect()->back()->withInput()->with('msgDanger', 'Failed! Please check the error');
                } else {
                    $this->model->update($dKey->id_keys, $data_saves);
                    return redirect()->back()->with('msgSuccess', 'User key successfuly updated!');
                }
            } else {
                $msgDanger = "Restricted to this user key~";
            }
        }
        return redirect()->to('keys')->with('msgDanger', $msgDanger);
    }
    public function generate()
    {
        if ($this->request->getPost())
            return $this->generate_action();
            
        $user = $this->user;
        $validation = Services::validation();
        $unlimited = ((int)$user->level === 2);
        $message = setMessage("<i class='bi bi-wallet'></i> Total Saldo " . ($unlimited ? 'Unlimited' : '$' . $user->saldo));
        if (! $unlimited && $user->saldo <= 0) {
            $message = setMessage("Please top up to your beloved admin.", 'warning');
        }
        
        $data = [
            'title' => 'Generate',
            'user' => $this->user,
            'time' => $this->time,
            'game' => $this->game_list,
            'duration' => $this->duration,
            'loopcount' => $this->bulk_key,
            'price' => json_encode($this->price),
            'messages' => $message,
            'validation' => $validation,
        ];
        
        return view('Keys/generate', $data);
    }
   private function generate_action()
{
    $user = $this->user;
    $game = $this->request->getPost('game');
    $maxd = $this->request->getPost('max_devices');
    $drtn = $this->request->getPost('duration');
    $loopcount = $this->request->getPost('loopcount');
    $getPrice = getPrice($this->price, $drtn, $loopcount, $maxd);
    $customMode = $this->request->getPost('custominput') === 'custom';
    $customText = trim((string) $this->request->getPost('cuslicense'));
    $prefix = 'CHRIS';
    $niga = '';

    if  ($drtn == 1){
        $niga = "1";
    }elseif($drtn == 2){
        $niga = "2";
    }elseif($drtn == 3){
        $niga = "3";
    }elseif($drtn == 7){
        $niga = "7";
    }elseif($drtn == 15){
        $niga = "15";
    }elseif($drtn == 30){
        $niga = "30";
    }elseif($drtn == 60){
        $niga = "60";
    }
    elseif($drtn == 90){
        $niga = "90";
    }
     elseif($drtn == 239976){
        $niga = "9999";
    }
    if ($customMode) {
        $chanlicense = $customText;
        $loopcount = 1;
    } else {
        $chanlicense = $niga . "x" . $prefix . "-" . random_string('alnum', 10);
    }

    $game_list = implode(",", array_keys($this->game_list));

    $form_rules = [
        'game' => [
            'label' => 'Games',
            'rules' => "required|alpha_numeric_space|in_list[$game_list]",
            'errors' => [
                'alpha_numeric_space' => 'Invalid characters.'
            ],
        ],
        'duration' => [
            'label' => 'duration',
            'rules' => 'required|numeric|greater_than_equal_to[1]',
            'errors' => [
                'greater_than_equal_to' => 'Minimum {field} is invalid.',
                'numeric' => 'Invalid duration.'
            ]
        ],
        'max_devices' => [
            'label' => 'devices',
            'rules' => 'required|numeric|greater_than_equal_to[1]',
            'errors' => [
                'greater_than_equal_to' => 'Minimum {field} is invalid.',
                'numeric' => 'Invalid max of {field}.'
            ]
        ],
    ];

    if ($customMode) {
        $form_rules['cuslicense'] = [
            'label' => 'Custom key',
            'rules' => 'required|alpha_numeric|min_length[4]|max_length[16]|is_unique[keys_code.user_key]',
            'errors' => [
                'alpha_numeric' => 'Custom key may only contain letters and numbers.',
                'min_length' => 'Custom key must be at least 4 characters.',
                'max_length' => 'Custom key may not exceed 16 characters.',
                'is_unique' => 'This custom key already exists.',
            ],
        ];
    }

    $validation = Services::validation();

    $reduceCheck = ($user->saldo - $getPrice);

    if ((int)$user->level !== 2 && $reduceCheck < 0) {
        $validation->setError('duration', 'Insufficient balance');
        return redirect()->back()->withInput()->with('msgWarning', 'Please top up to your account.');
    } else {
        if (!$this->validate($form_rules)) {
            return redirect()->back()->withInput()->with('msgDanger', 'Failed! Please check the error');
        } else {
            $msg = "Keys Successfully Generated.";
            $generatedKeys = [];

            for ($i = 0; $i < $loopcount; $i++) {
                $license = $chanlicense;
                if ($loopcount > 1) {
                    $license .= "-" . ($i + 1);
                }
                $data_response = [
                    'game' => $game,
                    'user_key' => $license,
                    'duration' => $drtn,
                    'max_devices' => $maxd,
                    'registrator' => $user->username,
                    'admin_id' => $this->userId
                ];

                $idKeys = $this->model->insert($data_response);
                $generatedKeys[] = $license;
            }

            if ((int)$user->level !== 2) {
                $this->userModel->update(session('userid'), ['saldo' => $reduceCheck]);
            }

            $history = new HistoryModel();
            foreach ($generatedKeys as $key) {
                $history->insert([
                    'keys_id' => $idKeys,
                    'user_do' => $user->username,
                    'info' => "$game|" . substr($key, 0, 5) . "|$drtn|$maxd"
                ]);
            }

            $other_response = [
                'fees' => $getPrice
            ];

            $flashdata = $data_response;
            if ($loopcount > 1) {
                $keys_info = implode("<br>", $generatedKeys);
                $msg .= "<br>Keys: <br>$keys_info";
                $flashdata['keys_info'] = $keys_info;
            }

            session()->setFlashdata(array_merge($flashdata, $other_response));

            return redirect()->back()->with('msgSuccess', $msg);
        }
    }
}
     public function ExtendDurationKey()
    {
        if ($this->request->getPost()) return $this->ExtendDurationKey_action();
        $user = $this->user;
        if (! in_array((int)$user->level, [1, 2], true))
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');

        $dGen = $this->model;
        $validation = Services::validation();

        $data = [
            'title' => '',
            'user' => $user,
            'time' => $this->time,   
            'extendkeyduration' => $dGen->extendkeyduration,
        ];
        return view('Keys/ExtendDurationKey', $data);
    }
    
    public function ExtendDurationKey_action()
{
    $user = $this->user;
    if (! in_array((int)$user->level, [1, 2], true))
        return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');

    $extendKey = $this->request->getPost('extendkeyy');
    $extendDuration = $this->request->getPost('extendkeyduration');

    $allowed = [1, 2, 3, 7, 15, 30, 60, 90, 120, 365];
    if (! in_array((int)$extendDuration, $allowed, true)) {
        return redirect()->back()->with('msgDanger', 'Invalid duration.');
    }
    $affectedRows = (new KeysModel())
        ->set('expired_date', date('Y-m-d H:i:s', strtotime('+' . (int)$extendDuration . ' days')))
        ->where('user_key', $extendKey)
        ->where('registrator', $user->username)
        ->update();

    if ($affectedRows > 0) {
        return redirect()->to('ExtendDuration')->with('msgSuccess', 'Key duration successfully updated!');
    } else {
        $msgDanger = "Failed to update key duration.";
        return redirect()->back()->with('msgDanger', $msgDanger);
    }
}

    public function memberKey()
    {
        $data = [
            'title' => 'Member Key',
            'searched' => '',
            'result' => null,
            'game_list' => $this->game_list,
            'time' => $this->time,
        ];

        if ($this->request->getMethod() === 'post') {
            $searched = trim((string) $this->request->getPost('member_key'));
            $data['searched'] = $searched;
            $key = $searched !== '' ? $this->model->where('registrator', $this->user->username)->getKeys($searched) : null;

            if ($key) {
                $doReset = $this->request->getPost('action') === 'reset';
                if ($doReset) {
                    $this->model->set('devices', NULL)->where('user_key', $searched)->where('registrator', $this->user->username)->update();
                    $this->model->set('expired_date', NULL)->where('user_key', $searched)->where('registrator', $this->user->username)->update();
                    $key = $this->model->where('registrator', $this->user->username)->getKeys($searched);
                }
                $devices = $key->devices
                    ? array_filter(explode(',', reduce_multiples($key->devices, ',', true)))
                    : [];
                $data['result'] = [
                    'found' => true,
                    'game' => $this->game_list[$key->game] ?? $key->game,
                    'duration' => $key->duration,
                    'devices' => count($devices),
                    'max_devices' => (int) $key->max_devices,
                    'expired' => $key->expired_date,
                    'status' => (int) $key->status,
                    'reset' => $doReset,
                ];
            } else {
                $data['result'] = ['found' => false];
            }
        }

        return view('Keys/member_key', $data);
    }
}
