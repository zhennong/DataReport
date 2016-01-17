<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/16/16
 * Time: 9:32 AM
 */

namespace Admin\Controller;


class FinanceController extends AdminController
{
    public function financeIndex()
    {
        $this->display('financeIndex');
    }

    /**
     * 获取付款总额
     * @param $map
     * @return int
     */
    private function getSuccessPaymentByDate($map)
    {
        $trades = D("Trade")->where($map)->field("amount")->select();
        return get_arr_k_amount($trades,'amount');
    }

    /**
     * 付款总额
     * @author wodrow
     */
    public function paymentTotal()
    {
        $map['paytime'] = $this->mapDateRange;
        $map['status'] = ['in','2,3,4'];
        $amount_total = $this->getSuccessPaymentByDate($map);
        $today_start = strtotime(date("Y-m-d",time())." 00:00:00");
        $today_amount_total = $this->getSuccessPaymentByDate("paytime > {$today_start}");
        $this->assign(['amount_total'=>$amount_total,'today_amount_total'=>$today_amount_total]);
        $this->display();
    }

    /**
     * 月付款
     * @author wodrow
     */
    public function mouthSoltPayment()
    {
        $Trade = D('Trade');
        $mouth_solt = get_mouth_solt($this->date_start,$this->date_end);
        $map['status'] = ['in','2,3,4'];
        foreach($mouth_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_trades[$k]['mouth_solt'] = $v;
            $mouth_solt_trades[$k]['trades'] = $Trade->field('itemid,amount')->where($map)->select();
            $mouth_solt_trades[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_trades[$k]['trade_amount'] = get_arr_k_amount($mouth_solt_trades[$k]['trades'],'amount');
            unset($mouth_solt_trades[$k]['trades']);
        }
        $this->assign('mouth_solt_trades',$mouth_solt_trades);
        $this->display();
    }

    /**
     * 年付款
     * @author wodrow
     */
    public function annualSoltPayment()
    {
        $Trade = D('Trade');
        $year_solt = get_year_solt($this->year_start,$this->year_end);
        $map['status'] = ['in','2,3,4'];
        foreach($year_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $year_solt_trades[$k]['year_solt'] = $v;
            $year_solt_trades[$k]['trades'] = $Trade->field('itemid,amount')->where($map)->select();
            $year_solt_trades[$k]['year_name'] = date("Y", $v['start']['ts']);
            $year_solt_trades[$k]['trade_amount'] = get_arr_k_amount($year_solt_trades[$k]['trades'],'amount');
            unset($year_solt_trades[$k]['trades']);
        }
        $this->assign('year_solt_trades',$year_solt_trades);
        $this->display();
    }

    /**
     * 付款年增长率
     * @author wodrow
     */
    public function annualGrowthRateOfPayment()
    {
        $start_year = "2012";
        $end_year = date("Y",time()) - 1;
        $Trade = D('Trade');
        $year_solt = get_year_solt(strtotime($start_year . '-01-01 00:00:00'),strtotime($end_year . '-12-31 23:59:59'));
        $map['status'] = ['in','2,3,4'];
        foreach($year_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $year_solt_trades[$k]['year_solt'] = $v;
            $year_solt_trades[$k]['trades'] = $Trade->field('itemid,amount')->where($map)->select();
            $year_solt_trades[$k]['year_name'] = date("Y", $v['start']['ts']);
            $year_solt_trades[$k]['trade_amount'] = get_arr_k_amount($year_solt_trades[$k]['trades'],'amount');
            unset($year_solt_trades[$k]['trades']);
        }
        $this->assign(['start_year'=>$start_year,'end_year'=>$end_year,'year_solt_trades'=>$year_solt_trades]);
        $this->display();
    }

    /**
     * 月付款同期对比
     * @author wodrow
     */
    public function sameMouthAnnually()
    {
        $this->display();
    }
}