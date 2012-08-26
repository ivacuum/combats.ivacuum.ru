<?php

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');
include($root_path . 'includes/user_paladins.php');

$userdata = session_pagestart($user_ip);

//
// Чужакам вход воспрещен
//
if( !$userdata['session_logged_in'] || $userdata['user_blocked'] )
{
	redirect($root_path . 'return.php');
}
// ----------

//
// Если персонаж в бое, то он и должен там быть
//
if( $userdata['user_battle_id'] > 0 && $userdata['user_access_level'] != ADMIN )
{
	redirect($root_path . 'battle.php');
}
// ----------

//
// Определяем переменные
//
$chaos_login		= request_var('chaos_login', '');
$chaos_reason		= request_var('chaos_reason', '');
$chaos_time			= request_var('chaos_time', '');
$death_login		= request_var('death_login', '');
$death_reason		= request_var('death_reason', '');
$make_align0		= request_var('make_align0', '');
$make_align1		= request_var('make_align1', '');
$make_align1_4		= request_var('make_align1_4', '');
$make_align1_5		= request_var('make_align1_5', '');
$make_align1_6		= request_var('make_align1_6', '');
$make_align1_7		= request_var('make_align1_7', '');
$make_align1_75		= request_var('make_align1_75', '');
$make_align1_9		= request_var('make_align1_9', '');
$make_align1_91		= request_var('make_align1_91', '');
$make_align1_92		= request_var('make_align1_92', '');
$make_align1_99		= request_var('make_align1_99', '');
$make_align2_5		= request_var('make_align2_5', '');
$make_align3		= request_var('make_align3', '');
$make_align3_01		= request_var('make_align3_01', '');
$make_align3_05		= request_var('make_align3_05', '');
$make_align3_06		= request_var('make_align3_06', '');
$make_align3_07		= request_var('make_align3_07', '');
$make_align3_09		= request_var('make_align3_09', '');
$make_align3_091	= request_var('make_align3_091', '');
$make_align3_99		= request_var('make_align3_99', '');
$make_align7		= request_var('make_align7', '');
$make_align50		= request_var('make_align50', '');
$message			= '';
$silence15			= request_var('silence15', '');
$silence30			= request_var('silence30', '');
$silence60			= request_var('silence60', '');
$silence180			= request_var('silence180', '');
$silence360			= request_var('silence360', '');
$silence720			= request_var('silence720', '');
$silence1440		= request_var('silence1440', '');
$sleep_off			= request_var('sleep_off', '');
// ----------

