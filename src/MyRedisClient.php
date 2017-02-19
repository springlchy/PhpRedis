<?php

class MyRedisClient {
    private $host;
    private $port;
    private $socket;
    private $error;

    public function __construct() {
        $this->host = false;
        $this->error = "";
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * 获取最后一次的socket错误
     * @return string 最后一次socket错误字符串
     */
    public function setSocketError() {
        $errno = socket_last_error($this->socket);
        $this->error = "[$errno]" . socket_strerror($errno);
    }

    /**
     * 获取最后一次错误信息;
     * @return string 最后一次错误信息
     */
    public function getLastError() {
        return $this->error;
    }

    /**
     * 连接redis服务器
     * @param  string  $host redis-server监听的ip
     * @param  integer $port redis-server监听的端口
     * @return boolean     true表示连接成功，false表示连接失败
     */
    public function connect($host = '127.0.0.1', $port = 6379) {
        $this->host = $host;
        $this->port = $port;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            $this->setSocketError();
            return false;
        }

        $result = socket_connect($this->socket, $host, $port);
        if ($result === false) {
            $this->setSocketError();
            return false;
        } else {
            return true;
        }
    }


    /**
     * 关闭连接
     * @return void 没有返回值
     */
    public function close() {
        if ($this->socket != false) {
            socket_close($this->socket);
            $this->socket = false;
        }
    }
}