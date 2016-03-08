<?php
/**
 * Created by PhpStorm.
 * User: iredbaby
 * Date: 16-3-5
 * Time: 上午11:45
 */

namespace Admin\Controller;



use Common\Tools;

class InformationController extends AdminController
{
    //默认配置 对栏目权限判断
    public function information_index()
    {
        $this->display('information_index');
    }

    /*
     *月资讯总数
     * @Edwin
     */
    public function monthlyInformation()
    {
        //查询月资讯总数
        $Information = D('Information');
        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];
        $field = ['itemid','addtime'];
        $sel_information = $Information->where($map)->field($field)->select();
        //var_dump($sel_information);
        //数据重组
        $legend_data = Tools::getCols($sel_information,'itemid',true);
        $number = count($legend_data);
        //}
        //注入显示
        $this->assign(['legend_data'=>$legend_data]);
        $this->display();
        }
}