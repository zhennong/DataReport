<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-14
 * Time: 上午9:14
 */

namespace Admin\Controller;

use Common\Tools;
class Behavior extends AdminController
{
    //默认配置 对栏目权限判断
    public function behavior_index()
    {
        $this->display('behavior_index');
    }

    /*
     * 改价记录
     * @Edwin
     */
    public function ChangePrice()
    {

        $this->display();
    }

    /*
     * 订单操作记录
     */
    public function OrderOperation()
    {
        $this->display();
    }
}