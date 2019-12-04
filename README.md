# BedrockServerAPI
Check information on some PMMP servers

# Example
```
<?php
include __DIR__.'/BedrockServerAPI.php';
$server = new BedrockServerAPI();
$server->ip = 'play.nethergames.org';
$server->port = 19132;
echo $server->getName()."<br>";
if($server->isOnline())
{
    echo "O N L I N E <br>";
} else {
    echo "O F F L I N E <br>";
}
echo $server->getPlaying().' / '.$server->getSlots();
```
