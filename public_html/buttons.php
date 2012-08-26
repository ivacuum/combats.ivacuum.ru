<?php

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

include($root_path . 'includes/page_header.php');

$battle		= request_var('battle', '');
$ch			= request_var('ch', '');
$header		= request_var('header', '');

//
// Проверка установки куков
//
if( !isset($_COOKIE['user_login']) )
{
	redirect('return.php');
}
// ----------

if( $battle )
{
	// ---------------
	// Основной фрэйм
	//

	// Настраиваем генератор случайных чисел
	mt_srand(time() + (double)microtime() * 1000000);

	$template->set_filenames(array(
		'index' => 'buttons_battle.html')
	);

	$template->assign_vars(array(
		'U_CH_ONLINE'	=> 'ch.php?online=' . substr(mt_rand(), 0, 6),
		'U_CH_REFRESH'	=> 'ch.php?show=' . substr(mt_rand(), 0, 6))
	);

	$template->pparse('index');

	$db->sql_close();
	exit;
	//
	// ---------------
}
elseif( $header == 1 )
{
	// ---------------
	// Шапка
	//
	$sql = "SELECT user_town FROM " . USERS_TABLE . " WHERE `user_login` = '" . $_COOKIE['user_login'] . "'";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}

	$userdata = $db->sql_fetchrow($result);

	switch( $userdata['user_town'] )
	{
		case 'capital':		$images_folder = '1/capital'; $header_width = '239'; $town_name_width = '137'; break;
		case 'angels':		$images_folder = '2/angel'; $header_width = '205'; $town_name_width = '120'; break;
		case 'demons':		$images_folder = '3/demons'; $header_width = '236'; $town_name_width = '122'; break;
		case 'devils':		$images_folder = '4/devils'; $header_width = '227'; $town_name_width = '106'; break;
		case 'sun':			$images_folder = '5/sun'; $header_width = '259'; $town_name_width = '100'; break;
		case 'emeralds':	$images_folder = '6/emeralds'; $header_width = '214'; $town_name_width = '155'; break;
		case 'sand':		$images_folder = '7/sand'; $header_width = '214'; $town_name_width = '111'; break;
		case 'moon':		$images_folder = '8/moon'; $header_width = '251'; $town_name_width = '110'; break;
		case 'newcapital':	$images_folder = '10/newcapital'; $header_width = '214'; $town_name_width = '128'; break;
	}

	$template->set_filenames(array(
		'header' => 'buttons_header.html')
	);

	$template->assign_vars(array(
		'HEADER_WIDTH'		=> $header_width,
		'I_FOLDER'			=> $images_folder,
		'TOWN_NAME_WIDTH'	=> $town_name_width)
	);

	$template->pparse('header');

	$db->sql_close();
	exit;
	//
	// ---------------
}
elseif( $ch == 1 )
{
	// ---------------
	// Чат
	//
	$template->set_filenames(array(
		'ch' => 'buttons_ch.html')
	);

	$template->assign_vars(array(
		'LOGIN'	=> $_COOKIE['user_login'])
	);

	$template->pparse('ch');

	$db->sql_close();
	exit;
	// ---------------
}

$userdata = session_pagestart($user_ip);

//
// Считаем количество выводимых кнопок
//
$buttons = 2;
$buttons += ( $userdata['user_level'] >= 4 ) ? 1 : 0;										// Передачи
$buttons += ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 1 : 0;		// Паладинский крест
//$buttons += ( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 ) ? 1 : 0;	// Хаос / блок
$buttons += ( $userdata['user_align'] >= 3 && $userdata['user_align'] < 4 ) ? 1 : 0;		// Способности темных
$buttons += ( $userdata['user_align'] == 50 ) ? 1 : 0;										// Способности дилера
$buttons += ( $userdata['user_klan'] ) ? 1 : 0;												// Клановый значок
$buttons += ( $userdata['user_access_level'] == ADMIN || $userdata['user_vip'] ) ? 1 : 0;	// VIP-значок
// ----------

//
// Кнопки
//
$btn = '';
$btn .= ( $userdata['user_level'] >= 4 ) ? '<img src="http://static.ivacuum.ru/i/a___trf.gif" width="30" height="30" alt="Передать предметы/кредиты" style="cursor: hand" onclick="top.cht(\'main.php?setkredit=1\')">' : '';
$btn .= ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? '<img src="http://static.ivacuum.ru/i/a___pal.gif" width="30" height="30" alt="Паладины" style="cursor: hand" onclick="top.cht(\'paladins.php\')">' : '';
//$btn .= ( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 ) ? '<img src="http://static.ivacuum.ru/i/a___haos.gif" width="30" height="30" alt="Хаос / блок" style="cursor: hand">' : '';
$btn .= ( $userdata['user_align'] >= 3 && $userdata['user_align'] < 4 ) ? '<img src="http://static.ivacuum.ru/i/a___drk.gif" width="30" height="30" alt="Темные" style="cursor: hand" onclick="top.cht(\'dark.php\')">' : '';
$btn .= ( $userdata['user_align'] == 50 ) ? '<img src="http://static.ivacuum.ru/i/a___dlr.gif" width="30" height="30" alt="Дилер" style="cursor: hand">' : '';
$btn .= '<img src="http://static.ivacuum.ru/i/a___friend3.gif" width="30" height="30" alt="Друзья" style="cursor:hand" onclick="top.cht(\'main.php?friends=1\')" />';
$btn .= ( $userdata['user_klan'] ) ? '<img src="http://static.ivacuum.ru/i/a___kln.gif" width="30" height="30" alt="Клан" style="cursor: hand" onclick="top.cht(\'clans.php\')">' : '';
$btn .= ( $userdata['user_access_level'] == ADMIN || $userdata['user_vip'] ) ? '<img src="http://static.ivacuum.ru/i/a___vip.gif" width="30" height="30" alt="VIP-клуб" style="cursor: hand" onclick="top.cht(\'vip.php\')">' : '';
// ----------

$template->set_filenames(array(
	'buttons' => 'buttons_body.html')
);

$template->assign_vars(array(
	'BTN'		=> $btn,
	'BUTTONS'	=> $buttons,
	
	'F_VALUE'	=> 'http://static.ivacuum.ru/i/flash/clock.swf?hours=' . date("H") . '&minutes=' . date("i") . '&sec=' . date("s"))
);

$template->pparse('buttons');

$db->sql_close();
exit;

?>