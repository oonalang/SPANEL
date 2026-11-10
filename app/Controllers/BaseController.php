<?php

namespace App\Controllers;

use App\Models\CodeModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
	protected $request;

	protected $helpers = ['nata', 'form', 'url', 'text', 'html', 'filesystem'];
  
    /** Client IP from CodeIgniter's trusted-proxy settings, not raw headers. */
    public function getUserIP(): string
    {
        return (string) $this->request->getIPAddress();
    }

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

	}
}
