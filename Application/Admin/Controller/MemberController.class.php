<?php
/*
 * @Controller 会员模块
 * @Date 2016/01/18
 * @Author  iredbaby   1596229276@qq.com
 */
namespace Admin\Controller;
Use Common\Controller\AuthController;
use Common\Tools;
Use Think\Auth;
class MemberController extends AuthController
{
	/**
	 * 勿删权限判断
	 */
	public function memberIndex()
	{
		$this->display();
	}
	/**
	 * 会员月注册趋势
	 * @author iredbaby
	 */
	public function memberReg()
	{
		$Member = D('Member');
		$month_start = strtotime('January 2013');
		$mouth_solt = get_month_solt($month_start, $this->month_end);
		foreach ($mouth_solt as $k => $v) {
			$map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
			$mouth_solt_member[$k]['mouth_sort'] = $v;
			$mouth_solt_member[$k]['member'] = $Member->field('regtime')->where($map)->select();
			$mouth_solt_member[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
			$mouth_solt_member[$k]['member_amount'] = count($mouth_solt_member[$k]['member']);
			unset($mouth_solt_member[$k]['member']);
		}
		$this->assign(['mouth_solt_member' => $mouth_solt_member]);
		$this->assign(['month_start' => $month_start]);
		$this->display();
	}
	/**
	 * 会员信息 带分页
	 */
	public function memberInfos()
	{
		$Trade = D('Trade');
		$map['status'] = array('in', '2,3,4');
		$count = $Trade->field('buyer')->where($map)->count(('DISTINCT buyer'));
		$Page = new \Think\Page($count, 20);
		$show = $Page->show();
		$sql = "SELECT ft.addressid,ft.buyer,ft.buyer_name,ft.buyer_mobile,SUM(ft.total) as a,SUM(ft.amount) as b,ad.areaid FROM destoon_finance_trade AS ft,destoon_address AS ad WHERE ft.addressid = ad.itemid AND status in (2,3,4) GROUP BY buyer ORDER BY b LIMIT ".$Page->firstRow . "," . $Page->listRows ;
		$data = queryMysql($sql);
		$all_amount_count = $Trade->where($map)->field('amount')->sum('amount');
		foreach ($data as $k => $v) {
			$member_info[$k]['addressid'] = $v['addressid'];
			$member_info[$k]['buyer'] = $v['buyer'];
			$member_info[$k]['buyer_name'] = $v['buyer_name'];
			$member_info[$k]['buyer_mobile'] = $v['buyer_mobile'];
			$member_info[$k]['total'] = $v['a'];
			$member_info[$k]['amount'] = $v['b'];
			$member_info[$k]['rate'] = round($v['b'] / $all_amount_count * 100, 5);
			$arealist = getAreaFullNameFromAreaID($v['areaid']);
			$member_info[$k]['area'] = arr2str($arealist,'');
		}
		$this->assign(['member_info' => $member_info, 'day_s' => $this->month_start, 'day_e' => $this->month_end, 'show' => $show, 'count' => $count]);
		$this->display();
	}
	protected function getCacheTrade(){
		if(!S('data')){
			$sql = "SELECT ft.addressid,ft.buyer,ft.buyer_name,ft.buyer_mobile,SUM(ft.total) as a,SUM(ft.amount) as b,ad.areaid FROM destoon_finance_trade AS ft,destoon_address AS ad WHERE ft.addressid = ad.itemid AND status in (2,3,4) GROUP BY buyer ORDER BY b";
			$data = queryMysql($sql);
			S('data',$data);
		}
	}
	//导出Excel表
	public function exportExcel()
	{
		$Trade = D('Trade');
		$map['status'] = array('in', '2,3,4');
		$map['status'] = array('neq', '');
//        $limit = I('get.limit');
//        $count = I('get.count');
		if (I('get.type') == 'export') {
			$this->getCacheTrade();
			$data = S('data');
//            $sql = "SELECT ft.addressid,ft.buyer,ft.buyer_name,ft.buyer_mobile,SUM(ft.total) as a,SUM(ft.amount) as b,ad.areaid FROM destoon_finance_trade AS ft,destoon_address AS ad WHERE ft.addressid = ad.itemid AND status in (2,3,4) GROUP BY buyer ORDER BY b LIMIT " . $limit ."," . $count ;
//            $data = queryMysql($sql);
			$all_amount_count = $Trade->where($map)->field('amount')->sum('amount');
			foreach ($data as $k => $v) {
				$member_info[$k]['buyer'] = $v['buyer'];
				$member_info[$k]['buyer_name'] = $v['buyer_name'];
				$member_info[$k]['total'] = $v['a'];
				$member_info[$k]['amount'] = $v['b'];
				$member_info[$k]['rate'] = round($v['b'] / $all_amount_count * 100, 5) . '%';
				$member_info[$k]['buyer_mobile'] = $v['buyer_mobile'];
//                导出所在地区
				$arealist = getAreaFullNameFromAreaID($v['areaid']);
				$member_info[$k]['area'] = arr2str($arealist,'');
			}
			$fileName = "会员信息";
			$headArr = array('账号', '姓名', '购买数量', '交易额', '购买率','联系电话');
			exportExcel($fileName, $headArr, $member_info); //数据导出
		}
	}
	/**
	 * 获取新注册会员
	 * @author iredbaby
	 */
	private function get_new_member($date_id)
	{
		$Member = D('Member');
		$map_new['regtime'] = [['gt', $date_id], ['lt', $this->now]];
		$reg_member_data = $Member->where($map_new)->field('username,regtime')->select();
		foreach ($reg_member_data as $key => $value) {
			$reg_member_count[] = $value['username'];
		}
		$reg_member_str = implode(',', $reg_member_count);
		return $reg_member_str;
	}
	/**
	 * 会员付款统计
	 * @param format_date ($i)  1 年  2 月 3 日
	 * @author iredbaby
	 */
	public function memberPay()
	{
		$Trade = D('Trade');
		//按年月日全部付款
		$map['status'] = ['in', [2, 3, 4]];
		for ($i = 1; $i <= 3; $i++) {
			$map['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', $this->now]];
			$member_type[] = $i;
			$member_count[] = $Trade->where($map)->field('buyer,buyer_name,paytime')->count('distinct buyer');
		}
		$day_new = $month_new = 0;
		//日付款新会员
		$map['paytime'] = [['lt', $this->now_d_start]];
		$buyer_all_before_day = Tools::getCols($Trade->where($map)->field("buyer")->group('buyer')->select(), 'buyer');
		$map['paytime'] = [['gt', $this->now_d_start]];
		$buyer_after_day = $Trade->where($map)->field("buyer")->group('buyer')->select();
		foreach ($buyer_after_day as $k => $v) {
			if (!in_array($v['buyer'], $buyer_all_before_day)) {
				$day_new++;
			}
		}
		//月付款新会员
		$map['paytime'] = [['lt', $this->now_m_start]];
		$buyer_all_before_month = Tools::getCols($Trade->where($map)->field("buyer")->group('buyer')->select(), 'buyer');
		$map['paytime'] = [['gt', $this->now_m_start]];
		$buyer_after_month = $Trade->where($map)->field("buyer")->group('buyer')->select();
		foreach ($buyer_after_month as $k => $v) {
			if (!in_array($v['buyer'], $buyer_all_before_month)) {
				$month_new++;
			}
		}
		/*for ($i = 2; $i <= 3; $i++) {
            $day_member_name = $this->get_new_member(format_date($i));
            $map['paytime'] = [['neq', 0], ['gt', format_date($i)], ['lt', $this->now]];
            $map['buyer'] = [['in', $day_member_name]];
            $map['status'] = ['in',[2,3,4]];
            $new_member_type[] = $i;
            $new_member_str = $Trade->where($map)->field('buyer,buyer_name,paytime')->count('distinct buyer');
            if (empty($new_member_str)) {
                $new_member_count[] = 0;
            } else {
                $new_member_count[] = $new_member_str;
            }
        }*/
		//全部付款
		$map_all['paytime'] = [['neq', 0]];
		$all = $Trade->where($map_all)->field('buyer_name,paytime')->count('distinct buyer_name');
		$this->assign(['day' => $member_count[2], 'month' => $member_count[1], 'year' => $member_count[0], 'all' => $all]);
		$this->assign(['day_new' => $day_new, 'month_new' => $month_new]);
		$this->display();
	}
	/**
	 * 会员APP注册
	 */
	public function memberRegApp()
	{
		$Member = D('Member');
		$month_start = strtotime('May 2015');
		$mouth_solt = get_month_solt($month_start, $this->month_end);
		foreach ($mouth_solt as $k => $v) {
			$map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
			$map['comefrom'] = [['eq', 'touch']];
			$mouth_solt_member_app[$k]['mouth_sort'] = $v;
			$mouth_solt_member_app[$k]['member'] = $Member->field('regtime,comefrom')->where($map)->select();
			$mouth_solt_member_app[$k]['mouth_name'] = date("Y-m", $v['start']['ts']);
			$mouth_solt_member_app[$k]['member_amount'] = count($mouth_solt_member_app[$k]['member']);
			unset($mouth_solt_member_app[$k]['member']);
		}
		$this->assign(['mouth_solt_member_app' => $mouth_solt_member_app]);
		$this->assign(['month_start' => $month_start]);
		$this->display();
	}
	/**
	 * 会员统计
	 */
	public function memberCount()
	{
//        $Area = D('Area');
//        $provice_id = I('pid');
//        $provice = $this->getProvice();
//        $provice_name = $this->getProvice(1, $provice_id);
//        if ($provice_id == "") {
//            $provice_id = 17;
//        } //默认河南省
//
//        $data = $Area->where('parentid =' . $provice_id)->select();
//        if ($provice_id > 4 && $provice_id < 33) {
//            foreach ($data as $k => $v) {
//                $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,COUNT(b.areaid) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='" . $v['areaid'] . "' group by b.areaid";
//                $data[$k]['sub'] = queryMysql($sql);
//            }
//        } else { //特殊城市处理
//            foreach ($data as $k => $v) {
//                if ($k > 0) {
//                    unset($data[$k]);
//                } else {
//                    $sql = "select a.areaid as areaid,a.areaname as areaname,b.areaid as areaids,COUNT(b.areaid) as total from `destoon_area` as a,`destoon_member` as b where a.areaid = b.areaid AND a.parentid='" . $v['parentid'] . "' group by b.areaid";
//                    $data[$k]['sub'] = queryMysql($sql);
//                }
//            }
//        }
//
//        $this->assign('data', $data);
//        $this->assign('provice', $provice);
//        $this->assign('provice_name', $provice_name[0]['areaname']);
//        $this->display();
		$member = D('Member');
		$count = $member->cache(true)->count();
		$this->assign(['provice'=>$this->getMemProvice(),'city'=>$this->getMemCity(),'county'=>$this->getMemCounty(),'count'=>$count]);
		$this->display();
	}
	//获取省会员数
	public function getMemProvice(){
		$area = D('Area');
		$member = D('Member');
		$map['parentid'] = array('eq',0);
		$data_area = $area->cache(true)->where($map)->select();
		foreach($data_area AS $k=>$v){
			$map2['areaid'] = array('in',$v['arrchildid']);
			$data = $member->cache(true)->field('count(*) as count')->where($map2)->select();
			foreach($data AS $k2=>$v2){
				if($v2['count'] > 0){
					$data_area[$k]['count'] = $v2['count'];
				}
			}
		}
		return $data_area;
	}
	//获取市会员数
	public function getMemCity(){
		$id = I('get.pid');
		if(!empty($id)){
			$area = D('Area');
			$member = D('Member');
			$map['parentid'] = array('eq',$id);
			$data_area = $area->cache(true)->where($map)->select();
			foreach($data_area AS $k=>$v){
				$map2['areaid'] = array('in',$v['arrchildid']);
				$data = $member->cache(true)->field('count(*) as count')->where($map2)->select();
				foreach($data AS $k2=>$v2){
					if($v2['count'] > 0){
						$data_area[$k]['count'] = $v2['count'];
					}
				}
			}
			return $data_area;
		}
	}
	//获取县会员数
	public function getMemCounty(){
		$id = I('get.cid');
		if(!empty($id)){
			$area = D('Area');
			$member = D('Member');
			$map['parentid'] = array('eq',$id);
			$data_area = $area->cache(true)->where($map)->select();
			foreach($data_area AS $k=>$v){
				$map2['areaid'] = array('in',$v['arrchildid']);
				$data = $member->cache(true)->field('count(*) as count')->where($map2)->select();
				foreach($data AS $k2=>$v2){
					if($v2['count'] > 0){
						$data_area[$k]['count'] = $v2['count'];
					}
				}
			}
			return $data_area;
		}
	}
	/**
	 * 获取省份
	 */
	public function getProvice($id, $pid)
	{
		$m = D('area');
		$field = '';
		$data = $m->field($field)->where('parentid = 0')->select();
		if ($id == 1) {
			$data = $m->field('areaname')->where('areaid = ' . $pid)->select();
		}
		return $data;
	}
	/**
	 * 终端注册饼形图
	 */
	public function memberRegAppChart()
	{
		$Member = D('Member');
		$field = ['userid', 'comefrom'];
		/*
         * pc注册的数量
         */
		$map['comefrom'] = 'web';
		$sel_AppChart_list = $Member->where($map)->field($field)->select();
		$cat_group = Tools::groupBy($sel_AppChart_list, 'userid');
		foreach ($cat_group as $k => $v) {
			$x_pc[$k]['userid'] = $v[0]['userid'];
			$x_pc[$k]['count'] = count($v);
		}
		sort($x_pc);
		/*
         * 手机注册的数量
         */
		$map['comefrom'] = 'touch';
		$sel_AppChart_list = $Member->where($map)->field($field)->select();
		$cat_group = Tools::groupBy($sel_AppChart_list, 'userid');
		foreach ($cat_group as $k => $v) {
			$x_mobel[$k]['userid'] = $v[0]['userid'];
			$x_mobel[$k]['count'] = count($v);
		}
		sort($x_mobel);
		//重组数据
		$appChart_pc = get_arr_k_amount($x_pc, 'count');
		$appChart_mobel = get_arr_k_amount($x_mobel, 'count');
		//注入显示
		$this->assign(['appChart_pc' => $appChart_pc, 'appChart_mobel' => $appChart_mobel]);
		$this->display();
	}
	public function memberExport()
	{
		$Member = D('Member');
		$map['status'] = ['in', '2,3,4'];
		$mouth_solt = get_month_solt($this->month_start,$this->month_end);
		foreach ($mouth_solt as $k => $v)
		{
			$time_start = $mouth_solt[1]['start']['ts'];
			$time_end = $mouth_solt[$k]['end']['ts'];
			$map['regtime'] = [between,[$time_start,$time_end]];
			$mouth_solt_data[$k]['mouth_solt'] = $v;
			$x = $Member->field('userid')->where($map)->select();
			$mouth_solt_data[$k]['count'] = count($x);
		}
		$count = $mouth_solt_data[$k]['count'];
		$Page = new \Think\Page($count, 20);
		$pages = $Page->show();
		$time_start = $mouth_solt[1]['start']['ts'];
		$time_end = $mouth_solt_data[$k]['mouth_solt']['end']['ts'];
		$sql = "SELECT a.username,a.truename,a.mobile,a.areaid,b.areaname FROM destoon_member AS a LEFT JOIN destoon_area AS b ON (a.areaid = b.areaid) where a.regtime between $time_start and $time_end LIMIT ".$Page->firstRow . "," . $Page->listRows;
		$data = queryMysql($sql);
		foreach ($data as $key=>$value)
		{
				$member_info[$key]['username'] = $value['username'];
				$member_info[$key]['truename'] = $value['truename'];
				$member_info[$key]['mobile'] = $value['mobile'];
				$arealist = getAreaFullNameFromAreaID($value['areaid']);
				$member_info[$key]['areaname'] = arr2str($arealist,'');
		}
		$this->assign(['member_info' => $member_info, 'pages' => $pages, 'count' => $count,'month_start'=>$this->month_start,'month_end'=>$this->month_end]);
		$this->display();
	}
	protected function getCacheMember(){
		if(!S('data')){
			$sql = "SELECT a.username,a.truename,a.mobile,a.areaid,b.areaname FROM destoon_member AS a LEFT JOIN destoon_area AS b ON (a.areaid = b.areaid)";
			$data = queryMysql($sql);
			S('data',$data);
		}
	}

