<?php
/*
 * @thinkphp3.2.3  auth认证   php5.4以上
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Common\Controller;
use Think\Controller;
use Think\Auth;
use Think\Model;

class AuthController extends CommonController {
    protected function _initialize(){
	//session不存在时，不允许直接访问
	if (!session('aid')) {
		$this->error('还没有登录，正在跳转到登录页', U('Public/login'));
	}

	//session存在时，不需要验证的权限
	$not_check = ['Index/index', 'Index/main', 'Index/clear_cache','Index/edit_pwd', 'Index/logout', 'Admin/admin_list','Admin/admin_edit', 'Admin/admin_add'];

	//当前操作的请求                 模块名/方法名
	if (in_array(CONTROLLER_NAME . '/' . ACTION_NAME, $not_check)) {
		return true;
	}

	//下面代码动态判断权限
	$auth = new Auth();
	if (!$auth->check(CONTROLLER_NAME . '/' . ACTION_NAME, session('aid')) && session('aid') != 1) {
		$this->error('没有权限');
	}
    }
}