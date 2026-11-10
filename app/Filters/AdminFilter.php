<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (! $session->has('userid') || ! $session->has('time_login')) {
            return redirect()->to('/login')->with('msgWarning', 'Please login first');
        }

        $userModel = new UserModel();
        $auth = $userModel->checkAuthFilter();
        if ($auth instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $auth;
        }
        $user = $userModel->getUser($session->get('userid'));
        if (! $user || (int) $user->level !== 1) {
            return redirect()->to('/dashboard')->with('msgWarning', 'Access Denied!');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
