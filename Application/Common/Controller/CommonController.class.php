<?php
namespace Common\Controller;

use Think\Controller;

/**
 * Description of CommonController
 *
 * @author wodrow
 */
abstract class CommonController extends Controller
{

    protected $date_start; // 获取公用开始时间
    protected $date_end;   // 获取公用结束时间
    protected $year_start; // 获取公用开始时间
    protected $year_end;   // 获取公用结束时间
    protected $mapDateRange; // 获取公用查询日期段
    protected $mapYearRange; // 获取公用查询日期段

    /**
     *  公用查询时间
     */
    public function _initialize()
    {
        if (I('date_start') && I('date_end')) {
            $this->date_start = strtotime(I('date_start') . "00:00:00");
            $this->date_end = strtotime(I('date_end') . "23:59:59");
        } else {
            $curr_date = date('Y');
            $this->date_start = strtotime($curr_date . "-01-01 00:00:00");
            $this->date_end = time();
        }
        $this->assign(['date_start' => $this->date_start, 'date_end' => $this->date_end]);
        $this->mapDateRange = [['gt',$this->date_start],['lt',$this->date_end]];

        if (I('year_start') && I('year_end')) {
	    
	    dump(I('year_end'));
	    
            $this->year_start = strtotime(I('year_start') . "-01-01 00:00:00");
            $this->year_end = strtotime(I('year_end'));
        } else {
//            $curr_date = date('Y');
//            $this->year_start = strtotime($curr_date . "-01-01 00:00:00");
//            $this->year_end = time();
        }
        $this->assign(['year_start' => $this->year_start, 'year_end' => $this->year_end]);
        $this->mapYearRange = [['gt',$this->year_start],['lt',$this->year_end]];
    }

    public function _empty()
    {
        $this->display('Public:Error');
    }
}
