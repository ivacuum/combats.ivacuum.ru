<?php

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

//
// Запрет кэширования страниц
//
if( !empty($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache/2') )
{
	header ('Cache-Control: no-cache, pre-check=0, post-check=0');
}
else
{
	header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
}
header ('Expires: 0');
header ('Pragma: no-cache');
// ----------
?>