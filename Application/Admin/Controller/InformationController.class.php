<?php
/**
 * Created by PhpStorm.
 * User: iredbaby
 * Date: 16-3-5
 * Time: 上午11:45
 */

namespace Admin\Controller;



class InformationController extends AdminController
{
    //默认配置 对栏目权限判断
    public function information_index()
    {
        $this->display('information_index');
    }
    /**
     * 月度资讯
     */
    public function monthlyInformation()
    {
        //$Information=D("Information")->field('itemid')->select();
        $Information=D("Information");
        $monthlyCount=count($Information->select('itemid'));
        var_dump($monthlyCount);
    }
}