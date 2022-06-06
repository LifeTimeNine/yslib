<?php

declare(strict_types = 1);

namespace yslib\response;

use yslib\Response;

/**
 * Json 响应
 */
class Json extends Response
{
    protected $contentType = 'application/json';

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     * @throws \Exception
     */
    protected function output($data): string
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            if (false === $data) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }
}