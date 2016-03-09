<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/7/16
 * Time: 3:01 PM
 */

namespace Admin\Controller;


use Common\Controller\AuthController;
use Common\Tools;

class ProductController extends AuthController
{
    private $all_cate_list; // 所有分类
    private $all_cate_hash; // 所有分类hash

    /**
     * 获取分类hash
     */
    private function getCateHash(){
        $Category = D('Category');
        $this->all_cate_list = $Category->where(['moduleid'=>5])->field(['catid','catname'])->select();
        $this->all_cate_hash = Tools::toHashmap($this->all_cate_list,'catid','catname');
    }

    /**
        * 产品类别比例
        */
    public function categoryRatio(){
        // 查询添加的产品
        $Product = D('Product');
        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];
        $field = ['itemid','catid'];
        $sel_product_list = $Product->where($map)->field($field)->select();
        foreach($sel_product_list as $k => $v){
            $x = Tools::str2arr($v['catid']);
            $sel_product_list[$k]['catid'] = $x[0];
        }

        // 分类hash
        $this->getCateHash();

        // 产品分类详情
        $sel_cat_info = Tools::groupBy($sel_product_list,'catid');
        foreach($sel_cat_info as $k =>$v){
            unset($sel_cat_info[$k]);
            if($this->all_cate_hash[$k]){
                $sel_cat_info[$k]['catid'] = $k;
                $sel_cat_info[$k]['catname'] = $this->all_cate_hash[$k];
                $sel_cat_info[$k]['count'] = count($v);
            }
        }
        sort($sel_cat_info);

        // 数据重组
        $legend_data = Tools::arr2str(Tools::getCols($sel_cat_info,'catname',true));
        foreach($sel_cat_info as $k => $v){
            $series_data[] = "{value:".$v['count'].", name:'".$v['catname']."'}";
        }
        $series_data = Tools::arr2str($series_data);

        // 注入显示
        $this->assign(['legend_data'=>$legend_data,'series_data'=>$series_data]);
        $this->display();
    }

    /**
     * 各类产品数量
     *
     * 数据重组
     * legend: {
     *      data:['直接访问','邮件营销','联盟广告','视频广告','谷歌','必应','其他']
     * },
     * xAxis : [
     *      {
     *          type : 'category',
     *          data : ['周一','周二','周三','周四','周五','周六','周日']
     *      }
     * ],
     * series : [
     *      {
     *          name:'直接访问',
     *          type:'bar',
     *          data:[320, 332, 301, 334, 390, 330, 320]
     *      },
     *      ...
     * ]
     */
    public function categoryTotal(){}

    /**
     * 产品上传月走势
     */
    public function uploadMonthlyTrend(){
        //数据查询

        $this->display();
    }
}