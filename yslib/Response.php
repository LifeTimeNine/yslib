<?php

declare(strict_types = 1);

namespace yslib;

use Yaf_Dispatcher;
use Yaf_Response_Http;

/** 响应输出基础类 */
abstract class Response
{
    /**
     * 原始数据
     * @var mixed
     */
    protected $data;

    /**
     * 当前contentType
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * 字符集
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * 状态码
     * @var integer
     */
    protected $code = 200;

    /**
     * 是否允许请求缓存
     * @var bool
     */
    protected $allowCache = true;

    /**
     * header参数
     * @var array
     */
    protected $header = [];

    /**
     * 输出内容
     * @var string
     */
    protected $content = null;

    /**
     * 创建响应对象
     * @access  public
     * @param   mixed   $data   响应数据
     * @param   int     $code   响应状态码
     * @param   array   $header 响应头
     * @return  Response
     */
    public static function create($data = '', int $code = 200, array $header = []): Response
    {
        return (new static)->data($data)->code($code)->header($header);
    }

    /**
     * 发送数据到客户端
     * @access  public
     * @return void
     */
    public function send(): void
    {
        Yaf_Dispatcher::getInstance()->autoRender(false);
        $response = new Yaf_Response_Http;
        $response->setBody($this->getContent());
        $response->setAllHeaders($this->header);
        http_response_code($this->code);
        echo $response;
        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    /**
     * 输出数据设置
     * @access public
     * @param  mixed $data 输出数据
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 是否允许请求缓存
     * @access public
     * @param  bool $cache 允许请求缓存
     * @return $this
     */
    public function allowCache(bool $cache)
    {
        $this->allowCache = $cache;

        return $this;
    }

    /**
     * 是否允许请求缓存
     * @access public
     * @return bool
     */
    public function isAllowCache()
    {
        return $this->allowCache;
    }

    /**
     * 设置响应头
     * @access public
     * @param  array $header  参数
     * @return $this
     */
    public function header(array $header = [])
    {
        $this->header = array_merge($this->header, $header);

        return $this;
    }

    /**
     * 设置页面输出内容
     * @access public
     * @param  mixed $content
     * @return $this
     */
    public function content($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
            $content,
            '__toString',
        ])
        ) {
            throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
        }

        $this->content = (string) $content;

        return $this;
    }

    /**
     * 发送HTTP状态
     * @access public
     * @param  integer $code 状态码
     * @return $this
     */
    public function code(int $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * LastModified
     * @access public
     * @param  string $time
     * @return $this
     */
    public function lastModified(string $time)
    {
        $this->header['Last-Modified'] = $time;

        return $this;
    }

    /**
     * Expires
     * @access public
     * @param  string $time
     * @return $this
     */
    public function expires(string $time)
    {
        $this->header['Expires'] = $time;

        return $this;
    }

    /**
     * ETag
     * @access public
     * @param  string $eTag
     * @return $this
     */
    public function eTag(string $eTag)
    {
        $this->header['ETag'] = $eTag;

        return $this;
    }

    /**
     * 页面缓存控制
     * @access public
     * @param  string $cache 状态码
     * @return $this
     */
    public function cacheControl(string $cache)
    {
        $this->header['Cache-control'] = $cache;

        return $this;
    }

    /**
     * 页面输出类型
     * @access public
     * @param  string $contentType 输出类型
     * @param  string $charset     输出编码
     * @return $this
     */
    public function contentType(string $contentType, string $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;

        return $this;
    }

    /**
     * 获取头部信息
     * @access public
     * @param  string $name 头部名称
     * @return mixed
     */
    public function getHeader(string $name = '')
    {
        if (!empty($name)) {
            return $this->header[$name] ?? null;
        }

        return $this->header;
    }

    /**
     * 获取原始数据
     * @access public
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }

    /**
     * 获取输出数据
     * @access public
     * @return string
     */
    public function getContent(): string
    {
        if (null == $this->content) {
            $content = $this->output($this->data);

            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                $content,
                '__toString',
            ])
            ) {
                throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
            }

            $this->content = (string) $content;
        }

        return $this->content;
    }

    /**
     * 获取状态码
     * @access public
     * @return integer
     */
    public function getCode(): int
    {
        return $this->code;
    }
}