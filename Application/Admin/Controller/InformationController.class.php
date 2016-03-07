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

    /**
     * 资讯年总量
     * @Edwin
     */

    public function monthlyInformation()
    {
        $informationCount = D('Information');
        $year_solt = get_year_solt($this->year_start,$this->year_end);
        $map['status'] = ['in','2,3,4'];
        foreach($year_solt as $k => $v){
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $year_solt_information[$k]['year_solt'] = $v;
            $year_solt_information[$k]['information'] = $informationCount->field('count(addtime)')->where($map)->select();
            $year_solt_information[$k]['year_name'] = date("Y", $v['start']['ts']);
            $year_solt_information[$k]['infromation_amount'] = get_arr_k_amount($year_solt_information[$k]['information'],'amount');
            unset($year_solt_information[$k]['information']);
        }
        $this->assign('year_solt_trades',$year_solt_information);
        $this->display();
    }
}