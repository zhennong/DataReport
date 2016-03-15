<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-14
 * Time: 上午9:14
 */

namespace Admin\Controller;

use Common\Tools;
class BehaviorController extends AdminController
{
    //默认配置 对栏目权限判断
    public function behavior_index()
    {
        $this->display('behavior_index');
    }

    /*
     * 改价记录
     * @author Edwin
     */
    public function ChangePrice()
    {
        $change = D('Change');
        $count = $change->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $change->order('addtime DESC')->limit($_GET['p'],25)->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);

        $this->display();
    }

    /*
     * 订单操作记录
     * author Edwin
     */
    public function OrderOperation()
    {
        $orderOperation = D('OrderOperation');
        $count = $orderOperation->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $orderOperation->order('addtime DESC')->limit($_GET['p'],25)->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display();
    }
}