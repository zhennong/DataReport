<?php
/*
 * @Controller 会员模块
 * @Created on 2016/01/18
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
    
    //获取新注册会员
    private function get_new_member($date_id){
	$Member = D('Member');	
	$map_new['regtime'] = [['gt',$date_id],['lt',time()]];	
	$reg_member_data = $Member->where($map_new)->field('truename,regtime')->select();
	foreach ($reg_member_data as $key => $value) {
	    $reg_member_count[] = $value['truename']; 
	}
	$reg_member_str = implode(',', $reg_member_count);
	return $reg_member_str;
    }
    
    /**
     * 会员付款统计
     * @param format_date($i)  1 年  2 月 3 日
     */
    public function memberPay(){		
	$TradeOrder = D('TradeOrder');
	//按年月日付款
	for($i=1 ;$i <= 3;$i++){
	    $map['paytime'] = [['neq',0],['gt',format_date($i)],['lt',time()]]; 	
	    $member_type[] = $i;
	    $member_count[] = $TradeOrder->where($map)->field('buyer_name,paytime')->count('distinct buyer_name');	
	}
	
	//新会员 月日付款
	for($i = 2;$i <= 3;$i++){
	    $day_member_name = $this->get_new_member(format_date($i));	
	    $map_day['paytime'] = [['neq',0],['gt',format_date($i)],['lt',time()]]; 			
	    $map_day['buyer_name'] = [['in',$day_member_name]];	    
	    $new_member_type[] = $i;
	    $new_member_str = $TradeOrder->where($map_day)->field('buyer_name,paytime')->count('distinct buyer_name');
	    if(empty($new_member_str)){	$new_member_count[] = 0;}else{$new_member_count[] = $new_member_str;}	    
	}	

	//全部付款
	$map_all['paytime'] = [['neq',0]]; 	
	$all = $TradeOrder->where($map_all)->field('buyer_name,paytime')->count('distinct buyer_name');	
	
	$this->assign(['day'=>$member_count[2],'month'=>$member_count[1],'year'=>$member_count[0],'all'=>$all]);
	$this->assign(['day_new'=>$new_member_count[1],'month_new'=>$new_member_count[0]]);
	$this->display();
    }
    
    
    
}