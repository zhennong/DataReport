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




    private function getMouthSoltInformation($date_start,$date_end)
    {
        $Trade = D('Information');
        $mouth_solt = get_mouth_solt($date_start,$date_end);
        $map['status'] = ['in','2,3,4'];
        foreach($mouth_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_information[$k]['mouth_solt'] = $v;
            $mouth_solt_information[$k]['information'] = $Trade->field('itemid,amount')->where($map)->select();
            $mouth_solt_information[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_information[$k]['trade_information'] = get_arr_k_amount($mouth_solt_information[$k]['information'],'amount');
            unset($mouth_solt_information[$k]['information']);
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