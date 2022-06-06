<?php

use think\facade\Cache;
use think\facade\Db;

class IndexController extends \Yaf_Controller_Abstract
{
    public function indexAction() {
        echo '<pre>';
        $res = Db::table('system_error_log')->where('hash', '5f92b78317dcadf25893b082e526f889be2a404d')->find();
        var_dump($res);
        return false;
    }
}