<?php

declare(strict_types = 1);

require 'App.php';

/**
 * 基于 Swoole 的 Http 服务类
 */
class Server
{
    /**
     * 服务配置 参数
     * @var array
     */
    protected $config = [
        'host' => '0.0.0.0',
        'port' => 9501,
        'worker_num' => 1,
        'max_request' => 0,
        'task_worker_num' => 1,
        'task_max_request' => 0,
        'task_enable_coroutine' => false,
        'open_http2_protocol' => false,
        'pid_file' => null,
        'log_file' => null,
        'ssl_cert_file' => null,
        'ssl_key_file' => null,
    ];
    /**
     * 操作
     * @var string
     */
    protected $action = 'start';
    /**
     * 守护进程化
     * @var bool
     */
    protected $daemonize = false;
    /**
     * 构造函数
     */
    public function __construct()
    {
        $iniConfig = new \Yaf_Config_Ini(dirname(__DIR__) . '/config/server.ini');
        
        $this->config = array_merge($this->config, $iniConfig->toArray());

        $this->parseArg();
    }

    /**
     * 启动服务器
     * @access  protected
     * @return  void
     */
    protected function start()
    {
        if ($this->isRuning()) {
            throw new Exception('Server Running!');
        }
        $mode = SWOOLE_TCP;

        // 如果使用 HTTP2 协议
        if (!empty($this->config['open_http2_protocol'])) $mode = $mode | SWOOLE_SSL;
        $httpServer = new \Swoole\Http\Server($this->config['host'], (int)$this->config['port'], SWOOLE_PROCESS, $mode);

        $config = [
            'dispatch_mode' => 3,
            'daemonize' => $this->daemonize,
            'worker_num' => (int)$this->config['worker_num'],
            'max_request' => (int)$this->config['max_request'],
            'task_worker_num' => (int)$this->config['task_worker_num'],
            'task_max_request' => (int)$this->config['task_max_request'],
            'task_enable_coroutine' => !empty($this->config['task_enable_coroutine']),
            'open_http2_protocol' => !empty($this->config['open_http2_protocol']),
            'pid_file' => $this->config['pid_file'] ?: RUNTIME_PATH . 'server/.pid',
            'log_file' => $this->config['log_file'] ?: RUNTIME_PATH . 'server/.log',
        ];
        if (!is_dir(dirname($config['pid_file']))) mkdir(dirname($config['pid_file']), 0777, true);
        if (!is_dir(dirname($config['log_file']))) mkdir(dirname($config['log_file']), 0777, true);
        if (!empty($this->config['ssl_cert_file']) && !empty($this->config['ssl_key_file'])) {
            $config['ssl_cert_file'] = $this->config['ssl_cert_file'];
            $config['ssl_key_file'] = $this->config['ssl_key_file'];
        }
        $httpServer->set($config);

        $applicationConfig = new \Yaf_Config_Ini(ROOT_PATH . 'config/application.ini', App::environ());
        $applicationPath = $applicationConfig->get('application.directory');

        if (!$this->daemonize) {
            $filesLastModifyTime = $this->getDirFilesLastModifyTime($applicationPath);
            $httpServer->addProcess(new \Swoole\Process(function() use(&$filesLastModifyTime){
                \Swoole\Timer::tick(1000, function() use(&$filesLastModifyTime){
                    $hasModify = false;
                    clearstatcache();
                    foreach($filesLastModifyTime as $path => $oldLastModifyTime) {
                        $lastModifyTime = filectime($path);
                        if ($oldLastModifyTime <> $lastModifyTime) {
                            $filesLastModifyTime[$path] = $lastModifyTime;
                            $hasModify = true;
                        }
                    }
                    if ($hasModify) $this->reload();
                });
                \Swoole\Event::wait();
            }, false, 2));
        }

        /** @var Yaf_Application */
        $yafApplication = null;
        $httpServer->on('start', function() {
            echo 'Starting Success!' . PHP_EOL;
            echo 'Host: ' . $this->config['host'] . PHP_EOL;
            echo 'Port: ' . $this->config['port'] . PHP_EOL;
            echo 'Time: ' . date('Y-m-d H:i:s') . PHP_EOL;
        });
        $httpServer->on('workerStart', function(\Swoole\Server $server, $workerId) use(&$yafApplication){
            $yafApplication = (new App)->init()->getYafApplication();
        });
        $httpServer->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) use(&$yafApplication, $httpServer){
            ob_start();
            try {
                \Yaf_Registry::set('server', $httpServer);
                \Yaf_Registry::set('request', $request);
                \Yaf_Registry::set('response', $response);
                $yafRequest = new \Yaf_Request_Http($request->server['request_uri']);
                foreach($request->get ?: [] as $k => $v) $yafRequest->setParam($k, $v);
                foreach($request->post ?: [] as $k => $v) $yafRequest->setParam($k, $v);
                $yafRequest->method = $request->getMethod();
                $yafResponse = $yafApplication->getDispatcher()->returnResponse(true)->dispatch($yafRequest);
                $yafResponse->response();
            } catch(\Throwable $th) {
                $response->status(500);
                if (App::environ() == 'develop') {
                    echo $th->__toString();
                }
            };

            $responseContent = ob_get_contents();
            ob_clean();

            if ($response->isWritable()) {
                foreach($yafResponse->header ?: [] as $k => $v) {
                    $response->header($k, $v, true);
                }
                $response->status($yafResponse->code ?: 200);
                $response->end($responseContent);
            }
        });
        $httpServer->on('task', function(\Swoole\Server $server, $taskId, $srcWorkId, $data) {
            $className = $data[0][0] ?? null;
            if (empty($className) || !class_exists($className)) return;
            $methodName = $data[0][1] ?? null;
            if (empty($methodName) || !method_exists($className, $methodName)) return;
            $arguments = $data[1] ?? [];
            if (!is_array($arguments)) $arguments = [$arguments];
            try {
                $result = (new $className)->{$methodName}(...$arguments);
            } catch (\Throwable $th) {
                echo $th . PHP_EOL;
            }
            $server->finish($result);
        });
        echo 'Starting server...' . PHP_EOL;
        $httpServer->start();
    }

    /**
     * 停止服务器
     * @access  protected
     * @return void
     */
    protected function stop()
    {
        if (!$this->isRuning()) {
            throw new Exception('Server not started!');
        }
        echo 'Stoping server...' . PHP_EOL;
        \Swoole\Process::kill($this->getPid(), SIGTERM);
        $num = 0;
        \Swoole\Timer::tick(400, function($timerId) use(&$num){
            if (!$this->isRuning()) {
                echo '> Success' . PHP_EOL;
                \Swoole\Timer::clear($timerId);
            }
            if ($num++ > 20) {
                echo '> Failure' . PHP_EOL;
            }
        });
        \Swoole\Event::wait();
    }

    /**
     * 柔性重启
     * @access  protected
     * @return void
     */
    protected function reload()
    {
        if (!$this->isRuning()) {
            throw new Exception('Server not started!');
        }
        echo 'Reloading server...' . PHP_EOL;
        \Swoole\Process::kill($this->getPid(), SIGUSR1);
        if (!$this->isRuning()) {
            echo '> Failure' . PHP_EOL;
        } else {
            echo '> Success' . PHP_EOL;
        }
    }

    /**
     * 重启
     * @access protected
     * @return void
     */
    protected function restart()
    {
        $this->stop();
        $this->start();
    }

    /**
     * 运行状态
     * @access  protected
     * @return void
     */
    protected function state()
    {
        if ($this->isRuning()) {
            echo '> Server Runing';
        } else {
            echo '> Server Stop';
        }
        echo PHP_EOL;
    }

    /**
     * 获取服务器PID
     * @access  protected
     * @return bool|int
     */
    protected function getPid()
    {
        return (int)@file_get_contents($this->config['pid_file'] ?: RUNTIME_PATH . 'server/.pid');
    }

    /**
     * 判断服务是否正在运行
     * @access  portected
     * @return bool
     */
    protected function isRuning(): bool
    {
        $pid = $this->getPid();
        if (empty($pid)) return false;
        return \Swoole\Process::kill($pid, 0);
    }

    /**
     * 解析命令行参数
     * @access  protected
     * @return void
     */
    protected function parseArg()
    {
        $argv = $_SERVER['argv'];
        $arguments = getopt('dh::p::', ['daemonize', 'host::', 'port::'], $optind);

        $this->daemonize = isset($arguments['d']) || isset($arguments['daemonize']);
        $this->config['host'] = $arguments['h'] ?? ($arguments['host'] ?? $this->config['host']);
        $this->config['port'] = $arguments['p'] ?? ($arguments['port'] ?? $this->config['port']);

        $this->action = $argv[$optind] ?? $this->action;
    }

    /**
     * 获取目录下所有文件的最后修改时间
     * @access  protected
     * @param   string  $dir    目录地址
     * @return  array
     */
    protected function getDirFilesLastModifyTime(string $dir): array
    {
        if (!is_dir($dir)) return [];
        $fileTimes = [];
        foreach(scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_file($path)) {
                $fileTimes[$path] = filectime($path);
            } elseif (is_dir($path)) {
                $fileTimes = array_merge($fileTimes, $this->getDirFilesLastModifyTime($path));
            }
        }
        return $fileTimes;
    }

    /**
     * 运行
     * @access  pulic
     * @return void
     */
    public function run()
    {
        if (in_array($this->action, ['start', 'stop', 'reload', 'restart', 'state'])) {
            call_user_func([$this, $this->action]);
        }
    }
}