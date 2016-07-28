<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function _initialize(){
        if(!isset($_SESSION['uid'])){
            $this->redirect('Login/login');
        }
    }

    public function index() {
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $this->items = M('item')->where(array('auction_id'=>$auction['id']))->select();
        $this->auction = $auction;
        $this->display();
    }

    public function home() {

    }

    public function detail() {
        $item = M('item')->where(array('id'=>I('id')))->find();

        if(count(explode("\n", $item['description'])) == 1 ){
            $item['description'] = explode("\r", $item['description']);
        } else {
            $item['description'] = explode("\n", $item['description']);
        }
        foreach ($item['description'] as $key => $value) {
            $tmp .= $value."<br/>";
        }
        $item['description'] = $tmp;

        if(count(explode("\n", $item['donatorDesc'])) == 1 ){
            $item['donatorDesc'] = explode("\r", $item['donatorDesc']);
        } else {
            $item['donatorDesc'] = explode("\n", $item['donatorDesc']);
        }
        $tmp = '';
        foreach ($item['donatorDesc'] as $key => $value) {
            $tmp .= $value."<br/>";
        }
        $item['donatorDesc'] = $tmp;

        $user = M('user')->where(array('id'=>$item['owner']))->find();
        $item['owner'] = $user['name'];

        $item['imgs'] = [];
        for ($i = 1; $i <= 6; $i++) { 
            if ($item['image'.$i] != '') {
                $item['imgs'][] = $item['image'.$i];
            }
        }
        $this->item = $item;
        
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $items = M('item')->where(array('auction_id'=>$auction['id']))->select();

        $current = 0;
        $next = 0;
        $previous = 0;
        for ($i = 0; $i < count($items); $i++) { 
            if ($items[$i]['id'] == I('id')) {
                $current = $i;
                break;
            }
        }
        $previous = $current - 1;
        $next = $current + 1;
        if ($previous == -1) {
            $previous = count($items) - 1;
        }
        if ($next == count($items)) {
            $next = 0;
        }
        $this->previous = $items[$previous]['id'];
        $this->next = $items[$next]['id'];
        $this->auction = $auction;
        $this->now = time();

        $this->display();
    }

    public function action() {
        $item = M('item')->where(array('id'=>I('id')))->find();
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $user = M('user')->where(array('id'=>I('user_id')))->find();

        $data = array(
            'auction_id' => $auction['id'],
            'user_id' => I('user_id'),
            'item_id' => I('id'),
            'timestamp' => time(),
            'number' => $item['number'],
            'title' => $item['title'],
            'name' => $user['name'],
            'cellphone' => $user['cellphone'],
            );

        if ($item['highest'] == 0) {
            $data['price'] = $item['start'];
        }
        else {
            $data['price'] = $item['highest'] + $item['step'];
        }
        M('action')->data($data)->add();

        M('item')->where(array('id'=>I('id')))->save(array('highest'=>$data['price'], 'owner'=>$user['id']));

        $this->redirect('Index/detail', array('id'=>I('id')));
    }

    // public function result(){
    //     $this->items = M('item')->field('id,thumbnail,title,highest,owner')->select();
    //     $this->display();
    // }

    // public function price(){
    //     $data = array('itemId'=>I('itemId'),'itemTitle'=>I('itemTitle'),'name'=>I('name'),'mobile'=>I('mobile'),'company'=>I('company'),'position'=>I('position'),'price'=>I('price'),'timestamp'=>time());
    //     M('price')->data($data)->add();
    //     session('name',I('name'));
    //     session('mobile',I('mobile'));
    //     session('company',I('company'));
    //     session('position',I('position'));
    //     $name = I('name');
    //     $price = I('price');
    //     $item = M('item')->where(array('id'=>I('itemId')))->find();
    //     if ($price > $item['highest']) {
    //         M('item')->where(array('id'=>I('itemId')))->save(array('highest'=>$price,'owner'=>$name));
    //     }
    //     return json_encode(array('ok'=>true));
    // }
}