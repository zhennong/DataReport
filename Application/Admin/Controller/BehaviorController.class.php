<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-14
 * Time: 上午9:14
 */

namespace Admin\Controller;
use Admin\FixedData;
use Common\Tools;

class BehaviorController extends AdminController
{
    use FixedData;
    //默认配置 对栏目权限判断
    public function behavior_index()
    {
        $this->display('behavior_index');
    }

    /*
     * 改价记录
     * @author Edwin
     */
    public function ChangePrice()
    {
        $change = D('Change');
        $count = $change->count();
        $Page = new \Think\Page($count, 25);
        $pages = $Page->show();
        $_GET['p'] = $_GET['p'] ? $_GET['p'] : 1;
        $list = $change->order('addtime DESC')->limit($_GET['p'] . ',25')->select();
        $this->assign('list', $list);
        $this->assign('pages', $pages);
        $this->display();
    }

    /*
     * 订单操作记录
     * author Edwin
     */
    public function OrderOperation()
    {
        if ($draw = I("get.draw"))
        {
            // 字段
            $column = [
                ['select' => 'itemid', 'as' => 'itemid'],
                ['select' => 'tradeid', 'as' => 'tradeid'],
                ['select' => 'editor', 'as' => 'editor'],
                ['select' => 'addtime', 'as' => 'addtime'],
                ['select' => 'status', 'as' => 'status'],
            ];

            // 预定义
            $start = $_GET['start'];
            $limit = $_GET['length'];
            $order = $_GET['order'];
            $search[] = "status in(2,3,4)";

            // 重组条件
            $order = "{$column[$order[0]['column']]['as']} {$order[0]['dir']}";
            foreach ($_GET['columns'] as $k => $v) {
                if ($v['search']['value'] != '') {
                    $search[] = "{$column[$v['data']]['select']} LIKE '%{$v[search][value]}%'";
                }
            }
            $search = Tools::arr2str($search, " AND ");
            foreach ($column as $k => $v) {
                $field[] = "{$v['select']} AS {$v['as']}";
            }
            $field = Tools::arr2str($field);

            // 查询总数
        $orderOperation = D('OrderOperation');
            $order_count = $orderOperation->field(['count(*)' => 'count'])->where($search)->group("itemid")->select();
            $total = count($order_count);

             //查询数据并重组
            $data = $orderOperation->field($field)->where($search)->group("itemid")->order($order)->limit($start, $limit)->select();
            foreach ($data as $k => $v) {
                $data[$k]['status'] = $v['status'] = $this->order_status[$v['status']]['status_name'];
                $data[$k]['addtime'] = $v['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
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
        }
        $this->display();
    }
}
