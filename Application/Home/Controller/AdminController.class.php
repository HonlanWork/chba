<?php
namespace Home\Controller;
use Think\Controller;
class AdminController extends Controller {
    public function _initialize(){
        if(!isset($_SESSION['uadmin'])){
            $this->redirect('Login/admin_login');
        }
    }

    public function index(){
        layout('admin');
    	$this->display();
    }

    public function auction(){
        $this->auctions = M('auction')->select();
        layout('admin');
    	$this->display();
    }

    public function auction_add(){
        $data = array(
            'title' => $_POST['title'],
            'titleen' => $_POST['titleen'],
            'begintime' => $_POST['begintime'],
            'endtime' => $_POST['endtime'],
            'status' => '');
        $data['begintime'][10] = ' ';
        $data['begintime'] = strtotime($data['begintime']);
        $data['endtime'][10] = ' ';
        $data['endtime'] = strtotime($data['endtime']);
        if (array_key_exists('status', $_POST)) {
            $data['status'] = '当前';
            $map = array();
            $map['id'] = array('neq', -1);
            M('auction')->where($map)->save(array('status'=>''));
        }
        M('auction')->data($data)->add();
        $this->redirect('Admin/auction');
    }

    public function auction_active(){
        $map = array();
        $map['id'] = array('neq', -1);
        M('auction')->where($map)->save(array('status'=>''));
        M('auction')->where(array('id'=>I('id')))->save(array('status'=>'当前'));
        $this->redirect('Admin/auction');
    }

    public function auction_delete(){
        M('auction')->where(array('id'=>I('id')))->delete();
        $this->redirect('Admin/auction');
    }

    public function auction_edit(){
        $data = array(
            'title' => $_POST['title'],
            'titleen' => $_POST['titleen'],
            'begintime' => $_POST['begintime'],
            'endtime' => $_POST['endtime']);
        $data['begintime'][10] = ' ';
        $data['begintime'] = strtotime($data['begintime']);
        $data['endtime'][10] = ' ';
        $data['endtime'] = strtotime($data['endtime']);
        M('auction')->where(array('id'=>I('id')))->save($data);
        $this->redirect('Admin/auction');
    }

    public function item(){
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $this->items = M('item')->where(array('auction_id'=>$auction['id']))->select();
        $this->auction = $auction;
        layout('admin');
    	$this->display();
    }

