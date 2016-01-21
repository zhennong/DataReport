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

    private function getMouthSoltPayment($date_start,$date_end)
    {
        $Trade = D('Trade');
        $mouth_solt = get_mouth_solt($date_start,$date_end);
        $map['status'] = ['in','2,3,4'];
        foreach($mouth_solt as $k => $v){
            $map['paytime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_trades[$k]['mouth_solt'] = $v;
            $mouth_solt_trades[$k]['trades'] = $Trade->field('itemid,amount')->where($map)->select();
            $mouth_solt_trades[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
            $mouth_solt_trades[$k]['trade_amount'] = get_arr_k_amount($mouth_solt_trades[$k]['trades'],'amount');
            unset($mouth_solt_trades[$k]['trades']);
        }
        return $mouth_solt_trades;
    }

    /**
     * 月付款
     * @author wodrow
     */
    public function mouthSoltPayment()
    {
        $this->assign('mouth_solt_trades',$this->getMouthSoltPayment($this->date_start,$this->date_end));
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
        $this->assign(['start_year'=>date("Y",$this->year_start),'end_year'=>date("Y",$this->year_end),'year_solt_trades'=>$year_solt_trades]);
        $this->display();
    }

    /**
     * 月付款同期对比
     * @author wodrow
     */
    public function sameMouthAnnually()
    {
        $Trade = D('Trade');
        $map['addtime'] = $this->mapYearRange;
        $map['status'] = ['in','2,3,4'];
        $trades = $Trade->where($map)->field("addtime,amount")->select();
        for($i=1;$i<=12;$i++){
            $xAxis[] = $i;
        }
        $year_solt = get_year_solt($this->year_start,$this->year_end);
        foreach($year_solt as $k => $v){
            $legend[] = $v['start']['year'];
            $legend_data[] = $v['start']['year'];
        }
        $legend_data = "'".implode("','",$legend_data)."'";
        foreach($xAxis as $k1 => $v1){
            $same_mouth_trades["mouth_name"] = $v1;
            foreach($year_solt as $k2 => $v2){
                foreach($trades as $k => $v){
                    if(date("m",$v['addtime'])==$v1){
                        if($v['addtime']>$v2['start']['ts']&&$v['addtime']<$v2['end']['ts']){
                            $same_mouth_trades[$v1][$v2['start']['year']]['trades'][] = $v;
                        }
                    }
                }
            }
        }
        foreach($same_mouth_trades as $k => $v){
            foreach($v as $k1 => $v1){
                $same_mouth_trades[$k][$k1]['amount'] = get_arr_k_amount($v1['trades'],'amount');
                unset($same_mouth_trades[$k][$k1]['trades']);
            }
        }
        /**
         * $legend=['2011', '2012',...,'前年']
         * $xAxis=['1', '2',...,'12'],
         * $same_mouth_trades = [
         *      [
         *          'mouth_name'=>str,
         *          'mouth'=>[
         *              'year'=>int,'amount'=>int
         *      ]]
         * ]
         */
        $this->assign(['xAxis'=>$xAxis,'legend'=>$legend,'legend_data'=>$legend_data,'same_mouth_trades'=>$same_mouth_trades]);
        $this->display();
    }
}