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
     */
    public function paymentTotal()
    {
        $map['paytime'] = $this->mapDateRange;
        $map['status'] = ['in','2,3,4'];
        $amount_total = $this->getSuccessPaymentByDate($map);
        $today_start = strtotime(date("Y-m-d",time())." 00:00:00");
        $today_amount_total = $this->getSuccessPaymentByDate(['paytime'=>[['lt',$today_start]]]);
        $this->assign(['amount_total'=>$amount_total,'today_amount_total'=>$today_amount_total]);
        $this->display();
    }
}