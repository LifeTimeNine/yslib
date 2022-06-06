<?php

use yslib\plugin\Cache;
use yslib\plugin\Log;
use yslib\plugin\Orm;

class Bootstrap extends \Yaf_Bootstrap_Abstract
{
    /**
     * 文件加载
     * @access  public
     * @param   \Yaf_Dispatcher $dispatcher Yaf调度类
     * @return void
     */
    public function _initLoader(\Yaf_Dispatcher $dispatcher)
    {
        Yaf_Loader::import(ROOT_PATH . 'vendor/autoload.php');
    }
    /**
     * 配置文件加载
     * @access  public
     * @param   \Yaf_Dispatcher $dispatcher Yaf调度类
     * @return void
     */
    public function _initConfig(\Yaf_Dispatcher $dispatcher)
    {
        $config = [];
        foreach(scandir(ROOT_PATH . 'config') as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . $item;
            $pathinfo = pathinfo($path);
            if ($pathinfo['extension'] == 'ini') {
                $config[$pathinfo['filename']] = (new \Yaf_Config_Ini($path))->toArray();
            }
        }
        \Yaf_Registry::set('config', $config);
    }

    /**
     * 注册插件
     * @access public
     * @param   \Yaf_Dispatcher $dispatcher Yaf调度类
     * @return void
     */
    public function _initPlugin(\Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new Cache);
        $dispatcher->registerPlugin(new Log);
        $dispatcher->registerPlugin(new Orm);
    }
}