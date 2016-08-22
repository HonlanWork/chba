<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	public function login() {
        $this->error = I('error');
        $this->display();
    }

    public function login_handle() {
        $count = M('user')->where(array('cellphone'=>I('cellphone')))->count();
        if ($count == 0) {
            $this->redirect('Login/login', array('error'=>'该手机号尚未注册'));
        }
        else {
            $count = M('user')->where(array('cellphone'=>I('cellphone'), 'password'=>md5(I('password'))))->count();
            if ($count == 0) {
                $this->redirect('Login/login', array('error'=>'密码错误'));
            }
            else {
                $user = M('user')->where(array('cellphone'=>I('cellphone'), 'password'=>md5(I('password'))))->find();
                session('uid', $user['id']);
                $this->success('登录成功', U('Index/index'), 3);
            }
        }
    }

    public function register() {
        $this->error = I('error');
        $this->display();
    }

    public function register_handle() {
        $count = M('user')->where(array('cellphone'=>I('cellphone')))->count();
        if ($count > 0) {
            $this->redirect('Login/register', array('error'=>'该手机号已经注册'));
        }
        else {
            M('user')->data(array('name'=>I('name'), 'cellphone'=>I('cellphone'), 'password'=>md5(I('password'))))->add();
            $this->success('注册成功', U('Login/login'), 3);
        }
    }

    public function validate_cellphone() {
        $count = M('user')->where(array('cellphone'=>I('cellphone')))->count();
        if ($count > 0) {
            echo json_encode(array('error'=>'该手机号已经注册'));
        }
        else {
            echo json_encode(array('error'=>''));
        }
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