    public function item_add(){
        $data = array(
            'auction_id' => I('auction_id'),
            'number' => I('number'),
            'category' => I('category'),
            'title' => I('title'),
            'start' => I('start'),
            'step' => I('step'),
            'highest' => 0,
            'amount' => I('amount'),
            'owner' => '',
            'donator' => I('donator'),
            'description' => I('description'),
            'donatorDesc' => I('donatorDesc'),
            'thumbnail' => '/img/item.jpg',
            'image1' => '',
            'image2' => '',
            'image3' => '',
            'image4' => '',
            'image5' => '',
            'image6' => '');
        M('item')->data($data)->add();
        $data = M('item')->where($data)->find();

        for ($i = 1; $i <= 6; $i++) { 
            if ($_POST['image'.$i.$i] != '') {
                if ($_POST['image'.$i.$i] != 'remove') {
                    if (!file_exists("./Public/upload/".$data['id'])){ 
                        mkdir("./Public/upload/".$data['id'], 0777, true);
                    }

                    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'.$i.$i], $result)){
                        $filetype = $result[2];
                        $filename = 'image'.$i.'_thumb';
                        $new_file = "./Public/upload/".$data['id']."/".$filename.'.'.$filetype;
                        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $_POST['image'.$i.$i])))){
                            $data['image'.$i] = substr($new_file, 8); 
                        }
                    }
                }
            }
        }
        for ($i = 1; $i <= 6; $i++) {
            if ($data['image'.$i] != '') {
                $data['thumbnail'] = $data['image'.$i];
                break;
            }
        }
        M('item')->where(array('id'=>$data['id']))->save($data);
        $this->redirect('Admin/item');
    }

    public function item_delete(){
        if (file_exists("./Public/upload/".I('id'))){ 
            deldir("./Public/upload/".I('id'));
        }
        M('item')->where(array('id'=>I('id')))->delete();
        $this->redirect('Admin/item');
    }

    public function item_edit(){
        layout('admin');
        $this->item = M('item')->where(array('id'=>I('id')))->find();
        $categories = array();
        $categories[] = "机票、酒店、度假村等旅行产品 Travel-related Items";
        $categories[] = "体验服务类 Unique Experiences, Luxury / Fashion";
        $categories[] = "零售类产品 Retail Goods Items";
        $categories[] = "收藏品 Collectables Items";
        $categories[] = "工艺与艺术品类 Displayable Art-pieces";
        $categories[] = "非旅行类礼券 Service Vouchers";
        $categories[] = "儿童作品 Children's Art Works";        
        $this->categories = $categories;
        $this->display();
    }

    public function item_set_thumb(){
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $items = M('item')->where(array('auction_id'=>$auction['id']))->select();
        for ($i = 0; $i < count($items); $i++) {
            $flag = false; 
            for ($j = 1; $j <= 6; $j++) {
                if ($items[$i]['image'.$j] != '') {
                    M('item')->where(array('id'=>$items[$i]['id']))->save(array('thumbnail'=>$items[$i]['image'.$j]));
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                M('item')->where(array('id'=>$items[$i]['id']))->save(array('thumbnail'=>'/img/item.jpg'));
            }
        }
        $this->redirect('Admin/item');
    }

    public function item_edit_handle(){
        $old = M('item')->where(array('id'=>I('id')))->find();
        $description = I('description');
        $donatorDesc = I('donatorDesc');
        $data = array(
            'number' => I('number'),
            'category' => I('category'),
            'title' => I('title'),
            'start' => I('start'),
            'step' => I('step'),
            'amount' => I('amount'),
            'donator' => I('donator'),
            'description' => I('description'),
            'donatorDesc' => I('donatorDesc'));
        for ($i = 1; $i <= 6; $i++) { 
            if ($_POST['image'.$i.$i] != '') {
                if ($_POST['image'.$i.$i] != 'remove') {
                    if (!file_exists("./Public/upload/".$old['id'])){ 
                        mkdir("./Public/upload/".$old['id'], 0777, true);
                    }

                    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'.$i.$i], $result)){
                        $filetype = $result[2];
                        $filename = 'image'.$i.'_thumb';
                        $new_file = "./Public/upload/".$old['id']."/".$filename.'.'.$filetype;
                        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $_POST['image'.$i.$i])))){
                            $data['image'.$i] = substr($new_file, 8); 
                        }
                    }
                }
                else {
                    $filename = './Public'.$old['image'.$i];
                    unlink($filename);
                    $data['image'.$i] = ''; 
                }
            }
            else {
                $data['image'.$i] = $old['image'.$i];
            }
        }
        $flag = false;
        for ($i = 1; $i <= 6; $i++) {
            if ($data['image'.$i] != '') {
                $data['thumbnail'] = $data['image'.$i];
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            $data['thumbnail'] = '/img/item.jpg';
        }
        M('item')->where(array('id'=>I('id')))->save($data);
        $this->redirect('Admin/item_edit', array('id'=>I('id')));
    }

    public function action(){
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $this->auction = $auction;
        $actions = M('action')->where(array('auction_id'=>$auction['id']))->order('timestamp desc')->select();
        $this->actions = $actions;
        layout('admin');
    	$this->display();
    }

    public function action_init(){
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        M('action')->where(array('auction_id'=>$auction['id']))->delete();
        M('item')->where(array('auction_id'=>$auction['id']))->save(array('highest'=>0, 'owner'=>''));
        $this->redirect('Admin/action');
    }

    public function result(){
        layout('admin');
    	$this->display();
    }

    public function result_data() {
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $items = M('item')->where(array('auction_id'=>$auction['id']))->order('number asc')->select();

        for ($i = 0; $i < count($items); $i++) { 
            $tmp = M('action')->where(array('item_id'=>$items[$i]['id']))->order('timestamp desc')->select();
            $users = array();
            $rank = array();
            for ($j = 0; $j < count($tmp); $j++) { 
                if (!array_key_exists($tmp[$j]['user_id'], $users)) {
                    $users[$tmp[$j]['user_id']] = $tmp[$j]['price'];
                    $rank[] = array('user_id'=>$tmp[$j]['user_id'], 'price'=>$tmp[$j]['price'], 'name'=>$tmp[$j]['name']);
                }
            }
            usort($rank, "cmp");

            $tmp = '';

            for ($j = 0; $j < $items[$i]['amount']; $j++) {
                $tmp .= '<p>'.$rank[$j]['name'].' '.$rank[$j]['price'].'</p>';
            }
            $items[$i]['result'] = $tmp;
        }

        echo json_encode(array('data'=>$items));
    }

    public function user() {
        $this->users = M('user')->select();
        layout('admin');
        $this->display();
    }

    public function reset_password() {
        M('user')->where(array('id'=>I('id')))->save(array('password'=>md5('chba')));
        $this->redirect('Admin/user');
    }

}