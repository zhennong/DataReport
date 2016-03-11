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
        $map['status'] = ['in', '2,3,4'];
        //查询数据
        $mouth_solt = get_month_solt($this->month_start, $this->month_end);
        foreach ($mouth_solt as $k => $v) {
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
        $xAxis_data = Tools::arr2str(Tools::getCols($mouth_solt_information, 'mouth_name', true));
        $series_data_inforamtion_information = Tools::arr2str(Tools::getCols($mouth_solt_information, 'information_count'));

        //重组数据_月病虫害数据
        $series_data_inforamtion_pests = Tools::arr2str(Tools::getCols($mouth_solt_pests, 'pests_count'));

        //重组数据_月农药中毒数据
        $series_data_inforamtion_poisoning = Tools::arr2str(Tools::getCols($mouth_solt_poisoning, 'poisoning_count'));

        //注入显示_月资讯数据/月病虫害数据/月农药中毒数据
        $this->assign(['xAxis_data' => $xAxis_data, 'series_data_information' => $series_data_inforamtion_information, 'series_data_pests' => $series_data_inforamtion_pests, 'series_data_poisoning' => $series_data_inforamtion_poisoning]);
        $this->display();
    }

    /**
     * 资讯/病虫害/农药中毒饼形图
     * @Edwin
     */
    public function ratioInformation()
    {

        $Information = D('Information');
        $map['addtime'] = [['gt', $this->month_start], ['lt', $this->month_end]];

        //查询数据
        $mouth_solt = get_month_solt($this->month_start, $this->month_end);
        foreach ($mouth_solt as $k => $v) {
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

        //重组数据_查询月份资讯总数据
        $series_data_inforamtion_information = get_arr_k_amount($mouth_solt_information, 'information_count');

        //重组数据_查询月份病虫害数总数据
        $series_data_inforamtion_pests = get_arr_k_amount($mouth_solt_pests, 'pests_count');

        //重组数据_查询月份农药中毒总数据
        $series_data_inforamtion_poisoning = get_arr_k_amount($mouth_solt_poisoning, 'poisoning_count');

        //注入显示
        $this->assign(['series_data_information' => $series_data_inforamtion_information, 'series_data_pests' => $series_data_inforamtion_pests, 'series_data_poisoning' => $series_data_inforamtion_poisoning]);
        $this->display();
    }

    /*
     * 资讯分类饼形图
     * @Edwin
     */
    private $all_information_list; // 所有分类
    private $all_information_hash; // 所有分类hash

    /**
     * 获取资讯分类hash
     */
    private function getCateHash()
    {
        $Category = D('Category');
        $map['moduleid'] = ['in',[21,23,26]];
        $this->all_information_list = $Category->where($map)->field(['catid', 'catname'])->select();
        $this->all_information_hash = Tools::toHashmap($this->all_information_list, 'catid', 'catname');
    }


    /**
     * 三个栏目下的文章类别比例图 (以顶级分类为单位进行区分) (三个饼形图)
     */
    public function classes_Information()
    {
        $this->getCateHash();
        // 查询资讯
        $Information = D('Information');
        $field = ['itemid', 'catid'];
        $sel_information_list = $Information->where()->field($field)->select();
        $cat_group = Tools::groupBy($sel_information_list,'catid');
        foreach($cat_group as $k => $v){
            $x[$k]['catid'] = $v[0]['catid'];
            $x[$k]['catname'] = $this->all_information_hash[$v[0]['catid']];
            $x[$k]['count'] = count($v);
        }
        sort($x);

        // 查询虫害资讯
        $Pests = D('Pests');
        $field = ['itemid', 'catid'];
        $map['catid'] = ['in',[6086,6324,6087,6088,6318,6319,6092,6093,6096,14408]];
        $sel_pests_list = $Pests->where($map)->field($field)->select();
        $cat_group = Tools::groupBy($sel_pests_list,'catid');
        foreach($cat_group as $k => $v){
            $x_pests[$k]['catid'] = $v[0]['catid'];
            $x_pests[$k]['catname'] = $this->all_information_hash[$v[0]['catid']];
            $x_pests[$k]['count'] = count($v);
        }
        sort($x_pests);

        // 查询中毒资讯
        $Poisoning = D('Poisoning');
        $field = ['itemid', 'catid'];
        $map['catid'] = ['in',[12749,12750,12751,12752,12753,12776]];
        $sel_poisoning_list = $Poisoning->where($map)->field($field)->select();
        $cat_group = Tools::groupBy($sel_poisoning_list,'catid');
        foreach($cat_group as $k => $v){
            $x_poisoning[$k]['catid'] = $v[0]['catid'];
            $x_poisoning[$k]['catname'] = $this->all_information_hash[$v[0]['catid']];
            $x_poisoning[$k]['count'] = count($v);
        }
        sort($x_poisoning);

        //重组资讯数据
        $legend_data_inforamtion = Tools::arr2str(Tools::getCols($x, 'catname', true));
        foreach ($x as $k => $v) {
            $series_data_inforamtion[] = "{value:" . $v['count'] . ", name:'" . $v['catname'] . "'}";
        }
        $series_data_inforamtion = Tools::arr2str($series_data_inforamtion);


        //重组病虫害数据
        $legend_data_pests = Tools::arr2str(Tools::getCols($x_pests, 'catname', true));
        foreach ($x_pests as $k => $v) {
            $series_data_pests[] = "{value:" . $v['count'] . ", name:'" . $v['catname'] . "'}";
        }
        $series_data_pests = Tools::arr2str($series_data_pests);

        //重组农药中毒资讯数据
        $legend_data_poisoning = Tools::arr2str(Tools::getCols($x_poisoning, 'catname', true));
        foreach ($x_poisoning as $k => $v) {
            $series_data_poisoning[] = "{value:" . $v['count'] . ", name:'" . $v['catname'] . "'}";
        }
        $series_data_poisoning = Tools::arr2str($series_data_poisoning);


        //注入显示
        $this->assign(['legend_data_information' => $legend_data_inforamtion,'series_data_information' => $series_data_inforamtion,'legend_data_pests' => $legend_data_pests,'series_data_pests' => $series_data_pests,'legend_data_poisoning' => $legend_data_poisoning,'series_data_poisoning' => $series_data_poisoning]);
        $this->display();
    }
}