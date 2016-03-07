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
     * 获取资讯日期
     */
    public function getInformationDate($map)
    {
        $informationDate = D("Information")->where($map)->field("addtime")->select();
        return get_arr_k_amount($informationDate,'addtime');
    }

    private function getMouthSoltInformation($date_start,$date_end)
    {
        $Information = D('Information');
        $mouth_solt = get_mouth_solt($date_start,$date_end);
        $map['status'] = ['in','2,3,4'];
        foreach($mouth_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_information[$k]['mouth_solt'] = $v;
            $mouth_solt_information[$k]['information'] = $Information->field('itemid,content')->where($map)->select();
            $mouth_solt_information[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_information[$k]['trade_information'] = get_arr_k_amount($mouth_solt_information[$k]['information'],'information');
            unset($mouth_solt_information[$k]['trades']);
        }
        return $mouth_solt_information;
    }

    /**
     * 月资讯总数
     * @author wodrow
     */
    public function monthlyInformation()
    {
        $this->assign('mouth_solt_information',$this->getMouthSoltInformation($this->date_start,$this->date_end));
        $this->display();
    }
}