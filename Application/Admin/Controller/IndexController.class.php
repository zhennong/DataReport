<?php
/*
 * @Controller  后台默认模块
 * @Created on 2016/01/15
 * @Author  iredbaby   1596229276@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Admin\Controller;
use Common\Controller\AuthController;
use Think\Auth;

class IndexController extends AuthController{
    public function index(){
        $m = M('auth_rule');
	$field = 'id,name,title';
	$data = $m->field($field)->where('pid=0 AND status=1')->select();
	$auth = new Auth();
	//没有权限的菜单不显示
	foreach ($data as $k => $v) {
	    if (!$auth->check($v['name'], session('aid')) && session('aid') != 1) {
		unset($data[$k]);
	    } else {
		// status = 1    为菜单显示状态
		$data[$k]['sub'] = $m->field($field)->where('pid=' . $v['id'] . ' AND status=1')->order('sort asc,id')->select();
		foreach ($v['sub'] as $k2 => $v2) {
		    if (!$auth->check($v2['name'], session('aid')) && session('aid') != 1) {
			    unset($v['sub'][$k2]);
		    }
		}
	    }
	}
	
	$this->assign('data',$data);	// 顶级
    	$this->display();
    }
    
    //后台首页
    public function main(){
    	//服务器IP
    	$data['server_ip'] = GetHostByName($_SERVER['SERVER_NAME']);	
	
    	//最大上传限制
    	$data['max_upload'] = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
		
	//整理实时数据
	$realtime = array(
	  'time' => date('Y年n月j日 H:i:s'),
	  'uptime' => $sys_info['uptime'],
	  'disk_free' => round(@disk_free_space('.') / (1024*1024*1024), 2).' G',
	  'mem_used' => round($sys_info['mem_used']/1024, 2).' G',
	  'mem_free' => round($sys_info['mem_free']/1024, 2).' G',
	  'mem_cached' => round($sys_info['mem_cached']/1024, 2).' G',
	  'mem_buffers' => round($sys_info['mem_buffers']/1024, 2).' G',
	  'mem_real_used' => round($sys_info['mem_real_used']/1024, 2).' G', //真实内存使用
	  'mem_real_free' => round($sys_info['mem_real_free']/1024, 2).' G', //真实内存空闲
	  'mem_real_percent' => (int)$sys_info['mem_real_percent'].'%', //真实内存使用比率
	  'mem_percent' => (int)$sys_info['mem_percent'].'%', //内存总使用率
	  'mem_cached_percent' => (int)$sys_info['mem_cached_percent'].'%', //cache内存使用率
	  'swap_percent' => (int)$sys_info['swap_percent'].'%',
	  'load_avg' => $sys_info['load_avg'] //系统平均负载
	);

	$sys_info['disk_total'] = round(@disk_total_space('.') / (1024*1024*1024), 2);

	$this->assign('realtime',$realtime);
	$this->assign('sys_info',$sys_info);
    	$this->assign('data',$data);
    	$this->display();
    }
    
    //修改密码
    public function edit_pwd(){
    	if(!empty($_POST)){
    		$m = M('admin');
    		$where['id'] = session('aid');
    		$where['password'] = md5(I('old_pwd'));
    		$new_pwd = md5(I('new_pwd'));
    		$data = $m->field('id')->where($where)->find();
    		if(empty($data)){
    			$this->ajaxReturn(0);	//失败，原密码错误
    		}else{
    			$result = $m->where('id='.$where['id'])->data('password='.$new_pwd)->save();
    			if($result){
    				session('aid',null);
    				session('account',null);
    				$this->ajaxReturn(1);	//修改成功
    			}else{
    				$this->ajaxReturn(2);	//更新失败
    			}
    		}
    	}else{
    		$this->display();
    	}   	
    }
    
    //循环删除目录和文件函数
    function delDirAndFile($dirName){
	if ( $handle = opendir( "$dirName" ) ) {
	    while ( false !== ( $item = readdir( $handle ) ) ) {
		if ( $item != "." && $item != ".." ) {
		    if ( is_dir( "$dirName/$item" ) ) {
			    delDirAndFile( "$dirName/$item" );
		    } else {
			    unlink( "$dirName/$item" );
		    }
		}
	    }
	    closedir( $handle );
	    if( rmdir( $dirName ) ) return true;
	}
    }
    
    //清除缓存
    public function clear_cache(){
    	$str = I('clear');	//防止搜索到第一个位置为0的情况
    	if($str){
	    //strpos 参数必须加引号
	    //删除Runtime/Cache/admin目录下面的编译文件
	    if(strpos("'".$str."'", '1')){   			
		$dir = RUNTIME_PATH.'/Cache/Admin/';
		$this->delDirAndFile($dir);
	    }
	    //删除Runtime/Cache/Home目录下面的编译文件
	    if(strpos("'".$str."'", '2')){    			
		$dir = RUNTIME_PATH.'/Cache/Home/';
		$this->delDirAndFile($dir);
	    }
	    //删除Runtime/Data/目录下面的编译文件
	    if(strpos("'".$str."'", '3')){
		$dir = RUNTIME_PATH.'/Data/';
		$this->delDirAndFile($dir);
	    }
	    //删除Runtime/Temp/目录下面的编译文件
	    if(strpos("'".$str."'", '4')){	
		$dir = RUNTIME_PATH.'/Temp/';
		$this->delDirAndFile($dir);
	    }
	    $this->ajaxReturn(1);	//成功
    	}else{
	    $this->display();
    	}
    }

}