if( $chaos_login )
{

}
elseif( $death_login )
{
	if( !$death_reason && $userdata['user_access_level'] != ADMIN )
	{
		$message = 'Не указана причина блокировки персонажа';
	}
	elseif( ( $userdata['user_align'] < '1.9' || $userdata['user_align'] > 2 ) && $userdata['user_access_level'] != ADMIN )
	{
		$message = 'Паладины могут накладывать заклятие смерти только со звания "<img alt="Паладин неба" src="i/align1.9.gif">Паладин неба"';
	}
	else
	{
		// Блокируем персонажа
		$message = $user->death($death_login, $death_reason);
	}
}
elseif( $make_align0 )
{
	$message = $user->make_align($make_align0, '0');
}
elseif( $make_align1 )
{
	$message = $user->make_align($make_align1, '1');
}
elseif( $make_align1_4 )
{
	$message = $user->make_align($make_align1_4, '1.4');
}
elseif( $make_align1_5 )
{
	$message = $user->make_align($make_align1_5, '1.5');
}
elseif( $make_align1_6 )
{
	$message = $user->make_align($make_align1_6, '1.6');
}
elseif( $make_align1_7 )
{
	$message = $user->make_align($make_align1_7, '1.7');
}
elseif( $make_align1_75 )
{
	$message = $user->make_align($make_align1_75, '1.75');
}
elseif( $make_align1_9 )
{
	$message = $user->make_align($make_align1_9, '1.9');
}
elseif( $make_align1_91 )
{
	$message = $user->make_align($make_align1_91, '1.91');
}
elseif( $make_align1_92 )
{
	$message = $user->make_align($make_align1_92, '1.92');
}
elseif( $make_align1_99 )
{
	$message = $user->make_align($make_align1_99, '1.99');
}
elseif( $make_align2_5 )
{
	$message = $user->make_align($make_align2_5, '2.5');
}
elseif( $make_align3 )
{
	$message = $user->make_align($make_align3, '3');
}
elseif( $make_align3_01 )
{
	$message = $user->make_align($make_align3_01, '3.01');
}
elseif( $make_align3_05 )
{
	$message = $user->make_align($make_align3_05, '3.05');
}
elseif( $make_align3_06 )
{
	$message = $user->make_align($make_align3_06, '3.06');
}
elseif( $make_align3_07 )
{
	$message = $user->make_align($make_align3_07, '3.07');
}
elseif( $make_align3_09 )
{
	$message = $user->make_align($make_align3_09, '3.09');
}
elseif( $make_align3_091 )
{
	$message = $user->make_align($make_align3_091, '3.091');
}
elseif( $make_align3_99 )
{
	$message = $user->make_align($make_align3_99, '3.99');
}
elseif( $make_align7 )
{
	$message = $user->make_align($make_align7, '7');
}
elseif( $make_align50 )
{
	$message = $user->make_align($make_align50, '50');
}
elseif( $silence15 )
{
	$message = $user->silence($silence15, 15);
}
elseif( $silence30 )
{
	$message = $user->silence($silence30, 30);
}
elseif( $silence60 && $userdata['user_align'] >= '1.4' && $userdata['user_align'] < 2 )
{
	$message = $user->silence($silence60, 60);
}
elseif( $silence180 && $userdata['user_align'] >= '1.4' && $userdata['user_align'] < 2 )
{
	$message = $user->silence($silence180, 180);
}
elseif( $silence360 && $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 )
{
	$message = $user->silence($silence360, 360);
}
elseif( $silence720 && $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 )
{
	$message = $user->silence($silence720, 720);
}
elseif( $silence1440 && $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 )
{
	$message = $user->silence($silence1440, 1440);
}
elseif( $sleep_off )
{
	$message = $user->sleep_off($sleep_off);
}

include($root_path . 'includes/page_header.php');

$template->set_filenames(array(
	'body'	=> 'paladins_body.html')
);

$template->assign_vars(array(
	'DRWFL'				=> $user->drwfl($userdata),
	'MESSAGE'			=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '',
	
	'SILENCE60'			=> ( $userdata['user_access_level'] == ADMIN || ( $userdata['user_align'] >= '1.4' && $userdata['user_align'] < 2 ) ) ? '&nbsp;<img alt="Наложить заклятие молчания" height="25" onclick="findlogin(\'Заклятие молчания\', \'paladins.php\', \'silence60\', \'\')" src="i/items/silence60.gif" style="cursor: hand" width="40">' : '',
	'SILENCE180'		=> ( $userdata['user_access_level'] == ADMIN || ( $userdata['user_align'] >= '1.4' && $userdata['user_align'] < 2 ) ) ? '&nbsp;<img alt="Наложить заклятие молчания" height="25" onclick="findlogin(\'Заклятие молчания\', \'paladins.php\', \'silence180\', \'\')" src="i/items/silence180.gif" style="cursor: hand" width="40">' : '',
	'SILENCE360'		=> ( $userdata['user_access_level'] == ADMIN || ( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 ) ) ? '&nbsp;<img alt="Наложить заклятие молчания" height="25" onclick="findlogin(\'Заклятие молчания\', \'paladins.php\', \'silence360\', \'\')" src="i/items/silence360.gif" style="cursor: hand" width="40">' : '',
	'SILENCE720'		=> ( $userdata['user_access_level'] == ADMIN || ( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 ) ) ? '&nbsp;<img alt="Наложить заклятие молчания" height="25" onclick="findlogin(\'Заклятие молчания\', \'paladins.php\', \'silence720\', \'\')" src="i/items/silence720.gif" style="cursor: hand" width="40">' : '',
	'SILENCE1440'		=> ( $userdata['user_access_level'] == ADMIN || ( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 ) ) ? '&nbsp;<img alt="Наложить заклятие молчания" height="25" onclick="findlogin(\'Заклятие молчания\', \'paladins.php\', \'silence1440\', \'\')" src="i/items/silence1440.gif" style="cursor: hand" width="40">' : '')
);

if( $userdata['user_access_level'] == ADMIN || $userdata['user_align'] == '1.99' )
{
	$template->assign_block_vars('align1_99', array());
}

$template->pparse('body');

include($root_path . 'includes/page_bottom.php');

?>