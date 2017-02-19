<?php

class MyRedisClient {
    private $host;
    private $port;
    private $socket;
    private $error;

    public function __construct() {
        $this->socket = false;
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

    /**
     * [set description]
     * @param [type]  $key     [description]
     * @param [type]  $value   [description]
     * @param integer $expires [description]
     */
    public function set($key, $value, $expires = 5) {
        $command = sprintf("*5\r\n\$3\r\nset\r\n\$%d\r\n%s\r\n\$%d\r\n%s\r\n\$2\r\nEX\r\n\$%d\r\n%s\r\n", strlen($key), $key, strlen($value . ''), $value, strlen($expires . ''), $expires);

        $result = socket_write($this->socket, $command, strlen($command));
        if ($result === false) {
            $this->setSocketError();
            return false;
        }

        $code = socket_read($this->socket, 128, PHP_NORMAL_READ);
        socket_read($this->socket, 1, PHP_BINARY_READ);

        if ($code[0] == '+') {
            return true;
        } elseif ($code[0] == '-') {
            $this->error = $code;
            return false;
        }
    }

    /**
     * [get description]
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function get($key) {
        $command = sprintf("*2\r\n\$3\r\nget\r\n\$%d\r\n%s\r\n", strlen($key), $key);

        $result = socket_write($this->socket, $command, strlen($command));
        if ($result === false) {
            $this->setSocketError();
            return false;
        }

        $line1 = socket_read($this->socket, 1024, PHP_NORMAL_READ);

        $len = intval(trim($line1, '$\r'));
        if ($len == -1) {
            socket_read($this->socket, 1, PHP_BINARY_READ);
            return false;
        }

        $line2 = socket_read($this->socket, $len+3, PHP_BINARY_READ);
        return substr($line2, 1, $len);
    }
}