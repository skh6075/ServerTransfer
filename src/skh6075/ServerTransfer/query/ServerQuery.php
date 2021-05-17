<?php

namespace skh6075\ServerTransfer\query;

final class ServerQuery{

    private ?array $server;

    private string $serverAddress;

    private int $serverPort;

    public function __construct (string $ip, int $port) {
        $this->serverAddress = gethostbyname($ip);
        $this->serverPort = $port;
        if (($query = $this->UT3Query($this->serverAddress, $this->serverPort))) {
            $this->server = $query;
        }
    }

    public function getNumPlayer (){
        return $this->server [15];
    }

    public function getServerAddress(): string{
        return $this->serverAddress;
    }

    public function getServerPort(): int{
        return $this->serverPort;
    }

    public function isConnect(): bool{
        return $this->server !== null;
    }

    public function UT3Query ($host, $port){
        $socket = @fsockopen("udp://" . $host, $port);
        if (!$socket)
            return null;
        $online = @fwrite($socket, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01");
        if (!$online)
            return null;
        $challenge = @fread($socket, 1400);
        if (!$challenge)
            return null;
        $challenge = substr(preg_replace("/[^0-9-]/si", "", $challenge), 1);
        $query = sprintf("\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01",$challenge >> 24, $challenge >> 16, $challenge >> 8, $challenge >> 0);
        if (!@fwrite($socket, $query))
            return null;
        $response = array();
        $response[] = @fread($socket, 2048);
        $response = implode($response);
        $response = substr($response, 16);
        $response = explode("\0", $response);
        array_pop($response);
        array_pop($response);
        return $response;
    }
}
