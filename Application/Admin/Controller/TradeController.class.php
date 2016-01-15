<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/14/16
 * Time: 10:09 AM
 */

namespace Admin\Controller;


use PHPExcel\Shared\Excel5;

class TradeController extends AdminController
{
    public function index()
    {
//        $this->redirect('orderTeand');
    }

    /**
     * 订单走势
     */
    public function orderTrend()
    {
        $this->display();
    }
}