<?php

declare(strict_types = 1);

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);

class App
{
    /**
     * Yaf应用
     * @var \Yaf_Application
     */
    protected $yafApplication;
    /**
     * 初始化
     * @access  public
     * @return  $this
     */
    public function init(): App
    {
        ini_set('yaf.use_spl_autoload', '1');
        $this->yafApplication = new \Yaf_Application(ROOT_PATH . 'config/application.ini', static::environ());
        $this->yafApplication->bootstrap();
        return $this;
    }
    /**
     * 获取Yaf应用实例
     * @access  public
     * @return  \Yaf_Application
     */
    public function getYafApplication(): \Yaf_Application
    {
        return $this->yafApplication;
    }
    /**
     * 运行框架
     * @access public
     */
    public function run()
    {
        $this->yafApplication->run();
    }

    /**
     * 判断是否运行在命令行下
     * @access  public
     * @return  bool
     */
    public static function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * 获取当前运行环境（product|develop 默认product）
     * @access  public
     * @return string
     */
    public static function environ(): string
    {
        return ini_get('yaf.environ') === 'product' ? 'product' : 'develop';
    }
}