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
    /**
        * 产品类别比例
        */
    public function categoryRatio(){
            // 添加的产品
        $Product = D('Product');
        $map['addtime'] = [['gt',$this->date_start],['lt',$this->date_end]];
        $field = ['itemid','catid'];
        $sel_product_list = $Product->where($map)->field($field)->select();
        foreach($sel_product_list as $k => $v){
            $x = Tools::str2arr($v['catid']);
            $sel_product_list[$k]['catid'] = $x[0];
        }

        // 分类hash
        $Category = D('Category');
        $all_cate_list = $Category->where(['moduleid'=>5])->field(['catid','catname'])->select();
        $all_cate_hash = Tools::toHashmap($all_cate_list,'catid','catname');

        // 产品分类详情
        $sel_cat_info = Tools::groupBy($sel_product_list,'catid');
        foreach($sel_cat_info as $k =>$v){
            unset($sel_cat_info[$k]);
            if($all_cate_hash[$k]){
                $sel_cat_info[$k]['catid'] = $k;
                $sel_cat_info[$k]['catname'] = $all_cate_hash[$k];
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
     */
    public function categoryTotal(){
        // 注入显示
        $this->display();
    }
}