	/**
	 * 导出用户数据
	 */
	public function memberExcel()
	{
		//
		$Member = D('Member');
		$map['status'] = ['in', '2,3,4'];
		$month_start = I('get.month_start');
		$month_end = I('get.month_end');
		$mouth_solt = get_month_solt($month_start,$month_end);
		if (I('get.type') == 'export') {
			foreach ($mouth_solt as $k => $v)
			{
				$time_start = $mouth_solt[1]['start']['ts'];
				$time_end = $mouth_solt[$k]['end']['ts'];
				$map['regtime'] = [between,[$time_start,$time_end]];
				$mouth_solt_data[$k]['mouth_solt'] = $v;
				$x = $Member->field('userid')->where($map)->select();
				$mouth_solt_data[$k]['count'] = count($x);
			}
			$time_start = $mouth_solt[1]['start']['ts'];
			$time_end = $mouth_solt_data[$k]['mouth_solt']['end']['ts'];
			$sql = "SELECT a.username,a.truename,a.mobile,a.areaid,b.areaname FROM destoon_member AS a LEFT JOIN destoon_area AS b ON (a.areaid = b.areaid) where a.regtime between $time_start and $time_end ";
			$data = queryMysql($sql);
			if(!empty($data)){
				foreach ($data as $k => $v) {
					$member_info[$k]['username'] = $v['username'];
					$member_info[$k]['truename'] = $v['truename'];
					$member_info[$k]['mobile'] = $v['mobile'];
					$arealist = getAreaFullNameFromAreaID($v['areaid']);
					$member_info[$k]['areaname'] = arr2str($arealist,'');
				}
				$fileName = "会员信息";
				$headArr = array('用户名', '姓名', '联系方式', '所在地区');
				exportExcel($fileName, $headArr, $member_info); //数据导出
			}else{
				$this->error('错误');
			}
		}
	}

