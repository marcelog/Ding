<?php
use Ding\Helpers\TCP\ITCPServerHandler;

class MyServerHandler implements ITCPServerHandler
{
    public function beforeOpen()
    {
        echo "before open\n";
    }

    public function beforeListen()
    {
        echo "before listen\n";
    }

    public function close()
    {
        echo "close\n";
    }

    public function handleConnection($remoteAddress, $remotePort)
    {
        global $server;
        echo "new connection from: $remoteAddress:$remotePort\n";
    }

    public function readTimeout($remoteAddress, $remotePort)
    {
        global $server;
        echo "read timeout for $remoteAddress:$remotePort\n";
        $server->disconnect($remoteAddress, $remotePort);
    }

    public function handleData($remoteAddress, $remotePort)
    {
        global $server;
        $buffer = '';
        $len = 4096;
        echo "data from: $remoteAddress:$remotePort\n";
        $server->read($remoteAddress, $remotePort, $buffer, $len);
        echo $buffer . "\n";
        $server->write($remoteAddress, $remotePort, 'You said: ' . $buffer);
    }

    public function disconnect($remoteAddress, $remotePort)
    {
        echo "disconnect: $remoteAddress:$remotePort\n";
    }
}

