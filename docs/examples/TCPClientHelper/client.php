<?php
use Ding\Helpers\TCP\ITCPClientHandler;

class MyClientHandler implements ITCPClientHandler
{

    public function connectTimeout()
    {
        echo "connection timeout\n";
    }

    public function readTimeout()
    {
        global $client;
        echo "read timeout\n";
        $client->close();
    }
    public function beforeConnect()
    {
        echo "before connect\n";
    }

    public function connect()
    {
        global $connected;
        global $client;
        $connected = true;
        echo "connected\n";
        $client->write("GET / HTTP/1.0\n\n");
    }

    public function disconnect()
    {
        global $connected;
        global $run;
        $connected = false;
        echo "disconnected\n";
        $run = false;
    }

    public function data()
    {
        global $client;
        $buffer = '';
        $len = 4096;
        $len = $client->read($buffer, $len);
        echo "got data (" . $len . "): \n";
        echo $buffer . "\n";
        $client->close();
    }
}