    public function memberList()
    {
        $day_search = I("get.search");
        $day_search = Tools::str2arr($day_search['value']);
        $this->day_start = strtotime($day_search[0] . ' 00:00:00');
        $this->day_end = strtotime($day_search[1] . ' 23:59:59');
        // 字段
        $column = [
            ['select'=>'member.userid','as'=>'userid','show_name'=>'用户id'],
            ['select'=>'member.username','as'=>'username','show_name'=>'账号'],
            ['select'=>'member.truename','as'=>'truename','show_name'=>'姓名'],
            ['select'=>'member.topagentid','as'=>'agent_id','show_name'=>'代理商id'],
            ['select'=>'member.mobile','as'=>'mobile','show_name'=>'手机'],
            ['select'=>'member_trade.trade_count','as'=>'trade_count','show_name'=>'订单数'],
            ['select'=>'member_trade.trade_total','as'=>'trade_total','show_name'=>'订单产品数'],
            ['select'=>'member_trade.amount','as'=>'amount','show_name'=>'订单总额'],
        ];
        if($draw = I("get.draw")){
            // 预定义
            $start = $_GET['start'];
            $limit = $_GET['length'];
            $order = $_GET['order'];
            $search[] = " member.regtime > {$this->day_start} AND member.regtime < {$this->day_end}";

            // 重组条件
            $order = "{$column[$order[0]['column']]['as']} {$order[0]['dir']}";
            foreach ($_GET['columns'] as $k => $v) {
                if ($v['search']['value'] != '') {
                    $search[] = "{$column[$v['data']]['select']} LIKE '%{$v[search][value]}%'";
                }
            }
            $search = Tools::arr2str($search, " AND ");
            foreach($column as $k => $v){
                $field[] = "{$v['select']} AS {$v['as']}";
            }
            $field = Tools::arr2str($field);

            // 查询总数
            $_sql = "SELECT {$field} FROM (SELECT userid, username, mobile, topagentid, regtime, truename FROM __MALL_member WHERE regtime > {$this->day_start} AND regtime < {$this->day_end} GROUP BY mobile) AS member
                LEFT JOIN (SELECT buyer, COUNT(itemid) AS trade_count, SUM(total) AS trade_total, SUM(amount) AS amount FROM __MALL_finance_trade WHERE addtime > {$this->day_start} AND addtime < {$this->day_end} AND status IN(2, 3, 4) GROUP BY buyer) AS member_trade ON member.username = member_trade.buyer
                WHERE {$search}
                ORDER BY {$order}";
            $sql = "SELECT COUNT(*) as total FROM ({$_sql}) AS x ";
            $x = $this->MallDb->list_query($sql);
            $total = $x[0]['total'];

            // 查询数据并重组
            $sql = "{$_sql}
                LIMIT {$start}, {$limit}";
//            Tools::_vp($this->MallDb->getSql($sql),0,2);
            $data = $this->MallDb->list_query($sql);
            foreach ($data as $k => $v) {
                foreach ($column as $key => $value) {
                    $x[$k][] = $v[$value['as']];
                }
            }

            //获取Datatables发送的参数 必要
            $show = [
                "draw" => $draw,
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $x,
            ];
            $x = json_encode($show);
            echo $x;
            exit();
        }else{
            $sql = "SELECT trade.* FROM __MALL_finance_trade AS trade
            LEFT JOIN __MALL_sell_5 AS product ON trade.p_id = product.itemid
            LIMIT 0,10";
            $orderList = $this->MallDb->list_query($sql);
            $this->assign(['column'=>$column,'orderList'=>$orderList]);
            $this->display();
        }
    }

