<?php

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

$template->set_filenames(array(
	'bottom' => 'page_bottom.html')
);

$mtime = explode(' ', microtime());
$totaltime = $mtime[0] + $mtime[1] - $starttime;

//
// Устанавливаем некоторые параметры
//
//if( $userdata['session_logged_in'] == 0 )
//{
//	$userdata['user_access_level'] = 0;
//	$userdata['user_bot'] = 0;
//}
// ----------

$debug_info = ( $userdata['user_access_level'] == ADMIN || $userdata['user_bot'] ) ? sprintf('[ Время: %.3fсек | Запросов: ' . ( $db->sql_num_queries() + 2 ) . ' ]', $totaltime) : '';
//$debug_info .= ( $config['debug_mode'] && $userdata['user_access_level'] == ADMIN ) ? '<br><br>[ http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . ' ]' : '';

$template->assign_vars(array(
	'DEBUG_INFO' => $debug_info)
);

$template->pparse('bottom');

$db->sql_close();
exit;

?>