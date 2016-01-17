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
        $amount_total = 0;
        foreach($trades as $k => $v){
            $amount_total += $v['amount'];
        }
        return $amount_total;
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
}