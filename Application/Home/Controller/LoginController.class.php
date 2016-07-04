<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	public function login() {
        $this->display();
    }

    public function login_handle() {

    }

    public function register() {
        $this->display();
    }

    public function register_handle() {

    }

    public function admin_login() {
    	$this->display();
    }

    public function admin_login_handle() {
    	if ($_POST['account'] != C('ADMIN_ACCOUNT') || $_POST['password'] != C('ADMIN_PASSWORD')) {
    		$this->redirect('Login/admin_login');
    	}
    	else {
    		session('uadmin', 1);
    		$this->redirect('Admin/index');
    	}
    }
}