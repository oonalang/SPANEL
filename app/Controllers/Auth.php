<?php

namespace App\Controllers;

use App\Models\CodeModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

class Auth extends BaseController
{
    protected $user;
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    public function getUserIP(): string
    {
        // Trust X-Forwarded-For only from proxies CodeIgniter is configured to trust.
        return (string) $this->request->getIPAddress();
    }

    public function index()
    {
        
        $a = $this->userModel->getUser(session('userid'));
        dd($a, session());
    }

    public function login()
    {
        if (session()->has('userid'))
            return redirect()->to('dashboard')->with('msgSuccess', 'Login Successful!');

        if (\App\Controllers\Setup::needed())
            return redirect()->to('setup');

        if ($this->request->getPost())
            return $this->login_action();
        $data = [
            'title' => 'Login',
            'validation' => Services::validation(),
            'user_ip' => $this->getUserIP(),
        ];
        return view('Auth/login', $data);
    }

    public function register()
    {
        if (strtolower((string) env('shai.registration', 'open')) === 'closed') {
            return redirect()->to('login')->with('msgWarning', 'Registration is closed. Ask the owner for an account.');
        }

        if (session()->has('userid'))
            return redirect()->to('dashboard')->with('msgSuccess', 'Login Successful!');

        if ($this->request->getPost())
            return $this->register_action();
        $data = [
            'title' => 'Register',
            'validation' => Services::validation(),
            'user_ip' => $this->getUserIP(),
        ];
        return view('Auth/register', $data);
    }

    private function login_action()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $stayLog = $this->request->getPost('stay_log');

        $rules = [
            'username' => 'required|alpha_numeric|min_length[4]|max_length[25]',
            'password' => 'required|min_length[6]|max_length[45]',
            'stay_log' => 'permit_empty|max_length[3]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Failed</strong> Please check the form.');
        }

        $ip = $this->getUserIP();
        if (($wait = \App\Libraries\LoginThrottle::lockedFor($ip, $username)) > 0) {
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Locked</strong> Too many wrong attempts. Try again in ' . ceil($wait / 60) . ' minute(s).');
        }

        $account  = $this->userModel->getUser($username, 'username');
        // Always run one password check so a missing account takes as long as a wrong password.
        $stored   = $account ? (string) $account->password : '$2y$11$NrWehkSfzcj1ZgCj0fnAfWeAdpJZqnLvlwTkgfy90M54SKDvDiK9P';
        $verified = password_verify(create_password($password, false), $stored);

        if (! $account || ! $verified) {
            \App\Libraries\LoginThrottle::fail($ip, $username);
            Services::validation()->setError('password', 'Wrong username or password.');
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Failed</strong> Wrong username or password.');
        }

        \App\Libraries\LoginThrottle::clear($ip, $username);

        if (password_needs_rehash($stored, PASSWORD_DEFAULT, ['cost' => 11])) {
            try {
                $this->userModel->update($account->id_users, ['password' => create_password($password)]);
            } catch (\Throwable $e) {
                log_message('error', 'Password rehash skipped.');
            }
        }

        $now = \CodeIgniter\I18n\Time::now();
        if ((int) $account->status !== 1 || empty($account->expiration_date)
            || strtotime((string) $account->expiration_date) <= $now->getTimestamp()) {
            return redirect()->route('login')->withInput()->with('msgDanger', '<strong>Expired</strong> Please Renew Your Account to Login.');
        }

        // Regenerate the session id against fixation; keep the expiry and IP binding.
        session()->regenerate(false);
        session()->set([
            'userid' => $account->id_users,
            'unames' => $account->username,
            'time_login' => $stayLog ? $now->addHours(10) : $now->addMinutes(120),
            'time_since' => $now,
            'login_at' => $now,
            'stay_log' => $stayLog ? 1 : 0,
            'bind_ip' => $this->getUserIP(),
        ]);
        $this->sendLoginNotice($account);
        return redirect()->to('dashboard')->with('msgSuccess', 'Account Expires on : ' . $account->expiration_date);
    }

    /** A mail provider outage must not block a successful login. */
    private function sendLoginNotice(object $account): void
    {
        $sender = (string) env('email.fromEmail', '');
        if ($sender === '' || ! filter_var($account->email ?? '', FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            $email = Services::email();
            $email->setFrom($sender, 'SHAI Panel');
            $email->setTo((string) $account->email);
            $email->setSubject('SHAI Panel login notification');
            $email->setMessage('A login to your SHAI Panel account was successful.');
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Login notification delivery failed.');
        }
    }

    public function register_action()
    {
        if (strtolower((string) env('shai.registration', 'open')) === 'closed') {
            return redirect()->to('login')->with('msgWarning', 'Registration is closed.');
        }

        $email = $this->request->getPost('email');
        $userna = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $password = $this->request->getPost('password');
        
        $form_rules = [
            'email' => [
                'label' => 'email',
                'rules' => 'required|valid_email|max_length[40]|is_unique[users.email]'
            ],
            'username' => [
                'label' => 'username',
                'rules' => 'required|alpha_numeric|min_length[4]|max_length[25]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'The {field} has been taken.'
                ]
            ],
            'fullname' => [
                'label' => 'fullname',
                'rules' => 'required|alpha_numeric_space|min_length[4]|max_length[155]',
                'errors' => [
                    'is_unique' => 'The {field} has been taken.'
                ]
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required|min_length[8]|max_length[45]',
            ],
            'password2' => [
                'label' => 'password',
                'rules' => 'required|min_length[8]|max_length[45]|matches[password]',
                'errors' => [
                    'matches' => '{field} not match, check the {field}.'
                ]
            ]
        ];

        if (! $this->validate($form_rules)) {
            return redirect()->route('register')->withInput()->with('msgDanger', implode(' ', array_values($this->validator->getErrors())));
        }

        $record = [
            'email' => $email,
            'username' => $userna,
            'fullname' => $fullname,
            'level' => 2,
            'status' => 1,
            'password' => create_password($password),
            'saldo' => 2147483647,
            'uplink' => null,
            'user_ip' => $this->getUserIP(),
            'expiration_date' => '2099-12-31 23:59:59',
            'reset_link_token' => '',
            'exp_date' => '',
            'logged_in' => 0
        ];
        try {
            if ($this->userModel->insert($record, true)) {
                return redirect()->to('login')->with('msgSuccess', 'Registration successful. Sign in to continue.');
            }
        } catch (\Throwable $error) {
            log_message('error', 'Registration failed: {error}', ['error' => $error->getMessage()]);
        }
        return redirect()->route('register')->withInput()->with('msgDanger', 'Unable to create account.');
    }

    public function logout()
    {
        if (session()->has('userid')) {
            $unset = ['userid', 'unames', 'time_login', 'time_since', 'login_at', 'stay_log', 'bind_ip'];
            session()->remove($unset);
            session()->setFlashdata('msgSuccess', 'Logout successfuly.');
        }
        return redirect()->to('login');
    }
}
