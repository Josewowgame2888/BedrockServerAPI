<?php
include __DIR__.'/BedrockServerAPI.php';
$server = new BedrockServerAPI();
$server->ip = 'play.cubecraft.net';
$server->port = 19132;
echo $server->getName()."<br>";
if($server->isOnline())
{
    echo "O N L I N E <br>";
} else {
    echo "O F F L I N E <br>";
}
echo $server->getPlaying().' / '.$server->getSlots();