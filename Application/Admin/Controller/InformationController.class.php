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
     *
     * @Edwin
     */
    public function monthlyInformation()
    {
        //查询月资讯总数
        $Information = D('Information');
        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];
        $field = ['itemid','addtime'];
        $sel_information = $Information->where($map)->field($field)->select();
        foreach($sel_information as $k=>$v){
            $x = Tools::str2arr($v['addtime']);
            $sel_information[$k['addtime']]=$x[0];
        }


    }

}