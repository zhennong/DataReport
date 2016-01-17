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
    public function _initialize()
    {
	parent::_initialize();
	
        $this->start_t = ['y' => '2013', 'm' => '01', 'd' => '01'];
        $this->getTimeStramp($this->start_t);
        $this->end_t = ['y' => date('Y'), 'm' => date('m'), 'd' => date('d')];
        $this->getTimeStramp($this->end_t);
    }
    
    //默认配置 对栏目权限判断
    public function trade_index()
    {
        $this->display('Trade/trade_index');
    }

    public $start_t, $end_t; // ['y'=>'','m'=>'','d'=>'','ts'=>''];

    public function index()
    {
//        $this->redirect('orderTeand');
    }

    

    /**
     * 获取时间戳
     * @param $t 日期['y'=>'','m'=>'','d'=>'']
     */
    private function getTimeStramp(&$t)
    {
        $t['ts'] = strtotime("{$t['y']}-{$t['m']}-{$t['d']} 00:00:00");
    }

    /**
     * 获取月时间段
     * @param $start_t
     * @param $end_t
     * @return array 时间段
     */
    private function getTimeSolt($start_t, $end_t)
    {
        $mouth_solt = [];
        $x = $start_t['ts'];
        $i = 1;
        while ($x < $end_t['ts']) {
            $mouth_solt[$i]['start']['ts'] = $x;
            $mouth_solt[$i]['start']['date'] = date("Y-m", $x);
            $x = strtotime("+{$i} Month", $start_t['ts']);
            $mouth_solt[$i]['end']['ts'] = $x;
            $mouth_solt[$i]['end']['date'] = date("Y-m", $x);
            $i++;
        }
        return $mouth_solt;
    }

    /**
     * 获取月订单总额
     */
    public function getTradeAmountByMouth($mouth_trades)
    {
        $x = 0;
        foreach ($mouth_trades as $k => $v) {
            $x += $v['amount'];
        }
        return $x;
    }

    /**
     * 获取订单信息 by 月份
     * @param $mouth_sort 月时间段
     * return [
     *      ['k'=>[
     *          'trades'=>[[]],
     *          'trade_total' => int,
     *          'mouth_solt' => ['start'=>[],'end'=>[]],
     *          ...
     *      ]]
     * ]
     */
    public function getTradeByMouthSolt($mouth_sort)
    {
        $model = D('Trade');
        foreach ($mouth_sort as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $map['status'] = ['in', '2,3,4'];
            $mouth_solt_trades[$k]['trades'] = $model->field('itemid,amount')->where($map)->select();
            $mouth_solt_trades[$k]['trade_total'] = count($mouth_solt_trades[$k]['trades']);
            $mouth_solt_trades[$k]['trade_amount'] = $this->getTradeAmountByMouth($mouth_solt_trades[$k]['trades']);
            $mouth_solt_trades[$k]['mouth_solt'] = $v;
            $mouth_solt_trades[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            unset($mouth_solt_trades[$k]['trades']);
        }
        return $mouth_solt_trades;
    }

    /**
     * 订单走势
     */
    public function orderTrend()
    {
        if (IS_POST) {
//            $this->start_t
        }
        $mouth_solt = ($this->getTimeSolt($this->start_t, $this->end_t));
        $mouth_solt_trades = $this->getTradeByMouthSolt($mouth_solt);
        $this->assign(['mouth_solt_trades' => $mouth_solt_trades]);
        $this->display();
    }


    /**
     * 下单时段（24小时制）
     */
    public function orderTime(){
        $date_start = $this->date_start;
        $date_end = $this->date_end;
		
        $map['addtime'] = [
            ['gt',$date_start],['lt',$date_end]
        ];
        $map['status'] = ['in','2,3,4'];
        $trades = D('Trade')->where($map)->field("addtime")->select();
        for($i = 0;$i<24;$i++){
            $x = $i;
            if($x<10){
                $x = "0{$x}";
            }
            $time_solt_trades[$i]['time_name'] = $x."点";
            $time_solt_trades[$i]['trade_total'] = 0;
            foreach($trades as $k => $v){
                if($x == date('H',$v['addtime'])){
                    $time_solt_trades[$i]['trade_total']++;
                }
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
	$date_start = $this->date_start;
        $date_end = $this->date_end;
	
        $map['paytime'] = [
            ['gt',$date_start],['lt',$date_end]
        ];
        $map['status'] = ['in','2,3,4'];
        $trades = D('Trade')->where($map)->field("paytime")->select();
        for($i = 0;$i<24;$i++){
            $x = $i;
            if($x<10){
                $x = "0{$x}";
            }
            $pay_solt_trades[$i]['pay_name'] = $x."点";
            $pay_solt_trades[$i]['trade_total'] = 0;
            foreach($trades as $k => $v){
                if($x == date('H',$v['paytime'])){
                    $pay_solt_trades[$i]['trade_total']++;
                }
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
	$date_start = $this->date_start;
        $date_end = $this->date_end;
	
        $map['addtime'] = [
            ['gt',$date_start],['lt',$date_end]
        ];
        $map['status'] = ['in','2,3,4'];
        $Logistics = D('Logistics')->where($map)->field("addtime")->select();
	
        for($i = 0;$i<24;$i++){
            $x = $i;
            if($x<10){
                $x = "0{$x}";
            }
            $time_solt_trades[$i]['time_name'] = $x."点";
            $time_solt_trades[$i]['trade_total'] = 0;
	    
	    foreach($Logistics as $k => $v){
                if($x == date('H',$v['addtime'])){
		     $time_solt_trades[$i]['trade_total'] ++;
                }
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
	$date_start = $this->date_start;
        $date_end = $this->date_end;	
        $map['paytime'] = [
            ['gt',$date_start],['lt',$date_end]
        ];
        $map['status'] = ['in','2,3,4'];
        $trades = D('Trade')->where($map)->field('pay,count(pay) AS c')->group('pay')->select();	
	foreach ($trades as $k => $v) {
	    $time_solt_trades[$k]['name'] = $v['pay'];
	    $title[] = "'".$v['pay']."'";
	    $time_solt_trades[$k]['value'] = $v['c'];
	}	
	$data = json_encode($time_solt_trades);		
	$title = arr2str($title);	
	$this->assign("title",$title);
	$this->assign("data",$data);		 
        $this->display();	
    }
    
    /**
     * 客户退单
     * @author iredbaby
     * 算法：【选择日期】成功退款笔数/【选择日期】支付宝交易笔数*100%；
     * @param $trades_a  退款状态统计
     * @param $trades_b  交易成功状态统计
    */
    public function orderRate() {
	$map_a['addtime'] = $this->mapDateRange;
        $map_a['status'] = ['in','8,9'];	
	$trades_a = D('Trade')->where($map_a)->count();	
	$map_b['addtime'] = $this->mapDateRange;
        $map_b['status'] = ['in','2,3,4'];	
	$trades_b = D('Trade')->where($map_b)->count();	
	$amount_rate = round($trades_a / $trades_b * 100, 3);		
        $this->assign(['amount_rate_total'=>$trades_a,'amount_rate'=>$amount_rate,'amount_total'=>$trades_b]);
        $this->display();
    }
    
    /**
     * 年趋势图
     * @author iredbaby
     */
    public function orderYearTrend(){
	$year_start = $this->year_start;
        $year_end = $this->year_end;		
        $map['addtime'] = [
            ['gt',$year_start],['elt',$year_end]
        ];
        $map['status'] = ['in','2,3,4'];
        $Trade = D('Trade')->where($map)->field("addtime")->select();		
	$year_s = (int)date("Y",$year_start); 
	$year_e = (int)date("Y",$year_end); 
	
	for ($i = $year_s;$i <= $year_e; $i++){
	    //$yeartrade_name['yeartrade_name'] .= "'".$i."',";	    
	    
	    $yeartrade_name['yeartrade_name'] .= $i.",";	    
	    
	    foreach($Trade as $k => $v){		
                if($i == date('Y',$v['addtime'])){
		    $yeartrade_total[$i]['yeartrade_total'] ++;
		}
            }	
	    
	}
	foreach ($yeartrade_total as $k => $v) {
	    $total[] = $v['yeartrade_total'];
	}		
	$yeartrade_total = arr2str($total);	
	$yeartrade_name = arr2str($yeartrade_name);	
	$this->assign(['yeartrade_name'=>$yeartrade_name,'yeartrade_total'=>$yeartrade_total]);
	$this->display();	
    }
}
