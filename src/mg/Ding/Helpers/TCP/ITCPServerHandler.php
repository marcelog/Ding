<?php
namespace Ding\Helpers\TCP;

interface ITCPServerHandler
{
    public function handleConnection($myAddress, $myPort, $remoteAddress, $remotePort);
    public function handleData();
    public function handleDisconnect();
}