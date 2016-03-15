<?php

namespace Admin\Controller;

use Common\Tools;

class LogController extends AdminController
{
    /*
     * 登录日志
     * @author houpanqi <houpanqi@qq.com>
     */

    public function Loglogin()
    {
        $mod = D('Login');
        $count = $mod->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $mod->order('logintime DESC')->limit($_GET['p'] . ',25')->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display('Log/login');
    }

    /*
     * 操作日志
     * @author houpanqi <houpanqi@qq.com>
     */

    public function LogOperation()
    {
        $mod = D('Operation');
        $count = $mod->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $mod->order('logtime DESC')->limit($_GET['p'] . ',25')->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display('Log/operation');
    }

    /*
     * 产品浏览记录
     * @author Edwin <junqianhen@gmail.com>
     */

    public function LogScanGoods()
    {
        $mod = D('Loglook');
        $count = $mod->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $mod->order('addtime DESC')->limit($_GET['p'], 25)->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display("Log/scangoods");
    }

    /*
     * 积分流水记录
     * @author Edwin <junqianhen@gmail.com>
     */
    public function LogIntegralWater()
    {
        $mod = D('LogIntegralWater');
        $count = $mod->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $mod->order('addtime DESC')->limit($_GET['p'] . ',25')->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display("Log/ntegralWater");
    }

}
