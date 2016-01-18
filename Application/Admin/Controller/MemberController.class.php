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
    
    //会员月注册趋势
    public function memberReg(){		
	$Member = D('Member');
        $mouth_solt = get_mouth_solt($this->date_start,$this->date_end);		
        foreach($mouth_solt as $k => $v){
            $map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
            $mouth_solt_member[$k]['mouth_sort'] = $v;	    
            $mouth_solt_member[$k]['member'] = $Member->field('regtime')->where($map)->select();	    
            $mouth_solt_member[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);	    
            $mouth_solt_member[$k]['member_amount'] = count($mouth_solt_member[$k]['member']);	    
            unset($mouth_solt_member[$k]['member']);	    
        }	
	//数组处理
	foreach($mouth_solt_member as $k => $v){
	    $mouth_name[] = $v["mouth_name"];
	    $member_amount[] = $v["member_amount"];
	}
	$mouth_name = implode("','",$mouth_name);
	$member_amount = implode(",",str_replace(0,'',$member_amount));
		
	$this->assign(['mouth_name'=>$mouth_name,'member_amount'=>$member_amount]);
        $this->display();	
    }    
    
    //会员信息
    public function memberPay(){	
	$this->display();
    }
}