<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/14/16
 * Time: 10:09 AM
 */

namespace Admin\Controller;

use Common\Tools;

class TradeController extends AdminController
{

    //默认配置 对栏目权限判断
    public function trade_index()
    {
        $this->display('Trade/trade_index');
    }

    public function index()
    {
//        $this->redirect('orderTeand');
    }

    /**
     * 订单走势
     */
    public function orderTrend()
    {
        /**
         * $mouth_solt_trades = [
         *   ['k'=>[
         *       'trades' => [
         *           'k'=>['itemid'=>int,'amount'=>int],
         *       ],
         *       'trade_total'=>int,
         *       'trade_amount'=>int,
         *       'mouth_name'=>data("Y-m",time),
         *       'mouth_solt'=>[
         *           'start'=>['ts'=>timestrap,'date'=>'Y-m'],
         *           'end'=>['ts'=>timestrap,'date'=>'Y-m']
         *       ]
         *   ]]
         * ];
         */

        //按照统计
//        $mouth_solt = get_day_solt($this->month_start,$this->month_end);
//        $model = D('Trade');
//        foreach ($mouth_solt as $k => $v) {
//            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
//            $map['status'] = ['in', '2,3,4'];
//            $mouth_solt_trades[$k]['trades'] = $model->field('itemid,amount')->where($map)->select();
//            $x = count($mouth_solt_trades[$k]['trades']);
//            $x = $x==0?'':$x;
//            $mouth_solt_trades[$k]['trade_total'] = $x;
//            $y = get_arr_k_amount($mouth_solt_trades[$k]['trades'],'amount');
//            $y = $y==0?'':$y;
//            $mouth_solt_trades[$k]['trade_amount'] = $y;
//            $mouth_solt_trades[$k]['mouth_solt'] = $v;
//            $mouth_solt_trades[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
//            unset($mouth_solt_trades[$k]['trades']);
//        }
//        $this->assign(['mouth_solt_trades' => $mouth_solt_trades]);
//        $this->display();

        //按照天数统计

        $day_solt = get_day_solt($this->day_start,$this->day_end);
        $model = D('Trade');
        foreach ($day_solt as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $map['status'] = ['in', '2,3,4'];
            $day_solt_trades[$k]['trades'] = $model->field('itemid,amount')->where($map)->select();
            $x = count($day_solt_trades[$k]['trades']);
            $x = $x==0?'':$x;
            $day_solt_trades[$k]['trade_total'] = $x;
            $y = get_arr_k_amount($day_solt_trades[$k]['trades'],'amount');
            $y = $y==0?'':$y;
            $day_solt_trades[$k]['trade_amount'] = $y;
            $day_solt_trades[$k]['day_solt'] = $v;
            $day_solt_trades[$k]['day_name'] = date("Y-m-d", $v['start']['ts']);
            unset($day_solt_trades[$k]['trades']);
        }
        $this->assign(['day_solt_trades' => $day_solt_trades]);
        $this->display();
    }


    /**
     * 下单时段（24小时制）
     */
    public function orderTime(){		
        $map['addtime'] = $this->mapMonthRange;;
        $map['status'] = ['in','2,3,4'];
        $trades = D('Trade')->where($map)->field("addtime")->select();
        for ($i = 0; $i < 24; $i++) {
            $x = $i;
	    $x < 10 ?  $x = "0{$x}" : $x;
            $time_solt_trades[$i]['time_name'] = $x . "点";
            foreach ($trades as $k => $v) {
		$x == date('H', $v['addtime']) ? $time_solt_trades[$i]['trade_total']++ : 0; 
            }
        }
        $this->assign(['time_solt_trades'=>$time_solt_trades]);
        $this->display();
    }

    /**
     * 付款时段（24小时制）
     * @author iredbaby
     */
    public function orderPay(){	
        $map['paytime'] = $this->mapMonthRange;
        $map['status'] = ['in','2,3,4'];
        $trades = D('Trade')->where($map)->field("paytime")->select();
        for ($i = 0; $i < 24; $i++) {
            $x = $i;
	    $x < 10 ?  $x = "0{$x}" : $x;	    
            $pay_solt_trades[$i]['pay_name'] = $x . "点";
            foreach ($trades as $k => $v) {		
		$x == date('H', $v['paytime']) ? $pay_solt_trades[$i]['trade_total']++ : 0;
            }
        }
        $this->assign(['pay_solt_trades'=>$pay_solt_trades]);
        $this->display();
    }

