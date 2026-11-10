<?php

namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\Server;
use App\Models\Status;
use App\Models\_ftext;
use App\Models\Feature;
use App\Models\onoff;
use App\Models\HistoryModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Controller;

class User extends BaseController
{
    protected $time, $accExpire, $accLevel;
    protected $model, $userid, $user;

    public function __construct()
    {
        $this->userid = session()->userid;
        $this->model = new UserModel();
        $this->user = $this->model->getUser($this->userid);
        $this->time = new \CodeIgniter\I18n\Time;
        
        $this->accExpire = [
           1 => '1 Day',
           2 => '2 Days',
           3 => '3 Days',
           7 => '7 Days',
           15 => '15 Days',
           30 => '30 Days',
           60 => '60 Days',
           90 => '90 Days',
           120 => '120 Days',
           365 => '1 Year',
           1095 => '3 Years',
           1825 => '5 Years',
           9999 => 'Unlimited Years',
        ];
        
        $this->accLevel = [
            3 => 'Reseller',
            2 => 'Admin',
             1 => 'Owner',
           
        ];
    }

    public function index()
    {
        $historyModel = new HistoryModel();
        $data = [
            'title' => 'Dashboard',
            'user' => $this->user,
            'time' => $this->time,
            'history' => $historyModel->getAll(),
        ];
        return view('User/dashboard', $data);
    }
    public function ClearAllHistory() {
    $history_models = new HistoryModel();
    $result = $history_models->where('user_do', $this->user->username)->delete();

    if ($result) {
        return $this->response->setJSON(['success' => true, 'message' => 'History']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to Clear History']);
    }
}

public function DeleteAllHistory() {
    $user = $this->user;
    $history_model = new HistoryModel();
    $username = $user->username;
    $data = $history_model->where('user_do', $username)->delete();
    
    if ($data) {
        return $this->response->setJSON(['success' => true, 'message' => 'History']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to Clear History']);
    }
}


    
     public function ref_index()
    {
        $user = $this->user;
        
        if ($this->request->getPost())
        if (($user->level == 1) || ($user->level == 2)){
		return $this->reff_action();
	     }
	     else {
	         
	         return redirect()->to('dashboard')->with('msgWarning','Access Denied!');
	     }

        $mCode = new CodeModel();
        $validation = Services::validation();
        $data = [
            'title' => 'Referral',
            'user' => $user,
            'time' => $this->time,
            'code' => $mCode->getCode(),
            'accExpire' => $this->accExpire,
            'accLevel' => $this->accLevel,
            'total_code' => $mCode->countAllResults(),
            'validation' => $validation
        ];
        return view('Admin/referral', $data);
    }
    

    private function reff_action()
    {
        $saldo = $this->request->getPost('set_saldo');
        $user_expire = $this->request->getPost('accExpire');
        $accLevel1 = $this->request->getPost('accLevel');
        $accExpire = $this->time::now()->addDays($user_expire);
        $form_rules = [
            'set_saldo' => [
                'label' => 'saldo',
                'rules' => 'required|numeric|max_length[11]|greater_than_equal_to[0]',
                'errors' => [
                    'greater_than_equal_to' => 'Invalid currency, cannot set to minus.'
                ]
            ],
            'accExpire' => [
                'label' => 'Account Expiration',
                'rules' =>  'required|numeric|max_length[4]|greater_than_equal_to[1]',
                'errors' => [
                     'greater_than_equal_to' => 'Invalid Days, cannot set to expired.'
                ]
            ]
        ];

        if (!$this->validate($form_rules)) {
            return redirect()->back()->withInput()->with('msgDanger', 'Failed, check the form');
        } else {
            $code = random_string('alnum', 6);
            $codeHash = create_password($code, false);
            $referral_code = [
                'code' => $codeHash,
                'Referral' => $code,
                'level' => $accLevel1,
                'set_saldo' => ($saldo < 1 ? 0 : $saldo),
                'created_by' => session('unames'),
                'acc_expiration' => $accExpire
            ];
            $mCode = new CodeModel();
            $ids = $mCode->insert($referral_code, true);
            if ($ids) {
                $msg = "Referral : $code";
                return redirect()->back()->with('msgSuccess', $msg);
            }
        }
    }

  
    

    public function api_get_users()
    {
        $model = $this->model;
        return $this->response->setJSON($model->API_getUser());
    }

    public function manage_users()
    {
        $user  = $this->user;
        if ($user->level != 1)
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');

        $model = $this->model;
        $validation = Services::validation();
        $data = [
            'title' => 'Users',
            'user' => $user,
            'user_list' => $model->getUserList(),
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Admin/users', $data);
    }

    public function user_delete($userid = false)
    {
        $model = new userModel();
        $data=$model->where('id_users =', $userid)->delete();
        
        if ($data) {
        return $this->response->setJSON(['success' => true, 'message' => 'success']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'not success']);
    }
}
    
    public function user_edit($userid = false)
    {
        $user = $this->user;
        if ($user->level != 1)
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');

        if ($this->request->getPost())
            return $this->user_edit_action();

        $model = $this->model;
        $validation = Services::validation();

        $data = [
            'title' => 'Settings',
            'user' => $user,
            'target' => $model->getUser($userid),
            'user_list' => $model->getUserList(),
            'time' => $this->time,
            'validation' => $validation,
        ];
        return view('Admin/user_edit', $data);
    }

    private function user_edit_action()
    {
        $model = $this->model;
        $userid = $this->request->getPost('user_id');

        $target = $model->getUser($userid);
        if (!$target) {
            $msg = "User no longer exists.";
            return redirect()->to('dashboard')->with('msgDanger', $msg);
        }

        $username = $this->request->getPost('username');

        $form_rules = [
            'username' => [
                'label' => 'username',
                'rules' => "required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.username,username,$target->username]",
                'errors' => [
                    'is_unique' => 'The {field} has taken by other.'
                ]
            ],
            'fullname' => [
                'label' => 'name',
                'rules' => 'permit_empty|alpha_space|min_length[4]|max_length[155]',
                'errors' => [
                    'alpha_space' => 'The {field} only allow alphabetical characters and spaces.'
                ]
            ],
            'level' => [
                'label' => 'roles',
                'rules' => 'required|numeric|in_list[1,2,3]',
                'errors' => [
                    'in_list' => 'Invalid {field}.'
                ]
            ],
            'status' => [
                'label' => 'status',
                'rules' => 'required|numeric|in_list[1,2,3]',
                'errors' => [
                    'in_list' => 'Invalid {field} account.'
                ]
            ],
            'saldo' => [
                'label' => 'saldo',
                'rules' => 'permit_empty|numeric|max_length[11]|greater_than_equal_to[0]',
                'errors' => [
                    'greater_than_equal_to' => 'Invalid currency, cannot set to minus.'
                ]
            ],
            'uplink' => [
                'label' => 'uplink',
                'rules' => 'required|alpha_numeric|is_not_unique[users.username,username,]',
                'errors' => [
                    'is_not_unique' => 'Uplink not registered anymore.'
                ]
            ],
        ];

        if (!$this->validate($form_rules)) {
            return redirect()->back()->withInput()->with('msgDanger', 'Something wrong! Please check the form');
        } else {
            $fullname = $this->request->getPost('fullname');
            $level = $this->request->getPost('level');
            $status = $this->request->getPost('status');
            $saldo = $this->request->getPost('saldo');
            $uplink = $this->request->getPost('uplink');
            $expiration = $this->request->getPost('expiration');
            $data_update = [
                'username' => $username,
                'fullname' => esc($fullname),
                'level' => $level,
                'status' => $status,
                'saldo' => (($saldo < 1) ? 0 : $saldo),
                'uplink' => $uplink,
                'expiration_date' => $expiration
            ];

            $update = $model->update($userid, $data_update);
            if ($update) {
                return redirect()->back()->with('msgSuccess', "Successfuly update $target->username.");
            }
        }
    }

    public function settings()
    {
        $user = $this->user;
        if (($user->level == 1) || ($user->level == 2)) 
        {
            if ($this->request->getPost('password_form'))
                return $this->passwd_act();
        }
            if ($this->request->getPost('fullname_form'))
                return $this->fullname_act();
        
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'Settings',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('User/settings', $data);
    }
    
    public function EditReferral()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('EditReferral_form'))
                return $this->EditReferral();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'EditReferral',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/EditReferral', $data);
    }
        
