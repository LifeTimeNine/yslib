<?php

declare(strict_types = 1);

namespace yslib\plugin;

use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use Yaf_Request_Abstract;
use Yaf_Response_Abstract;

/** 
 * Orm 插件
 */
class Orm extends \Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $config = (new \Yaf_Config_Ini(ROOT_PATH . 'config/database.ini'))->toArray();
        Db::setConfig($config);
        Db::setCache(Cache::instance()->store());
        Db::setLog(Log::instance());
    }
}