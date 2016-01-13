<?php
/*
 * @log  日志记录系统
 * @Created on 2015/09/22
 * @Author  小艾   1596229276@qq.com
 */
namespace Common\Controller;
use Think\Controller;

//日志记录系统
class LogController extends Controller{
	
	public $close = 0; // 0-记录日志, 1-关闭日志系统
	public $table = "sys_log";
	public $outtime = 30; //过期时间，按天，将自动删除过期日志
	
	public function log_add($type='login|logout|delete|add|edit|...', $title, $data='', $table_name='', $view_url='') {		

		if ($this->close) return false;
		global $db, $uid, $username, $table;
		$data = array();
		$data["type"] = $type;
		$data["title"] = $title;
		$data["pagename"] = $_SERVER["REQUEST_URI"];
		$data["view_url"] = $view_url;
		$data["data"] = is_array($data) ? serialize($data) : $data;
		$data["table_name"] = $table_name ? $table_name : ("(".$table.")");
		$data["username"] = session('account');
		$data["uid"] = session('aid');
		$data["ip"] = get_client_ip();
		$data["addtime"] = time();

		$sys_log = M('sys_log');
		$sys_log->add($data);

		// 过期日志删除:
		if (mt_rand(1, 1000) <= 10) {
			$this->log_clear();
		}
	}
	
	public function log_clear() {
		if ($this->outtime > 0) {
			$outtime = strtotime("-".intval($this->outtime)." days");
			$sys_log = M('sys_log');
			$sys_log->where('addtime < '.$outtime)->delete();
		}
	}
}