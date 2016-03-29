<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/14/16
 * Time: 10:09 AM
 */

namespace Admin\Controller;

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
}
