<?php

namespace yslib;

use Yaf_Controller_Abstract;
use  Yaf_Request_Abstract;
use Yaf_Response_Abstract;

/**
 * 控制器
 * @property    public      array                   $actions        动作名与动作类文件路径映射数组
 * @property    protected   string                  $_module        当前请求的模块名
 * @property    protected   string                  $_name          当前请求的控制器名
 * @property    protected   Yaf_Request_Abstract    $_request       当前请求对象
 * @property    protected   Yaf_Response_Abstract   $_response      当前响应对象
 * @property    protected   array                   $_invoke_args   调用参数列表
 * @property    protected   Yaf_View_Interface      $_view          当前视图对象
 */
class Controller extends Yaf_Controller_Abstract
{
    /**
     * Yaf 自动渲染模板
     * @var bool
     */
    protected $yafAutoRender = null;
    /**
     * 渲染内容输出
     * @access  protected
     * @param   string  $tpl        模板名称
     * @param   array   $parameters 模板变量
     * @return  bool
     */
    protected function display($tpl, array $parameters = null)
    {
        return parent::display(...func_get_args());
    }
    /**
     * 将当前的请求转交给另外的Action
     * @access  public
     * @param string $module 模块名
     * @param string $controller 控制器名称
     * @param string $action 操作名称
     * @param array $parameters 参数列表数组
     * @return void
     */
    public function forward($module, $controller = null, $action = null, array $parameters = null)
    {
        parent::forward(...func_get_args());
    }
    /**
     * 获取全部调用参数
     * @access  public
     * @param   $name
     */
    public function getInvokeArgs()
    {
        parent::getInvokeArgs();
    }
     /**
     * 
     * 获取指定调用参数名的值
     * @param string $name 参数名称
     * @return void
     */
    public function getInvokeArg($name)
    {
        parent::getInvokeArg($name);
    }
    /**
     * 获取当前模块名
     * @access  public
     * @return string
     */
    public function getModuleName(): string
    {
        return parent::getModuleName();
    }
    /**
     * 获取当前控制名称
     * @access  public
     * @return  string
     */
    public function getName(): string
    {
        return parent::getName();
    }
    /**
     * 获取当前请求对象
     * @access  public
     * @return   Yaf_Request_Abstract
     */
    public function getRequest():  Yaf_Request_Abstract
    {
        return parent::getRequest();
    }
    /**
     * 获取响应对象
     * @access  public
     * @return Yaf_Response_Abstract 
     */
    public function getResponse(): Yaf_Response_Abstract
    {
        return parent::getResponse();
    }
    /**
     * 返回视图对象
     * @access  public
     * @return Yaf_View_Interface
     */
    public function getView(): Yaf_View_Interface
    {
        return parent::getView();
    }
    /**
     * 获取模板文件目录
     * @access  public
     * @return string
     */
    public function getViewPath(): string
    {
        return parent::getViewPath();
    }
    /**
     * 控制器初始化
     * @access  public
     * @return void
     */
    public function init(): void {}
    /**
     * 初始化视图对象
     * @access  public
     * @param   array   $options    参数
     * @return void
     */
    public function initView(array  $options = null): void
    {
        parent::initView($options);
    }

    /**
     * 将当前请求重定向到指定的URL
     * @access public
     * @param string $url 跳转的地址
     * @return void
     */
    public function redirect($url): void
    {
        parent::redirect($url);
    }
    /**
     * 渲染动作对应的模板，并返回结果
     * @access public
     * @param   mixed  $tpl        模板名称
     * @param   array   $parameters 模板变量
     * @return string
     */
    protected function render($tpl, array $parameters = null): string
    {
        return parent::render($tpl, $parameters);
    }
    /**
     * 设置模板文件目录
     * @param string $view_directory 模板目录
     * @return void
     */
    public function setViewPath($view_directory)
    {
        parent::setViewPath($view_directory);
    }
}