    public function lib()
    {
        $user  = $this->user;
        if ($user->level == 1) 
        {
            if ($this->request->getPost('lib_form'))
                return $this->lib();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'lib',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/lib', $data);
    }
    
    public function PrivateDashboard()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('PrivateDashboard_form'))
                return $this->PrivateDashboard();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'PrivateDashboard',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/PrivateDashboard', $data);
    }
    
    public function ManageHackingAttempt()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('ManageHackingAttempt_form'))
                return $this->ManageHackingAttempt();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'Hacking Attempt',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/ManageHackingAttempt', $data);
    }
    
    public function ExtendDuration()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('ExtendDuration_form'))
                return $this->ExtendDuration();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'ExtendDuration',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/ExtendDuration', $data);
    }
    
    public function ManageShortenerLinks()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('ExtendDuration_form'))
                return $this->ManageShortenerLinks();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'Shortener Links',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/ManageShortenerLinks', $data);
    }
    
    public function Links()
    {
        $user  = $this->user;
        if (! $user || (int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'Access Denied!');
        }
        if ($user->level == 1) 
        {
            if ($this->request->getPost('Links_form'))
                return $this->Links();
        }
        $user = $this->user;
        $validation = Services::validation();
        $data = [
            'title' => 'Keygen Links',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        return view('Server/Links', $data);
    }

    public function Server()
    {
        $user = $this->user;
        if ((int)$user->level !== 1) {
            return redirect()->to('dashboard')->with('msgWarning', 'System settings are reserved for the site owner.');
        }
        if (($user->level == 1) || ($user->level == 2)) 
        {
        
        if ($this->request->getPost('modname_form'))
            
            return $this->modname_act();
            
        if ($this->request->getPost('status_form'))
            return $this->status_act();
        }
        if ($user->level == 1)
        {
        if ($this->request->getPost('feature_form'))
            return $this->feature_act();
        if ($this->request->getPost('password_form'))
            return $this->passwd_act();
        }
        if (($user->level == 1) || ($user->level == 2)) 
        {
        if ($this->request->getPost('_ftext'))
            return $this->_ftext_act();

        }
        $user = $this->user;
        
        $validation = Services::validation();
        $data = [
            'title' => 'Server',
            'user' => $user,
            'time' => $this->time,
            'validation' => $validation
        ];
        
        $id = 1;
	    
	    $model= new Server();
	    
	    $data['row'] = $model->where('id',$id)->first();
	    
	     if (($user->level == 1) || ($user->level == 2)){
		return view('Server/Server',$data);
	     }
	     else {
	         
	         return redirect()->to('dashboard')->with('msgWarning','Access Denied');
	     }
    }
    
    private function _ftext_act()
    {
        $id = 1;
	    $model= new _ftext();
	    $myinput = $this->request->getPost('_ftext');
	    $status = $this->request->getPost('_ftextr');
	    $wow = '';
	if($status == "Safe"){
            $wow .= "Safe";
        }else{
            $wow .= "Anti-Cheat is High..!!";
        }
      $data = ['_ftext' => $myinput,'_status' => $wow];
	    $model->update($id,$data);
	    return redirect()->back()->with('msgSuccess', 'Successfuly Changed Mod Floating And Status.');
    }
    
    private function status_act()
    {
        $id = 1;
	    $model= new onoff();
	    $myinput = $this->request->getPost('myInput');
	    $wow = '';
	    if(isset($_POST['radios']) && $_POST['radios'] == 'on') 
        {
            $wow .= "on";
        }
        else
        {
            $wow .= "off";
        }
	    $data = [
	        'status' => $wow,
    	    'myinput' => $myinput
	    ];
	    $model->update($id, $data);
	    return redirect()->back()->with('msgSuccess', 'Mod Status Successfuly Changed.');
    }
    
    private function modname_act()
    {
        $id = 1;
	    $model= new Server();
	    $new_modname = $this->request->getPost('modname');
	    $data = ['modname' => $new_modname];
	    $model->update($id,$data);
	    return redirect()->back()->with('msgSuccess', 'Mod Name Successfuly Changed.');
    }
    
    private function feature_act()
    {
        $id = 1;
	    $model = new Feature();
	    if(isset($_POST['ESP']) && $_POST['ESP'] === 'true') 
        {
            $new_espvalue = 'true';
        }
        else
        {
            $new_espvalue = 'false';
        }
	    if(isset($_POST['Item']) && $_POST['Item'] === 'true') 
        {
            $new_Itemvalue = 'true';
        }
        else
        {
            $new_Itemvalue = 'false';
        }
	    if(isset($_POST['AIM']) && $_POST['AIM'] === 'true') 
        {
            $new_aimvalue = 'true';
        }
        else
        {
            $new_aimvalue = 'false';
        }
	    if(isset($_POST['SilentAim']) && $_POST['SilentAim'] === 'true') 
        {
            $new_SilentAimvalue = 'true';
        }
        else
        {
            $new_SilentAimvalue = 'false';
        }
	    if(isset($_POST['BulletTrack']) && $_POST['BulletTrack'] === 'true') 
        {
            $new_BulletTrackvalue = 'true';
        }
        else
        {
            $new_BulletTrackvalue = 'false';
        }
	    if(isset($_POST['Memory']) && $_POST['Memory'] === 'true') 
        {
            $new_Memoryvalue = 'true';
        }
        else
        {
            $new_Memoryvalue = 'false';
        }
	    if(isset($_POST['Floating']) && $_POST['Floating'] === 'true') 
        {
            $new_Floatingvalue = 'true';
        }
        else
        {
            $new_Floatingvalue = 'false';
        }
	    if(isset($_POST['Setting']) && $_POST['Setting'] === 'true') 
        {
            $new_Settingvalue = 'true';
        }
        else
        {
            $new_Settingvalue = 'false';
        }
	    $data = [
    	    'ESP' => $new_espvalue,
    	    'Item' => $new_Itemvalue,
    	    'SilentAim' => $new_SilentAimvalue,
    	    'AIM' => $new_aimvalue,
    	    'BulletTrack' => $new_BulletTrackvalue,
    	    'Memory' => $new_Memoryvalue,
    	    'Floating' => $new_Floatingvalue,
    	    'Setting' => $new_Settingvalue
	    ];
	    $model->update($id,$data);
	    return redirect()->back()->with('msgSuccess', 'Mod Feature Stats Changed.');
    }
    
    private function passwd_act()
    {
        $current = $this->request->getPost('current');
        $password = $this->request->getPost('password');
        $user = $this->user;
        $currHash = create_password($current, false);
        $validation = Services::validation();
        if (!password_verify($currHash, $user->password)) {
            $msg = "Wrong current password.";
            $validation->setError('current', $msg);
        } elseif ($current == $password) {
            $msg = "Nothing to change.";
            $validation->setError('password', $msg);
        }

        $form_rules = [
            'current' => [
                'label' => 'current',
                'rules' => 'required|min_length[6]|max_length[45]',
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required|min_length[6]|max_length[45]',
            ],
            'password2' => [
                'label' => 'confirm',
                'rules' => 'required|min_length[6]|max_length[45]|matches[password]',
                'errors' => [
                    'matches' => '{field} not match, check the {field}.'
                ]
            ],
        ];

        if (!$this->validate($form_rules)) {
            return redirect()->back()->withInput()->with('msgDanger', 'Something wrong! Please check the form');
        } elseif($this->validate($form_rules)) {
            $newPassword = create_password($password);
            if (isset($newPassword)) {
        include('conn.php');
        $CompName = "Justine Hacks";
        $this->userid = session()->userid;
        $this->model = new UserModel();
        $this->user = $this->model->getUser($this->userid);
        $user = $this->user;
        $username = getName($user);
        date_default_timezone_set('Asia/Manila');
        $timestamp = date('d/m/Y h:i:sa');
        $server = $_SERVER['HTTP_HOST'];
        $result = mysqli_query($conn,"SELECT * FROM users WHERE id_users='" . $this->userid . "'");
        $row= mysqli_fetch_array($result);
 
        if($row)
        {
            $emailId = $row['email'];
            $token = md5($emailId).rand(10,9999);
            $format = mktime(date("h"), date("i")+10, date("s"), date("m"), date("d"), date("Y"));
            $expDate = date("Y-m-d H:i:s", $format);
            $update = mysqli_query($conn,"UPDATE users set reset_link_token='" . $token . "' ,exp_date='" . $expDate . "' WHERE id_users='" . $this->userid . "'");
            $link = 'Your account password has been updated.'; // Never put a password hash in a URL.
            $email = \Config\Services::email();
            $email->setFrom('noreply@Justine_Hacks', 'Justine Hacks');
            $email->setTo($emailId);
            $email->setSubject("[$server]✔ Password Change Request at $timestamp");
            $email->setMessage("<p>Hello...!</p>
        
            <p>You have been requested to change Password at [$server] for [$username] on [$timestamp].</p>
            <p>Please, Click on the following link to change your Account's Password.</p>
            <p>This Mail Can only Valid upto 10 minutes. If you are not requested for password change then contact Panel owner.</p>
         
            '".$link."'");
            $email->send();
            }
         }
         $this->model->update(session('userid'), ['password' => $newPassword]);
            return redirect()->back()->with('msgSuccess', 'Password Successfuly Changed.');
        }
    }
    
    public function verify_pass()
    {
        $user  = $this->user;
        $data = [
            'title' => 'verify_pass',
            'user' => $user,
            'time' => $this->time
        ];
        return view('User/verify_pass', $data);
    }
    
    private function fullname_act()
    {
        $user = $this->user;
        $newName = $this->request->getPost('fullname');

        if ($user->fullname == $newName) {
            $validation = Services::validation();
            $msg = "Nothing to change.";
            $validation->setError('fullname', $msg);
        }

        $form_rules = [
            'fullname' => [
                'label' => 'name',
                'rules' => 'required|alpha_space|min_length[4]|max_length[155]',
                'errors' => [
                    'alpha_space' => 'The {field} only allow alphabetical characters and spaces.'
                ]
            ]
        ];

        if (!$this->validate($form_rules)) {
            return redirect()->back()->withInput()->with('msgDanger', 'Failed! Please check the form');
        } else {
            $this->model->update(session('userid'), ['fullname' => esc($newName)]);
            return redirect()->back()->with('msgSuccess', 'Account Detail Successfuly Changed.');
        }
    }
}