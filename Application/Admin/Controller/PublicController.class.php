<?php
/*
 * @Modul 用户登陆
 * @Created on 2015/09/27
 * @Author  小艾 1596229276@qq.com
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Controller\LogController;

class PublicController extends Controller {
       
    //登录验证
    public function login(){
    	if(!empty($_POST)){
			
			$code = I('code');	//验证码
			$verify = new \Think\Verify();
			if($verify->check($code)){						
				$map['account'] = I('account');   //用户名
				$map['password'] = md5(I('password'));	//密码
				$m = M('admin');
				$result = $m->field('id,account,login_count,status')->where($map)->find();
				if($result){
					if($result['status'] == 0){
						$this->error('登录失败，账号被禁用',U('Public/login'));
					}
					session('aid',$result['id']);	//管理员ID
					session('account',$result['account']);	//管理员帐号  				
					//保存登录信息
					$data['id'] = $result['id'];	//用户ID
					$data['login_ip'] = get_client_ip();	//最后登录IP
					$data['login_time'] = time();		//最后登录时间
					$data['login_count'] = $result['login_count'] + 1;

				}else{
					
					$this->error('账户或密码错误',U('/Admin'));	
				}
			}else{
				//$this->ajaxReturn(0);	//失败
				$this->error('验证码错误',U('/Admin'));
			}
    	}else{
    		if(session('aid')){
    			$this->error('已登录，正在跳转到主页',U('Index/index'));
    		}
    		$this->display();
    	}
    }
    
    //验证码
    public function verify(){   	
    	ob_clean();		//清除缓存
    	$Verify = new \Think\Verify();
    	$Verify->fontSize = 30;	//验证码字体大小
    	$Verify->length = 4;	//验证码位数
    	$Verify->entry();
    }

}




