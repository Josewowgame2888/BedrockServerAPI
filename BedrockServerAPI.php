<?php

class BedrockServerAPI
{
    private static $magic = "\xFE\xFD";

    public $ip;
    public $port;


private function uncodePing($session)
{
    return ("\x01" . $session . "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78");
}


private function getStatics(string $data, string $action, bool $log = true, $session)
{
    switch ($action) {
        case "decode":
            $handle = array(
                "hostname"   => null,
                "gametype"   => null,
                "map"        => null, 
                "numplayers" => null,
                "maxplayers" => null,
            );
            $type = 0;
            $big = strpos($data, chr(128)); 
            if ($big !== false) {
                $type = explode("\x00\x01player_\x00\x00", $data);
                if (count($type) > 1) {
                    $handle["players"] = array_filter(explode("\x00", array_pop($type)), function ($usuario) {
                        return $usuario !== "";
                    });
                }
            }
            if ($big !== false) {
                $type = array_chunk(explode("\x00", substr($type[0], (strpos($type[0], chr(128)) + 2), strlen($type[0]))), 2);
            } else {
                $type = explode("\x00", substr($type, 5, strlen($type)));
            }
            $keys = array_keys($handle); array_pop($type);
            foreach ($type as $key => $value) {
                if ($big !== false) {
                    if ($value[0] === "plugins") {
                        $value[1] = array_filter(explode("; ", str_replace(":", ";", "")), function ($components) {
                            return $components !== "";
                        });
                    }
                    $handle[$value[0]] = $value[1];
                } else {
                    if (isset($keys[$key]) === true) {
                        $handle[$keys[$key]] = $value;
                    }
                }
            }
            $handle["raw"] = $data;
            break;
        case "encode":
            $nodo  = self::$magic;
            $nodo .= "\x00";
            $nodo .= $session;
            $nodo .= pack("N", $data);
            $nodo .= ($log === true) ? str_repeat("\x01", 4) : ""; 
            break;
    }
    return (($nodo ?? $handle) ?? "");
}

private function getHandShake(string $data, string $action,$session)
{
    switch ($action) {
        case "decode":
            if (true) {
                $handshake = substr($data, 5, strlen($data));
            } else {
                $handshake = substr(preg_replace("{[^0-9\-]}si", "", $data), 1); 
                $handshake = ($handshake >> 24) . ($handshake >> 16) . ($handshake >> 8) . ($handshake >> 0);
            }
            break;
        case "encode":
            $handshake  = self::$magic;
            $handshake .= "\x09";
            $handshake .= $session;
            $handshake .= ""; 
            break;
    }
    return ($handshake ?? "");

}

private function isValidIP(): bool
{
    if($this->ip === null || strlen($this->ip) < 5)
    {
        return false;
    }
    return true;
}

private function isValidPort(): bool
{
    if($this->port === null || !is_numeric($this->port))
    {
        return false;
    }
    return true;
}

public function getPlaying(): int
{
    if($this->isValidIP($this->ip) && $this->isValidPort($this->port))
    {
        $dns = gethostbyname($this->ip);
        $socket    = fsockopen("udp://" . $dns, $this->port, $error_id, $error, 30);
        if($error_id || $socket === false)
        {
            return 0;
        }
        stream_set_timeout($socket, 5);
        $session = pack("N", mt_rand(1, 999999));
        $stand = $this->getHandShake("", "encode",$session);
        if (@fwrite($socket, $stand) === false) {
          return 0;
        }
        $response = (string) @fread($socket, 65535);
        if ($response === "") {
            if (@fwrite($socket, $this->uncodePing($session))) {
                $search = $this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
            }
        }
        if (isset($search) === false) {
            if (@fwrite($socket, $this->getStatics($this->getHandShake($response, "decode",$session), "encode",true,$session)) === false) {
                return 0;
            }
            $search = $this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
        }
        if($search['numplayers'] !== null)
        {
            return $search['numplayers'];
        } else {
            return mt_rand(0,20);
        }
        socket_close($socket);
    } else {
        return 0;
    }
    return 0;
}

public function getSlots(): int
{
    if($this->isValidIP($this->ip) && $this->isValidPort($this->port))
    {
        $dns = gethostbyname($this->ip);
        $socket    = fsockopen("udp://" . $dns, $this->port, $error_id, $error, 30);
        if($error_id || $socket === false)
        {
            return 0;
        }
        stream_set_timeout($socket, 5);
        $session = pack("N", mt_rand(1, 999999));
        $stand = $this->getHandShake("", "encode",$session);
        if (@fwrite($socket, $stand) === false) {
          return 0;
        }
        $response = (string) @fread($socket, 65535);
        if ($response === "") {
            if (@fwrite($socket, $this->uncodePing($session))) {
                $search =$this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
            }
        }
        if (isset($search) === false) {
            if (@fwrite($socket, $this->getStatics($this->getHandShake($response, "decode",$session), "encode",true,$session)) === false) {
                return 0;
            }
            $search = $this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
        }
        if($search['maxplayers'] !== null)
        {
            return $search['maxplayers'];
        } else {
            return 20;
        }
        socket_close($socket);
    } else {
        return 0;
    }
    return 0;
}

public function getName(): string
{
    if($this->isValidIP($this->ip) && $this->isValidPort($this->port))
    {
        $dns = gethostbyname($this->ip);
        $socket    = fsockopen("udp://" . $dns, $this->port, $error_id, $error, 30);
        if($error_id || $socket === false)
        {
            return 'Minecraft Bedrock Server';
        }
        stream_set_timeout($socket, 5);
        $session = pack("N", mt_rand(1, 999999));
        $stand = $this->getHandShake("", "encode",$session);
        if (@fwrite($socket, $stand) === false) {
          return 'Minecraft Bedrock Server';
        }
        $response = (string) @fread($socket, 65535);
        if ($response === "") {
            if (@fwrite($socket, $this->uncodePing($session))) {
                $search =$this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
            }
        }
        if (isset($search) === false) {
            if (@fwrite($socket, $this->getStatics($this->getHandShake($response, "decode",$session), "encode",true,$session)) === false) {
                return 'Minecraft Bedrock Server';
            }
            $search = $this->getStatics((string) @fread($socket, 65535), "decode",true,$session);
        }
        if($search['hostname'] !== null)
        {
            return $search['hostname'];
        } else {
            return 'Minecraft Bedrock Server';
        }
        socket_close($socket);
    } else {
        return 'Minecraft Bedrock Server';
    }
    return 'Minecraft Bedrock Server';   
}

public function isOnline(): bool
{
    if($this->isValidIP($this->ip) && $this->isValidPort($this->port))
    {
        $dns = gethostbyname($this->ip);
        $socket    = fsockopen("udp://" . $dns, $this->port, $error_id, $error, 30);
        if($error_id || $socket === false)
        {
            return false;
        } else {
            return true;
        }
    }
    return false;
}


}

?>