<?php

declare(strict_types = 1);

namespace yslib\plugin;

use think\facade\Cache as FacadeCache;
use Yaf_Request_Abstract;
use Yaf_Response_Abstract;

/**
 * 缓存插件
 */
class Cache extends \Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $config = (new \Yaf_Config_Ini(ROOT_PATH . 'config/cache.ini'))->toArray();
        FacadeCache::init($config);
    }
}