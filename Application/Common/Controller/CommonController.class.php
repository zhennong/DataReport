<?php
namespace Common\Controller;

use Think\Controller;

/**
 * Description of CommonController
 *
 * @author wodrow
 */
abstract class CommonController extends Controller{
    
    protected $date_start; // 获取公用开始时间
    protected $date_end;   // 获取公用结束时间
    /**
     *  公用查询时间
     */
    public function _initialize(){
	if(I('date_start')&&I('date_end')){
	    $this->date_start = strtotime(I('date_start'));
	    $this->date_end = strtotime(I('date_end'));

	    $this->assign(['date_start' => $this->date_start,'date_end' => $this->date_end]);	
	}else{	    
	    $curr_date = date('Y');    
	    $this->date_start = strtotime($curr_date ."-1-1 00:00:00");
	    $this->date_end = strtotime($curr_date ."-12-31 23:59:59");
	}
    }
    
    public function _empty(){
        $this->display('Public:Error');
    }   
}
