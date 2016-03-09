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

    /**
     * 月付款
     * @author wodrow
     */
    public function monthlyInformation()
    {
        $Information = D('Information');
        $map['status'] = ['in','2,3,4'];
        //查询数据
        $mouth_solt = get_mouth_solt($this->date_start,$this->date_end);
        foreach($mouth_solt as $k => $v){
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_information[$k]['mouth_solt'] = $v;
            $x = $Information->field('itemid')->where($map)->select();
            $mouth_solt_information[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_information[$k]['information_count'] = count($x);
        }

        //重组数据
        $xAxis_data = Tools::arr2str(Tools::getCols($mouth_solt_information,'mouth_name',true));
        $series_data = Tools::arr2str(Tools::getCols($mouth_solt_information,'information_count'));

        //注入显示
        $this->assign(['xAxis_data'=>$xAxis_data,'series_data'=>$series_data]);
        $this->display();
    }
}