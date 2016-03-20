<?php
namespace Common\Controller;

use Common\MallDb;
use Common\Tools;
use Think\Controller;

/**
 * Description of CommonController
 *
 * @author wodrow
 */
abstract class CommonController extends Controller
{
    protected $MallDb;
    protected $malldb; //商城数据库链接
    protected $month_start; // 获取公用开始时间
    protected $month_end;   // 获取公用结束时间
    protected $year_start; // 获取公用开始时间
    protected $year_end;   // 获取公用结束时间
    protected $mapMonthRange; // 获取公用查询段
    protected $mapYearRange; // 获取公用查询段
    protected $now; //当前时间戳
    protected $now_Y; //当前年
    protected $now_m;
    protected $now_d;
    protected $now_H;
    protected $now_i;
    protected $now_s;
    protected $now_Y_start; //当前年开始时间戳
    protected $now_m_start;
    protected $now_d_start;
    protected $month_solt; // 所选时间内各个月时间段
    protected $year_solt; // 所选时间内各个年时间段


    public function _initialize()
    {
        $this->getMallDb();
        $this->getNow();
        $this->getRange();
    }

    /**
     * 获取商城数据库连接
     */
    private function getMallDb(){
        if(!$this->MallDb){
            $MallDb = new MallDb();
            $this->MallDb = $MallDb;
            $this->malldb = $MallDb->db;
        }
    }

    /**
     * 获取当前时间
     */
    private function getNow(){
        $this->now = time();
        $this->now_Y = date('Y',$this->now);
        $this->now_m = date('m',$this->now);
        $this->now_d = date('d',$this->now);
        $this->now_H = date('H',$this->now);
        $this->now_i = date('i',$this->now);
        $this->now_s = date('s',$this->now);
        $this->now_Y_start = strtotime($this->now_Y . '-01-01 00:00:00');
        $this->now_m_start = strtotime($this->now_Y . '-' . $this->now_m . '-01 00:00:00');
        $this->now_d_start = strtotime($this->now_Y . '-' . $this->now_m . '-' .$this->now_d . ' 00:00:00');
    }

    protected function getAreaChildIds($area_id)
    {
        $Area = D('Area');
        $x = $Area->where(['parentid'=>$area_id])->field("areaid")->select();
        return Tools::getCols($x,'areaid');
    }

    /**
     * 从市获取所有县区
     * @param $city_id
     * @return []
     */
    protected function getCountyIdsFromCityId($city_id)
    {
        return $this->getAreaChildIds($city_id);
    }

    /**
     * 从省获取所有市
     * @param $provice
     * @return []
     */
    protected function getCityIdsFromProviceId($provice_id)
    {
        return $this->getCountyIdsFromCityId($provice_id);
    }

    /**
     * 从全国获取所有省
     * @param $provice
     * @return []
     */
    protected function getProviceIdsFromCountry()
    {
        $provice = $this->getCountyIdsFromCityId(0);
        return $provice;
    }

    /**
     * 从省获取所有县区
     * @param $provice
     * @return []
     */
    protected function getCountyIdsFromProviceId($provice_id)
    {
        $citys = $this->getCityIdsFromProviceId($provice_id);
        $county_ids = [];
        foreach($citys as $k => $v){
            $x = $this->getCountyIdsFromCityId($v);
            $county_ids = array_merge($county_ids,$x);
        }
        return $county_ids;
    }

    /**
     * 从全国获取所有地区子id
     * @param $area_id
     * @return []
     */
    protected function getCountyIdsFromCountry()
    {
        #
    }

    /**
     * 获取所有地区
     * @return [[]]
     */
    protected function getAllAreaList()
    {
        $areas = D('Area')->cache(true)->field(['areaid'=>'id','areaname','parentid'=>'pid'])->select();
        return $areas;
    }

    /**
     * 获取地区树
     * @return tree
     */
    protected function getAreaTree($root=0)
    {
        return Tools::list2tree($this->getAllAreaList(),'id','pid','_child',$root);
    }

    /**
     *  公用查询时间
     */
    private function getRange(){
        if (I('month_start') && I('month_end')) {
            $this->month_start = strtotime(I('month_start') . "-01 00:00:00");
            $this->month_end = strtotime(I('month_end') . "-01 23:59:59");
        } else {
            $this->month_start = strtotime($this->now_Y . "-01-01 00:00:00");
            $this->month_end = time();
        }
        $this->month_solt = get_month_solt($this->month_start,$this->month_end);
        $this->assign(['month_start' => $this->month_start, 'month_end' => $this->month_end]);
        $this->mapMonthRange = [['gt',$this->month_start],['lt',$this->month_end]];

        if (I('year_start') && I('year_end')) {
            $this->year_start = strtotime(I('year_start') . "-01-01 00:00:00");
            $this->year_end = strtotime(I('year_end') . "-12-31 23:59:59");
        } else {
            $this->year_start = strtotime($this->now_Y-1 . "-01-01 00:00:00");
            $this->year_end = time();
        }
        $this->year_solt = get_year_solt($this->year_start,$this->year_end);
        $this->assign(['year_start' => $this->year_start, 'year_end' => $this->year_end]);
        $this->mapYearRange = [['gt',$this->year_start],['lt',$this->year_end]];
    }

    public function _empty()
    {
        $this->display('Public:error');
    }
}
