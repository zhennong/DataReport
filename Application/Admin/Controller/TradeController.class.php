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
    public $start_t, $end_t; // ['y'=>'','m'=>'','d'=>'','ts'=>'']

    public function index()
    {
//        $this->redirect('orderTeand');
    }

    public function _initialize()
    {
        $this->start_t = ['y' => '2013', 'm' => '01', 'd' => '01'];
        $this->getTimeStramp($this->start_t);
        $this->end_t = ['y' => date('Y'), 'm' => date('m'), 'd' => date('d')];
        $this->getTimeStramp($this->end_t);
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
            $mouth_solt[$i]['start']['date'] = date("Y-m-d", $x);
            $x = strtotime("+{$i} Month", $start_t['ts']);
            $mouth_solt[$i]['end']['ts'] = $x;
            $mouth_solt[$i]['end']['date'] = date("Y-m-d", $x);
            $i++;
        }
        return $mouth_solt;
    }

    /**
     * 获取订单信息 by 月份
     * @param $mouth_sort 月时间段
     * return [
     *      ['k'=>[
     *          'trades'=>[[]],
     *          'trade_total' => int,
     *          'mouth_solt' => ['start'=>[],'end'=>[]],
     *      ]]
     * ]
     */
    public function getTradeByMouthSolt($mouth_sort)
    {
        $model = D('Trade');
        foreach ($mouth_sort as $k => $v) {
            $map['addtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_trades[$k]['trades'] = $model->field('itemid')->where($map)->select();
            $mouth_solt_trades[$k]['trade_total'] = count($mouth_solt_trades[$k]['trades']);
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
        _vp($mouth_solt_trades, 0, 2);
        $this->assign(['mouth_solt_trades' => $mouth_solt_trades]);
        $this->display();
    }
}