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
     * 月资讯总量柱状图
     * @Edwin
     */
    public function monthlyInformation()
    {
        /*
         * 月资讯总量
         */
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

        /*
         * 月病虫害总量
         */
        $Pests = D('Pests');
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_pests[$k]['mouth_solt'] = $v;
            $x = $Pests->field('itemid')->where($map)->select();
            $mouth_solt_pests[$k]['pests_count'] = count($x);
        }

        /*
        * 月农药中毒总量
        */
        $Poisoning = D('Poisoning');
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_poisoning[$k]['mouth_solt'] = $v;
            $x = $Poisoning->field('itemid')->where($map)->select();
            $mouth_solt_poisoning[$k]['poisoning_count'] = count($x);
        }

        //重组数据_月资讯数据
        $xAxis_data = Tools::arr2str(Tools::getCols($mouth_solt_information,'mouth_name',true));
        $series_data_information = Tools::arr2str(Tools::getCols($mouth_solt_information,'information_count'));

        //重组数据_月病虫害数据
        $series_data_pests = Tools::arr2str(Tools::getCols($mouth_solt_pests,'pests_count'));

        //重组数据_月农药中毒数据
        $series_data_poisoning = Tools::arr2str(Tools::getCols($mouth_solt_poisoning,'poisoning_count'));

        //注入显示_月资讯数据/月病虫害数据/月农药中毒数据
        $this->assign(['xAxis_data'=>$xAxis_data,'series_data_information'=>$series_data_information,'series_data_pests'=>$series_data_pests,'series_data_poisoning'=>$series_data_poisoning]);
        $this->display();
    }

    /**
     * 资讯/病虫害/农药中毒饼形图
     * @Edwin
     */
    public function ratioInformation(){

        $Information = D('Information');
        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];

        //查询数据
        $mouth_solt = get_mouth_solt($this->date_start,$this->date_end);
        foreach($mouth_solt as $k => $v){
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_information[$k]['mouth_solt'] = $v;
            $x = $Information->field('itemid')->where($map)->select();
            $mouth_solt_information[$k]['information_count'] = count($x);
        }

        /*
         * 月病虫害总量
         */
        $Pests = D('Pests');
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_pests[$k]['mouth_solt'] = $v;
            $x = $Pests->field('itemid')->where($map)->select();
            $mouth_solt_pests[$k]['pests_count'] = count($x);
        }

        /*
        * 月农药中毒总量
        */
        $Poisoning = D('Poisoning');
        foreach ($mouth_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_poisoning[$k]['mouth_solt'] = $v;
            $x = $Poisoning->field('itemid')->where($map)->select();
            $mouth_solt_poisoning[$k]['poisoning_count'] = count($x);
        }
        //重组数据_月资讯数据
        $series_data_information = Tools::arr2str(Tools::getCols($mouth_solt_information,'information_count'));

        //重组数据_月病虫害数据
        $series_data_pests = Tools::arr2str(Tools::getCols($mouth_solt_pests,'pests_count'));

        //重组数据_月农药中毒数据
        $series_data_poisoning = Tools::arr2str(Tools::getCols($mouth_solt_poisoning,'poisoning_count'));

        //判断数据是否为0，如果是则赋值为0
        if($series_data_information==0||$series_data_poisoning==0||$series_data_pests==0){
            $series_data_information = 0;
            $series_data_poisoning = 0;
            $series_data_pests = 0;
        }
        $this->assign(['series_data_information'=>$series_data_information,'series_data_pests'=>$series_data_pests,'series_data_poisoning'=>$series_data_poisoning]);
        //注入显示

        $this->display();
    }
}