    /**
     * 发货时段（24小时制）
     * @author iredbaby
     */
    public function orderLogistics(){	
        $map['addtime'] = $this->mapMonthRange;;
        $map['status'] = ['in','2,3,4'];
        $Logistics = D('Logistics')->where($map)->field("addtime")->select();	
        for($i = 0;$i<24;$i++){
            $x = $i;	    
	    $x < 10 ?  $x = "0{$x}" : $x;	    
            $time_solt_trades[$i]['time_name'] = $x."点";    
	    foreach($Logistics as $k => $v){
		$x == date('H',$v['addtime'])?$time_solt_trades[$i]['trade_total'] ++ : 0;
            }  
        }	
        $this->assign(['time_solt_trades'=>$time_solt_trades]);
        $this->display();		
    }

    /**
     * 支付方式
     * @author iredbaby
     */
    public function orderPaytype() {
        $map['paytime'] = $this->mapMonthRange;
        $map['status'] = ['in','2,3,4'];
	$map['pay'] = ['neq',''];
        $trades = D('Trade')->where($map)->field('pay,count(pay) AS c')->group('pay')->select();	
	foreach ($trades as $k => $v) {
	    $title[] = $v['pay'];
	    $time_solt_trades[$k]['name'] = $v['pay'];	    
	    $time_solt_trades[$k]['value'] = $v['c'];
	}					
	$this->assign("title",implode("','",$title));
	$this->assign("data",json_encode($time_solt_trades));		 
        $this->display();	
    }

    /**
     * 客户退单
     * @author iredbaby
     * 算法：【选择日期】成功退款笔数/【选择日期】支付宝交易笔数*100%；
     * @param $trades_a  退款状态统计
     * @param $trades_b  交易成功状态统计
     * @author iredbaby
     */
    public function orderRate(){	
	$Trade = D('Trade');	
        $map_a['addtime'] = $this->mapMonthRange;
        $map_a['status'] = ['in', '8,9'];
        $trades_a = $Trade->where($map_a)->count();
        $map_b['addtime'] = $this->mapMonthRange;
        $map_b['status'] = ['in', '2,3,4'];
        $trades_b = $Trade->where($map_b)->count();
        $amount_rate = round($trades_a / $trades_b * 100, 3);
        $this->assign(['amount_rate_total' => $trades_a, 'amount_rate' => $amount_rate, 'amount_total' => $trades_b]);
        $this->display();
    }

    /**
     * 年趋势图
     * @author iredbaby
     */
    public function orderYearTrend(){
	$Trade = D('Trade');
	$year = time2year($this->year_start,$this->year_end);
	for($i = $year['year']['start']; $i <= $year['year']['end'];$i++){	    
	    $year_start = strtotime($i . "-01-01 00:00:00");
	    $year_start_end = strtotime($i . "-12-31 23:59:59");

	    $map['addtime'] = [['gt',$year_start],['lt',$year_start_end]];
	    $map['status'] = ['in','2,3,4'];
	    $yeartrade_names[] = $i;
	    $yeartrade_total[] = $Trade->where($map)->field("addtime")->count();	    
	}

	$yeartrade_name = implode("','",$yeartrade_names);
	$yeartrade_total = implode(",",str_replace(0,'',$yeartrade_total));		
	$this->assign(['yeartrade_name'=>$yeartrade_name,'yeartrade_total'=>$yeartrade_total]);
	$this->display();	
    }

