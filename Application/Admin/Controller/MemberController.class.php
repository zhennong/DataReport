<?php
/*
 * @Controller 会员模块
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 */
namespace Admin\Controller;
use Common\Controller\AuthController;
use Think\Auth;

class MemberController extends AuthController{
    
    //勿删权限判断
    public function memberIndex(){        
    	$this->display();
    }
    
    public function memberReg(){
		
	$Member = D('Member');
        $mouth_solt = get_mouth_solt($this->date_start,$this->date_end);
	
	
	
        foreach($mouth_solt as $k => $v){
            $map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_trades[$k]['mouth_solt'] = $v;
	    
            //$mouth_solt_trades[$k]['trades'] = $Member->field('regtime')->where($map)->select();
	    
//            $mouth_solt_trades[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
//            $mouth_solt_trades[$k]['trade_amount'] = get_arr_k_amount($mouth_solt_trades[$k]['trades'],'regtime');
//            unset($mouth_solt_trades[$k]['trades']);
	    
	   dump($v);
	    
        } 
        $this->assign('mouth_solt_trades',$mouth_solt_trades);
        $this->display();
	
    }
    
}