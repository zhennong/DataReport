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
    private function getCateHash()
    {
        $Category = D('Category');
        $this->all_cate_list = $Category->where(['moduleid' => 5])->field(['catid', 'catname'])->select();
        $this->all_cate_hash = Tools::toHashmap($this->all_cate_list, 'catid', 'catname');

    }

    /**
     * 获取在一个价格段位的商品id数组
     * @param $products [['itemid','price']]
     * @param $range ['start_price'=>1,'end_price'=>100]
     * @return array []
     */
    private function getPriceRangeProductIDs($products, $range = ['start_price' => 1, 'end_price' => 100])
    {
        foreach ($products as $k => $v) {
            if ($v['price'] > $range['start_price'] && $v['price'] < $range['end_price']) {
                $ids[] = $v['itemid'];
            }
        }
        return $ids;
    }

    /**
     * 根获取在一个价格段位的订单
     * @param $trades
     * @param $product_ids
     * @return array [[]]
     */
    private function getPriceRangeTrades($trades, $range = ['start_price' => 1, 'end_price' => 100])
    {
        foreach ($trades as $k => $v) {
            if ($v['price'] > $range['start_price'] && $v['price'] < $range['end_price']) {
                $sel_trades[] = $v;
            }
        }
        return $sel_trades;
    }

    /**
     * 产品类别比例
     */
    public function categoryRatio()
    {
        // 查询添加的产品
        $Product = D('Product');
        $map['addtime'] = [['gt', $this->month_start], ['lt', $this->month_end]];
        $field = ['itemid', 'catid'];
        $sel_product_list = $Product->where($map)->field($field)->select();
        foreach ($sel_product_list as $k => $v) {
            $x = Tools::str2arr($v['catid']);
            $sel_product_list[$k]['catid'] = $x[0];
        }
        // 分类hash
        $this->getCateHash();

        // 产品分类详情
        $sel_cat_info = Tools::groupBy($sel_product_list, 'catid');
        foreach ($sel_cat_info as $k => $v) {
            unset($sel_cat_info[$k]);
            if ($this->all_cate_hash[$k]) {
                $sel_cat_info[$k]['catid'] = $k;
                $sel_cat_info[$k]['catname'] = $this->all_cate_hash[$k];
                $sel_cat_info[$k]['count'] = count($v);
            }
        }
        sort($sel_cat_info);

        // 数据重组
        $legend_data = Tools::arr2str(Tools::getCols($sel_cat_info, 'catname', true));
        foreach ($sel_cat_info as $k => $v) {
            $series_data[] = "{value:" . $v['count'] . ", name:'" . $v['catname'] . "'}";
        }
        $series_data = Tools::arr2str($series_data);

        // 注入显示
        $this->assign(['legend_data' => $legend_data, 'series_data' => $series_data]);
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
    public function categoryTotal()
    {
    }

    /**
     * 产品上传月走势
     */
    public function uploadMonthlyTrend()
    {
        $Product = D('Product');
        //数据查询
        foreach ($this->month_solt as $k => $v) {
            $upload_products[$k]['month'] = $v['start']['date'];
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $upload_products[$k]['count'] = count($Product->where($map)->field('itemid')->select());
        }

        //数据重组
        $xAxis_data = Tools::arr2str(Tools::getCols($upload_products, 'month', true));
        $series_data = Tools::arr2str(Tools::getCols($upload_products, 'count'));

        // 注入显示
        $this->assign(['xAxis_data' => $xAxis_data, 'series_data' => $series_data]);
        $this->display();
    }

    /**
     * 价格区间分布图(销量 产品数量 交易额 订单总数)
     */
    public function price_range_information()
    {
        $map['status'] = 3;
        $map['price'] = ['gt', 0];
        $Product = D('Product');
        $Trade = D('Trade');
        $products = $Product->where($map)->field(['itemid', 'price'])->select();
        $trades = $Trade->where(['status' => ['in', [2, 3, 4]]])->field(['itemid', 'p_id', 'price', 'total', 'amount'])->select(); // 订单
        foreach ($this->price_range as $k => $v) {
            $price_range_data[$k] = $v;
            // 产品数量
            $product_ids = $this->getPriceRangeProductIDs($products, $v);
            $price_range_data[$k]['product_count'] = count($product_ids);

            $sel_trades = $this->getPriceRangeTrades($trades, $v);
            // 订单总数
            $price_range_data[$k]['trade_count'] = count($sel_trades);
            // 销量
            $price_range_data[$k]['trade_total'] = get_arr_k_amount($sel_trades, 'total');
            // 交易额
            $price_range_data[$k]['trade_amount'] = get_arr_k_amount($sel_trades, 'amount');
        }

        // 数据重组
        /*$xAxis_data = "'产品数量','订单总数','销量','交易额'";
        $legend_data = Tools::getCols($this->price_range,'range_name',true);
        foreach($price_range_data as $k => $v){
            $sel_trades[] = "{name:'".$v['range_name']."',type:'bar',data:[".$v['product_count'].",".$v['trade_count'].",".$v['trade_total'].",".$v['trade_amount']."]}";
        }
        $series = Tools::arr2str($sel_trades);
        $this->assign(['legend_data'=>$legend_data,'xAxis_data'=>$xAxis_data,'series'=>$series]);*/
        $legend_data = ['产品数量', '订单总数', '销量', '交易额'];
        $xAxis_data = Tools::arr2str(Tools::getCols($this->price_range, 'range_name', true));
        $series['product_count'] = Tools::arr2str(Tools::getCols($price_range_data, 'product_count'));
        $series['trade_count'] = Tools::arr2str(Tools::getCols($price_range_data, 'trade_count'));
        $series['trade_total'] = Tools::arr2str(Tools::getCols($price_range_data, 'trade_total'));
        $series['trade_amount'] = Tools::arr2str(Tools::getCols($price_range_data, 'trade_amount'));

        // 注入显示
        $this->assign(['legend_data' => $legend_data, 'xAxis_data' => $xAxis_data, 'series' => $series]);
        $this->display();
    }

    /**
     * 厂家会员出货统计 member username trade sell
     * 订单数 出货数 在售产品数sell 销售总额
     */
    public function shipmentStatistics()
    {
        $this->display();
    }

    /**
     * ajax获取厂家会员出货统计
     */
    public function ajaxGetSellShipmentStatistics()
    {
        $column_index = [
            "0" => "userid",
            "1" => "username",
            "2" => "company",
            "3" => "product_total",
            "4" => "trade_count",
            "5" => "trade_total",
            "6" => "trade_amount",
        ];
        $draw = $_GET['draw'];//这个值作者会直接返回给前台
        $start = $_GET['start'];
        $limit = $_GET['length'];
        $order = $_GET['order'];
        $order = "{$column_index[$order[0]['column']]} {$order[0]['dir']}";
        foreach ($_GET['columns'] as $k => $v) {
            if ($v['search']['value'] != '') {
                $y[] = "{$column_index[$v['data']]} LIKE '%{$v[search][value]}%'";
            }
        }
        $search = '';
        if (count($y) > 0) {
            $search = " AND " . Tools::arr2str($y, " AND ");
        }
        $x = $this->MallDb->list_query("SELECT COUNT(x.userid) as count FROM (SELECT member.userid
FROM __MALL_member AS member
LEFT JOIN __MALL_finance_trade AS trade ON member.username = trade.seller AND trade.status IN(1,2,3,4)
WHERE member.groupid = 6 {$search}
GROUP BY member.username) AS x");
        $total = $x[0]['count'];
        $data = $this->getSellShipmentStatistics($start, $limit, $order, $search);
        foreach ($data as $k => $v) {
            foreach ($column_index as $key => $value) {
                $x[$k][] = $v[$value];
            }
        }
        //获取Datatables发送的参数 必要
        $show = [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $x,
        ];
        $x = json_encode($show);
        echo $x;
    }

    /**
     * 获取厂家会员出货统计
     * @param int $start
     * @param int $limit
     * @param $order
     * @return [[]]
     */
    private function getSellShipmentStatistics($start = 0, $limit = 10, $order = "trade_amount DESC", $search = '')
    {
        $sql = "SELECT member.userid, member.username, member.company, COUNT(trade.itemid) AS trade_count, SUM(trade.total) AS trade_total, SUM(trade.amount) AS trade_amount
FROM __MALL_member AS member
LEFT JOIN __MALL_finance_trade AS trade ON member.username = trade.seller AND trade.status IN(1,2,3,4)
WHERE member.groupid = 6 {$search}
GROUP BY member.username
ORDER BY {$order}
LIMIT {$start}, {$limit}";
        $sells = $this->MallDb->list_query($sql);

        $Product = D('Product');
        foreach ($sells as $k => $v) {
            $x = $Product->where([['username' => $v['username']]])->field("count(itemid) AS product_total")->group('username')->select();
            $sells[$k]['product_total'] = $x[0]['product_total'];
        }
        return $sells;
    }

    /**
     * 产品收藏统计
     */
    public function productStatistics()
    {
        $Favorite = D('Favorite');
        $favorites = $Favorite->where([['p_id' => ['gt', 0]]])->field("p_id AS product_id, title, COUNT(itemid) AS favorite_count")->group("p_id")->order("favorite_count DESC")->limit("0,100")->select();
        $this->assign(['favorites' => $favorites]);
        $this->display();
    }

    /**
     * 产品销量排行榜 销量 成交额
     */
    public function productSaleRankingList()
    {
        $this->display();
    }

    /**
     * ajax获取产品销量
     */
    public function ajaxGetProductSaleRankingList()
    {
        $column_index = [
            'p_id',
            'title',
            'trade_count',
            'trade_total',
            'trade_amount',
        ];
        $draw = $_GET['draw'];//这个值作者会直接返回给前台
        $start = $_GET['start'];
        $limit = $_GET['length'];
        $order = $_GET['order'];
        $order = "{$column_index[$order[0]['column']]} {$order[0]['dir']}";
        foreach ($_GET['columns'] as $k => $v) {
            if ($v['search']['value'] != '') {
                $y[] = "{$column_index[$v['data']]} LIKE '%{$v[search][value]}%'";
            }
        }
        $search = '';
        if (count($y) > 0) {
            $search = " AND " . Tools::arr2str($y, " AND ");
        }
        $Trade = D('Trade');
        $y = $Trade->where("status IN(2,3,4) {$search}")->field("p_id")->group("p_id")->select();
        $total = count($y);
        $data = $Trade->where("status IN(2,3,4) {$search}")->field("p_id, title, COUNT(itemid) AS trade_count, SUM(total) AS trade_total, SUM(amount) AS trade_amount")->group("p_id")->order($order)->limit("{$start}, {$limit}")->select();
        foreach ($data as $k => $v) {
            foreach ($column_index as $key => $value) {
                $x[$k][] = $v[$value];
            }
        }

        //获取Datatables发送的参数 必要
        $show = [
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $x,
        ];
        $x = json_encode($show);
        echo $x;
    }
}