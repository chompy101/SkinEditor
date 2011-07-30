<?php
$url="http://s3.amazonaws.com/MinecraftSkins/".$_GET["username"].".png";
$skin=file_get_contents($url);
header("Content-Type: image/png");
echo($skin);
?>