	/*
	 *会员年注册比例
	 */
	public function memberYear(){
		$Member = D("Member");
		$year_solt = get_year_solt($this->year_start, $this->year_end);
		$map['status'] = ['in', '2,3,4'];
		foreach ($year_solt as $k => $v) {
			$map['regtime'] = [['gt', $v['start']['ts']], ['lt', $v['end']['ts']]];
			$x = $Member->field('userid')->where($map)->select();
			$member_year_data[$k]['year_name'] = date("Y", $v['start']['ts']);
			$member_year_data[$k]['count'] = count($x);
		}
		$xAxis_data = Tools::arr2str(Tools::getCols($member_year_data, 'year_name', true));
		$series_data_information = Tools::arr2str(Tools::getCols($member_year_data, 'count'));
		$this->assign(['xAxis_data' => $xAxis_data, 'series_data_information' => $series_data_information,]);
		$this->display();
	}

	/*
	 * 会员购买记录总数
	 */
	public function memberPurchase(){
		//计算用户总量
		$Member = D("Member");
		$map['status'] = ['in', '2,3,4'];
		$mouth_solt = get_month_solt($this->month_start,$this->month_end);

		foreach ($mouth_solt as $k => $v)
		{
			$time_start = $mouth_solt[1]['start']['ts'];
			$time_end = $mouth_solt[$k]['end']['ts'];
			$map['regtime'] = [between,[$time_start,$time_end]];
			$mouth_solt_data[$k]['mouth_solt'] = $v;
			$x = $Member->field('userid')->where($map)->select();
			$mouth_solt_data[$k]['count'] = count($x);
			$sql ="SELECT count(username) as username FROM (SELECT a.username FROM destoon_member as a left join destoon_finance_trade as b on a.username=b.buyer WHERE b.updatetime BETWEEN $time_start AND $time_end GROUP BY b.buyer) AS x";
			$data = $Member->query($sql);
			$mouth_solt_data[$k]['count_pirchase'] = $data;
		}
		$count = $mouth_solt_data[$k]['count'];
		$count_pirchase = $mouth_solt_data[$k]['count_pirchase'];
		$this->assign(['member_count' => $count,'count_pirchase' => $count_pirchase[0]['username']]);
		$this->display();
	}
}