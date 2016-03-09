<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/7/16
 * Time: 3:01 PM
 */

namespace Admin\Controller;


use Admin\FixedData;
use Common\Controller\AuthController;
use Common\Tools;

class ProductController extends AuthController
{
    use FixedData;

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
        $map['addtime'] = [['gt',$this->month_start],['lt',$this->month_end]];
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
        $Product = D('Product');
        //数据查询
        foreach($this->month_solt as $k => $v){
            $upload_products[$k]['month'] = $v['start']['date'];
            $map['addtime'] = [['gt',$v['start']['ts']],['lt',$v['end']['ts']]];
            $upload_products[$k]['count'] = count($Product->where($map)->field('itemid')->select());
        }

        //数据重组
        $xAxis_data = Tools::arr2str(Tools::getCols($upload_products,'month',true));
        $series_data = Tools::arr2str(Tools::getCols($upload_products,'count'));

        // 注入显示
        $this->assign(['xAxis_data'=>$xAxis_data,'series_data'=>$series_data]);
        $this->display();
    }

    /**
     * 价格区间分布图(销量 产量 交易额 订单总数)
     */
    public function price_range_information(){
        $products = D('Product')->where("price > 0")->field(['itemid','price'])->select();
        foreach($this->price_range as $k => $v){
            $price_range_data[$k] = $v;
            // 销量
            // 产量
            // 交易额
            // 订单总数
        }
        // 注入显示
        $this->assign([]);
        $this->display();
    }

    /**
     * 获取在一个价格段位的商品id数组
     * @param $products [['itemid','price']]
     * @param $range ['start_price'=>1,'end_price'=>100]
     * @return array []
     */
    private function getPriceRangeProductIDs($products,$range = ['start_price'=>1,'end_price'=>100]){
        foreach($products as $k => $v){
            if($v['price']>$range['start_price']&&$v['price']<$range['end_price']){
                $ids[] = $v['itemid'];
            }
        }
        return $ids;
    }
}