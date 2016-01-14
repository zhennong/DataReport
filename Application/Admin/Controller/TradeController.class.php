<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 1/14/16
 * Time: 10:09 AM
 */

namespace Admin\Controller;


class TradeController extends AdminController
{
    public function index()
    {
        _vp(D('trade')->where('itemid<1000')->select());
    }
}