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
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $items = M('item')->where(array('auction_id'=>$auction['id']))->order('number asc')->select();

        $actions = [];
        for ($i = 0; $i < count($items); $i++) { 
            $tmp = M('action')->where(array('item_id'=>$items[$i]['id'], 'user_id'=>$_SESSION['uid']))->order('timestamp desc')->select();
            if (count($tmp) == 0) {
                continue;
            }
            $tmp = $tmp[0];
            $tmp['highest'] = $items[$i]['highest'];
            
            $actions[] = $tmp;
        }

        $this->actions = $actions;

        $results = [];
        for ($i = 0; $i < count($items); $i++) { 
            $tmp = M('action')->where(array('item_id'=>$items[$i]['id'], 'user_id'=>$_SESSION['uid']))->order('timestamp desc')->select();
            if (count($tmp) == 0) {
                continue;
            }

            $tmp = M('action')->where(array('item_id'=>$items[$i]['id']))->order('timestamp desc')->select();
            $users = array();
            $rank = array();
            for ($j = 0; $j < count($tmp); $j++) { 
                if (!array_key_exists($tmp[$j]['user_id'], $users)) {
                    $users[$tmp[$j]['user_id']] = $tmp[$j]['price'];
                    $rank[] = array('user_id'=>$tmp[$j]['user_id'], 'price'=>$tmp[$j]['price']);
                }
            }
            usort($rank, "cmp");

            $flag = false;
            for ($j = 0; $j < $items[$i]['amount']; $j++) {
                if ($rank[$j]['user_id'] == $_SESSION['uid']) {
                    $flag = true;
                    break;
                }
            }
            if ($flag) {
                $results[] = $items[$i];
            }
        }

        $this->auction = $auction;
        $this->results = $results;
        $this->now = time();

        $this->display();
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
        $item = M('item')->where(array('id'=>I('item_id')))->find();
        $auction = M('auction')->where(array('status'=>'当前'))->find();
        $user = M('user')->where(array('id'=>I('user_id')))->find();

        if ($item['owner'] == $user['id']) {
            echo json_encode(array('result'=>"error", "error"=>"您已经是最高价出价者了 - You are already owner of the highest price"));
        }
        else {
            if ($item['highest'] == 0) {
                if (I('price') < $item['start']) {
                    echo json_encode(array('result'=>"error", "error"=>"您的出价不符合要求 - Your price failed to meet the requirements"));
                }
                else {
                    $data = array(
                        'auction_id' => $auction['id'],
                        'user_id' => I('user_id'),
                        'item_id' => I('item_id'),
                        'timestamp' => time(),
                        'number' => $item['number'],
                        'title' => $item['title'],
                        'name' => $user['name'],
                        'price' => I('price'),
                        'cellphone' => $user['cellphone'],
                        );
                    M('action')->data($data)->add();
                    M('item')->where(array('id'=>I('item_id')))->save(array('highest'=>$data['price'], 'owner'=>$user['id']));
                    echo json_encode(array('result'=>"ok"));
                }
            }
            else {
                if (I('price') < $item['highest'] + $item['step']) {
                    echo json_encode(array('result'=>"error", "error"=>"您的出价不符合要求 - Your price failed to meet the requirements"));
                }
                else {
                    $data = array(
                        'auction_id' => $auction['id'],
                        'user_id' => I('user_id'),
                        'item_id' => I('item_id'),
                        'timestamp' => time(),
                        'number' => $item['number'],
                        'title' => $item['title'],
                        'name' => $user['name'],
                        'price' => I('price'),
                        'cellphone' => $user['cellphone'],
                        );
                    M('action')->data($data)->add();
                    M('item')->where(array('id'=>I('item_id')))->save(array('highest'=>$data['price'], 'owner'=>$user['id']));
                    echo json_encode(array('result'=>"ok"));
                }
            }
        }        
    }
}