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
                $this->success('登录成功<br/>Login Success', U('Index/index'), 3);
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
            M('user')->data(array('name'=>I('name'), 'email'=>I('email'), 'cellphone'=>I('cellphone'), 'password'=>md5(I('password'))))->add();
            $this->success('注册成功<br/>Register Success', U('Login/login'), 3);
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

    public function forget() {
        $reset_key = '';
        for ($i = 0; $i < 30; $i++) { 
            $reset_key .= rand(0, 9);
        }

        M('user')->where(array('email'=>I('email')))->save(array('reset_key'=>md5($reset_key)));

        $email = M('email')->where(array('name'=>'重置密码'))->find();
        $email_content = $email['content'];
        if(count(explode("\n", $email_content)) == 1 ){
            $email_content = explode("\r", $email_content);
        } else {
            $email_content = explode("\n", $email_content);
        }
        $temp = '';
        foreach ($email_content as $key => $value) {
            $temp .= $value."<br/>";
        }
        $email_content = $temp;
        $email_content = explode("^^^", $email_content);
        $email_content = $email_content[0].U('Login/reset', array('email'=>I('email'), 'reset_key'=>md5($reset_key)),false,true).$email_content[1];
        SendMail(I('email'), $email['title'], $email_content);

        $this->success('重置邮件已发送，请登录邮箱查收<br/>Check Your Email', U('Login/login'), 3);
    }

    public function reset() {
        $count = M('user')->where(array('email'=>I('email'), 'reset_key'=>I('reset_key')))->count();
        if ($count == 0) {
            $this->redirect('Login/login');
        }
        else {
            session('reset_email', I('email'));
            $this->display();
        }
    }

    public function reset_handle() {
        $email = $_SESSION['reset_email'];
        session('reset_email', null);
        M('user')->where(array('email'=>$email))->save(array('password'=>md5(I('password1'))));
        $this->success('重置成功<br/>Reset Success', U('Login/login'), 3);
    }
}