    /**
     * 订单列表
     */
    public function orderList()
    {
        $day_search = I("get.search");
        $day_search = Tools::str2arr($day_search['value']);
        $this->day_start = strtotime($day_search[0] . ' 00:00:00');
        $this->day_end = strtotime($day_search[1] . ' 23:59:59');
        // 字段
        $column = [
            ['select'=>'trade.itemid','as'=>'trade_id','show_name'=>'itemid'],
            ['select'=>'trade.p_id','as'=>'p_id','show_name'=>'产品id'],
            ['select'=>'trade.order','as'=>'order_id','show_name'=>'订单编号'],
            ['select'=>'trade.buyer','as'=>'buyer','show_name'=>'买家'],
            ['select'=>'trade.buyer_name','as'=>'buyer_name','show_name'=>'买家姓名'],
            ['select'=>'trade.buyer_phone','as'=>'buyer_phone','show_name'=>'买家电话'],
            ['select'=>'product.company','as'=>'company','show_name'=>'公司'],
            ['select'=>'product.cj','as'=>'cj','show_name'=>'厂家'],
            ['select'=>'trade.title','as'=>'title','show_name'=>'产品名'], // 产品
            ['select'=>'trade.note','as'=>'standard','show_name'=>'规格'],
            ['select'=>'trade.total','as'=>'total','show_name'=>'购买数'],
            ['select'=>'trade.amount','as'=>'amount','show_name'=>'总额'],
            ['select'=>'trade.pay','as'=>'pay','show_name'=>'支付方式'],
            ['select'=>'trade.status','as'=>'status','show_name'=>'状态编号'],
        ];
        if($draw = I("get.draw")){
            // 预定义
            $start = $_GET['start'];
            $limit = $_GET['length'];
            $order = $_GET['order'];
            $search[] = " trade.addtime > {$this->day_start} AND trade.addtime < {$this->day_end} ";

            // 重组条件
            $order = "{$column[$order[0]['column']]['as']} {$order[0]['dir']}";
            foreach ($_GET['columns'] as $k => $v) {
                if ($v['search']['value'] != '') {
                    $search[] = "{$column[$v['data']]['select']} LIKE '%{$v[search][value]}%'";
                }
            }
            $search = Tools::arr2str($search, " AND ");
            foreach($column as $k => $v){
                $field[] = "{$v['select']} AS {$v['as']}";
            }
            $field = Tools::arr2str($field);

            // 查询总数
            $_sql = "SELECT {$field} FROM __MALL_finance_trade AS trade
                LEFT JOIN __MALL_sell_5 AS product ON trade.p_id = product.itemid
                WHERE {$search}
                ORDER BY {$order}";
            $sql = "SELECT COUNT(x.trade_id) as total FROM ({$_sql}) AS x ";
            $x = $this->MallDb->list_query($sql);
            $total = $x[0]['total'];

            // 查询数据并重组
            $sql = "{$_sql}
                LIMIT {$start}, {$limit}";
//            Tools::_vp($this->MallDb->getSql($sql),0,2);
            $data = $this->MallDb->list_query($sql);
            foreach ($data as $k => $v) {
                foreach ($column as $key => $value) {
                    $x[$k][] = $v[$value['as']];
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
            exit();
        }else{
            $sql = "SELECT trade.* FROM __MALL_finance_trade AS trade
            LEFT JOIN __MALL_sell_5 AS product ON trade.p_id = product.itemid
            LIMIT 0,10";
            $orderList = $this->MallDb->list_query($sql);
            $this->assign(['column'=>$column,'orderList'=>$orderList]);
            $this->display();
        }
    }

    /**
     * 合并订单列表
     */
    public function tradeOrderList()
    {
        $day_search = I("get.search");
        $day_search = Tools::str2arr($day_search['value']);
        $this->day_start = strtotime($day_search[0] . ' 00:00:00');
        $this->day_end = strtotime($day_search[1] . ' 23:59:59');
        // 字段
        $column = [
            ['select'=>'trade.itemid','as'=>'trade_id','show_name'=>'itemid'],
            ['select'=>'trade.product_id','as'=>'product_id','show_name'=>'产品编号'],
            ['select'=>'trade.ordercode','as'=>'ordercode','show_name'=>'订单编号'],
            ['select'=>'trade.buyer_name','as'=>'buyer_name','show_name'=>'买家姓名'],
            ['select'=>'trade.total','as'=>'total','show_name'=>'数量'],
            ['select'=>'trade.amount','as'=>'amount','show_name'=>'总额'],
            ['select'=>'trade.buyer','as'=>'buyer','show_name'=>'买家'],
        ];
        if($draw = I("get.draw")){
            // 预定义
            $start = $_GET['start'];
            $limit = $_GET['length'];
            $order = $_GET['order'];
            $search[] = " trade.addtime > {$this->day_start} AND trade.addtime < {$this->day_end} ";

            // 重组条件
            $order = "{$column[$order[0]['column']]['as']} {$order[0]['dir']}";
            foreach ($_GET['columns'] as $k => $v) {
                if ($v['search']['value'] != '') {
                    $search[] = "{$column[$v['data']]['select']} LIKE '%{$v[search][value]}%'";
                }
            }
            $search = Tools::arr2str($search, " AND ");
            foreach($column as $k => $v){
                $field[] = "{$v['select']} AS {$v['as']}";
            }
            $field = Tools::arr2str($field);

            // 查询总数
            $_sql = "SELECT {$field} FROM __MALL_finance_trade_orders AS trade
                WHERE {$search}
                ORDER BY {$order}";
            $sql = "SELECT COUNT(*) as total FROM ({$_sql}) AS x ";
            $x = $this->MallDb->list_query($sql);
            $total = $x[0]['total'];

            // 查询数据并重组
            $sql = "{$_sql}
                LIMIT {$start}, {$limit}";
//            Tools::_vp($this->MallDb->getSql($sql),0,2);
            $data = $this->MallDb->list_query($sql);
            foreach ($data as $k => $v) {
                foreach ($column as $key => $value) {
                    $x[$k][] = $v[$value['as']];
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
            exit();
        }else{
            $this->assign(['column'=>$column]);
            $this->display();
        }
    }
}
