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
//    public function monthlyInformation()
//    {
//        //查询月资讯总数
//        $Information = D('Information');
//        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];
//        $field = ['itemid','addtime'];
//        $sel_information = $Information->where($map)->field($field)->select();
//
//        //数据重组
//        $legend_number = Tools::getCols($sel_information,'itemid',true);
//        $legend_data = Tools::getCols($sel_information,'addtime',true);
//
//        //注入显示
//        $this->assign([]);
//        $this->display();
//        }


    private function getMouthSoltInformation($date_start,$date_end)
    {
        $Information = D('Information');
        $mouth_solt = get_mouth_solt($date_start,$date_end);
        $map['status'] = ['in','2,3,4'];
        foreach($mouth_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_information[$k]['mouth_solt'] = $v;
            $mouth_solt_information[$k]['trades'] = $Information->field('itemid')->where($map)->select();
            $mouth_solt_information[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_information[$k]['information_amount'] = get_arr_k_amount($mouth_solt_information[$k]['trades'],'itemid');
            unset($mouth_solt_information[$k]['trades']);
        }
        return $mouth_solt_information;
    }

    /**
     * 月付款
     * @author wodrow
     */
    public function monthlyInformation()
    {
        $this->assign('mouth_solt_information',$this->getMouthSoltInformation($this->date_start,$this->date_end));
        $this->display();
    }
}