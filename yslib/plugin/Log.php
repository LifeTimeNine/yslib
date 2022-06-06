<?php

declare(strict_types = 1);

namespace yslib\plugin;

use think\facade\Log as FacadeLog;
use Yaf_Plugin_Abstract;
use Yaf_Request_Abstract;
use Yaf_Response_Abstract;

/**
 * 日志插件
 */
class Log extends Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $config = (new \Yaf_Config_Ini(ROOT_PATH . 'config/log.ini'))->toArray();
        FacadeLog::init($config);
    }
}