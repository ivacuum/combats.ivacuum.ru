<?php
/***************************************************************************
 *							    zayavka.php								   *
 *						  ----------------------						   *
 *   begin				: Sunday, January 23, 2005						   *
 *   copyright			: © 2005 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: zayavka.php, v 1.00 2005/11/18 19:24:00 V@cuum Exp $			   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);
$user->obtain_status($userdata);

//
// Проверки
//
if( !$userdata['session_logged_in'] || $userdata['user_blocked'] )
{
	// Чужакам вход воспрещён
	redirect($root_path . 'return.php');
}
elseif( $userdata['user_battle_id'] > 0 )
{
	// Если персонаж в бое, то он и должен там быть
	redirect($root_path . 'battle.php');
}
// ----------

//
// Определяем необходимые переменные
//
$all		= request_var('all', '');
$close		= request_var('close', '');
$confirm2	= request_var('confirm2', '');
$day		= '';
$filter		= request_var('filter', $userdata['user_login']);
$gocombat	= request_var('gocombat', '');
$level		= request_var('level', '');
$logs		= request_var('logs', '');
$message	= '';
$month		= '';
$n			= 0;
$open		= request_var('open', '');
$tklogs		= request_var('tklogs', '');
$year		= '';
$warning	= '';
// ----------

if( $confirm2 && $gocombat > 0 )
{
	//
	// Начинаем бой
	//
	if( !preg_match('#^[0-9]+$#', $gocombat) )
	{
		$message = 'Выбранная заявка не существует';
	}
	else
	{
		$enemy_id = substr($gocombat, 10);

		include($root_path . 'includes/user_zayavka.php');

		$user->start_battle($enemy_id, 'fiz');
	}
	// ----------
}

//
// Начинаем восстановление и обновляем HP и ману
//
$user->start_regen($userdata);
$user->update_hp($userdata);
$user->update_mana($userdata);
// ----------

switch( $level )
{
	case 'begin':
		// ---------------
		// Бои новичков
		//
		$warning = ( $user->check_battle_room() ) ? '' : 'В этой комнате нельзя подавать заявки';
		$warning = ( $userdata['user_level'] > 0 ) ? 'Вы уже выросли из этих ползунков ;)' : 'Эти бои на данный момент недоступны.';
		break;
		//
		// ---------------
	case 'fiz': 
		// ---------------
		// Физические бои
		//
		$warning = ( $user->check_battle_room() || $userdata['user_access_level'] == ADMIN ) ? '' : 'В этой комнате нельзя подавать заявки';

		if( !$warning )
		{
			//
			// Подача заявки
			//
			if( $open == 'Подать заявку' )
			{
				$timeout = intval($_POST['timeout']);
				$k = intval($_POST['k']);

				if( !$userdata['user_have_zayavka'] )
				{
					//
					// Добавляем заявку
					//
					$sql = "INSERT INTO " . ZAYAVKA_TABLE . " " . $db->sql_build_array('INSERT', array(
						'zayavka_id'			=> time(),
						'zayavka_type'			=> $level,
						'zayavka_kmp'			=> $k,
						'zayavka_timeout'		=> $timeout,
						'zayavka_user_id'		=> $userdata['user_id'],
						'zayavka_user_align'	=> $userdata['user_align'],
						'zayavka_user_login'	=> $userdata['user_login'],
						'zayavka_user_level'	=> $userdata['user_level'],
						'zayavka_user_klan'		=> $userdata['user_klan'],
						'zayavka_confirm_id'	=> 0));
					if( !$db->sql_query($sql) )
					{
						site_message('Не могу подать заявку...', '', __LINE__, __FILE__, $sql);
					}
					// ----------

					//
					// Обновляем данные персонажа
					//
					$sql = "UPDATE " . USERS_TABLE . " SET user_have_zayavka = 1 WHERE `user_id` = " . $userdata['user_id'];
					if( !$db->sql_query($sql) )
					{
						site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
					}
					// ----------

					$userdata['user_have_zayavka'] = 1;
					$message = '<br><font color="red"><b>Заявка на бой подана</b></font>';
				}
				else
				{
					$message = '<br><font color="red"><b>Вы уже подали заявку на бой</b></font>';
				}
			}
			elseif( $close == 'Отозвать свою заявку' )
			{
				$sql = "UPDATE " . USERS_TABLE . " SET user_have_zayavka = 0 WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$sql = "DELETE FROM " . ZAYAVKA_TABLE . " WHERE `zayavka_user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные заявок...', '', __LINE__, __FILE__, $sql);
				}

				$userdata['user_have_zayavka'] = 0;
				$message = '<br><font color="red"><b>Вы отозвали свою заявку</b></font>';
			}
			// ----------

			//
			// Получаем данные заявок
			//
			/*
			$sql = "SELECT * FROM " . ZAYAVKA_TABLE . " WHERE `zayavka_type` = 'fiz'";
			if( !($result = $db->sql_query($sql)) )
			{
				site_message('Не могу получить данные заявок...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
			*/

			$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_id` <> " . $userdata['user_id'] . " AND `user_id` <> " . $userdata['user_last_enemy'] . " AND `user_battle_id` = 0 AND `user_bot` = 1 AND `user_town` = '" . $userdata['user_town'] . "' AND `user_start_regen` < " . ( time() - 600 ) . " AND `user_level` = " . $userdata['user_level'];
			if( !$result = $db->sql_query($sql) )
			{
				site_message('Не могу получить данные заявок...', '', __LINE__, __FILE__, $sql);
			}

			$template->assign_block_vars('fiz', array());

			$n = 0;

			while( $row = $db->sql_fetchrow($result) )
			{
				//
				// Заполнение массивов
				//
				/*
				$zayavka['id'][$n] = $row['zayavka_id'];

				$zayavka['time'][$n] = gmdate('H:i', $zayavka['id'][$n] + (3600 * 3));
				$zayavka['timeout'][$n] = $row['zayavka_timeout'];
				$zayavka['user_id'][$n] = $row['zayavka_user_id'];
				$zayavka['user_align'][$n] = $row['zayavka_user_align'];
				$zayavka['user_login'][$n] = $row['zayavka_user_login'];
				$zayavka['user_level'][$n] = $row['zayavka_user_level'];
				$zayavka['user_klan'][$n] = $row['zayavka_user_klan'];
				*/

				$zayavka['id'][$n] = time() . $row['user_id'];
				$zayavka['kmp'][$n] = 1;
				$zayavka['time'][$n] = date('H:i', time());
				$zayavka['timeout'][$n] = mt_rand(3, 10);
				$zayavka['user_id'][$n] = $row['user_id'];
				$zayavka['user_align'][$n] = $row['user_align'];
				$zayavka['user_login'][$n] = $row['user_login'];
				$zayavka['user_level'][$n] = $row['user_level'];
				$zayavka['user_klan'][$n] = $row['user_klan'];

				switch( $zayavka['kmp'][$n] )
				{
					case 1: $zayavka['kmp_desc'][$n] = 'Физический бой'; break;
					case 4: $zayavka['kmp_desc'][$n] = 'Кулачный бой'; break;
					case 6: $zayavka['kmp_desc'][$n] = 'Бой без правил'; break;
				}

				$n++;
				// ----------
			}

			for( $i = 0; $i < $n; $i++ )
			{
				$template->assign_block_vars('list', array(
					'ID'			=> $zayavka['id'][$i],
					'KMP'			=> $zayavka['kmp'][$i],
					'KMP_DESC'		=> $zayavka['kmp_desc'][$i],
					'TIME'			=> $zayavka['time'][$i],
					'TIMEOUT'		=> $zayavka['timeout'][$i],
					'USER_ID'		=> $zayavka['user_id'][$i],
					'USER_ALIGN'	=> $zayavka['user_align'][$i],
					'USER_LOGIN'	=> $zayavka['user_login'][$i],
					'USER_LEVEL'	=> $zayavka['user_level'][$i],
					'USER_KLAN'		=> $zayavka['user_klan'][$i])
				);
			}
		}

		break;
		//
		// ---------------
	case 'dgv':
		// ---------------
		// Договорные бои
		//
		$warning = ( $user->check_battle_room() ) ? '' : 'В этой комнате нельзя подавать заявки';
		$warning = ( $userdata['user_level'] < 4 ) ? 'Договорные бои доступны только с четвертого уровня' : 'Договорные бои на данный момент недоступны';
		break;
		//
		// ---------------
	case 'group':
		// ---------------
		// Групповые бои
		//
		$warning = ( $user->check_battle_room() ) ? '' : 'В этой комнате нельзя подавать заявки';
		$warning = ( $userdata['user_level'] < 2 ) ? 'Групповые бои доступны только со второго уровня' : 'Подача заявок на групповые бои в процессе разработки';
		break;
		//
		// ---------------
	case 'haos':
		// ---------------
		// Хаотичные бои
		//
		$warning = ( $user->check_battle_room() ) ? '' : 'В этой комнате нельзя подавать заявки';
		$warning = ( $userdata['user_level'] < 2 ) ? 'Хаотичные бои доступны только со второго уровня' : 'Хаотичные бои на данный момент недоступны';
		break;
		//
		// ---------------
}

if( $tklogs )
{
	//
	// Получаем данные боев
	//
	$sql = "SELECT l.*, u.* FROM " . LOGS_TABLE . " l, " . LOGS_USERS_TABLE . " u WHERE l.log_time_end = 0 AND l.log_id = u.log_id ORDER BY l.log_time_start DESC";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные текущих боев...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	$log['team1'] = array();
	$log['team2'] = array();
	$n = 0;

	while( $row = $db->sql_fetchrow($result) )
	{
		if( !isset($log['team' . $row['log_user_team']][$row['log_id']]) )
		{
			$log['team' . $row['log_user_team']][$row['log_id']] = '';
		}

		// Участвующие в боях персонажи
		$log['team' . $row['log_user_team']][$row['log_id']] .= '<script>drwfl("' . $row['log_user_login'] . '", ' . $row['log_user_id'] . ', ' . $row['log_user_level'] . ', ' . $row['log_user_align'] . ',"' . $row['log_user_klan'] . '");</script>, ';

		if( $n == 0 || $log_id[$n - 1] != $row['log_id'] )
		{
			switch( $row['log_battle_type'] )
			{
				// Описание типа поединка
				case 1: $log_fight_desc[$n] = 'Физический бой'; break;
				case 2: $log_fight_desc[$n] = 'Групповой бой'; break;
				case 4: $log_fight_desc[$n] = 'Кулачный бой'; break;
				case 6: $log_fight_desc[$n] = 'Бой без правил'; break;
			}

			// Прочее
			$log_id[$n] = $row['log_id'];
			$log_start_time[$n] = gmdate('d.m.y H:i', $row['log_time_start'] + (3600 * $config['timezone']));

			$n++;
		}
	}

	for( $i = 0; $i < $n; $i++ )
	{
		$template->assign_block_vars('tklogs', array(
			'FIGHT_DESC'	=> $log_fight_desc[$i],
			'N'						=> $i + 1,
			'START_TIME'		=> $log_start_time[$i],
			'TEAM1'				=> substr($log['team1'][$log_id[$i]], 0, -2),
			'TEAM2'				=> substr($log['team2'][$log_id[$i]], 0, -2),
			
			'U_LOG'				=> append_sid($root_path . 'logs.php?log=' . $log_id[$i]))
		);
	}
}
elseif( $logs )
{
	if( !preg_match('#^[0-9]{1,2}+\.[0-9]{1,2}+\.[0-9]{2}+$#', $logs) )
	{
		// Устанавливаем текущую дату, если число неверное
		$logs = date('d.m.y');
		$date = sscanf($logs, '%d.%d.%s', $day, $month, $year);
		$start_date = mktime(0, 0, 0);
		$end_date = mktime(24, 0, 0);
	}
	else
	{
		// Преобразовываем данные
		$date = sscanf($logs, '%d.%d.%s', $day, $month, $year);
		$start_date = mktime(0, 0, 0, $month, $day, $year);
		$end_date = mktime(24, 0, 0, $month, $day, $year);
	}

//	print 'Старт: ' . date('D d M, Y H:i', $start_date) . ', окончание: ' . date('D d M, Y H:i', $end_date) . '<br />';

	//
	// Получаем данные боев
	//
	$sql = "SELECT l.*, u.* FROM " . LOGS_TABLE . " l, " . LOGS_USERS_TABLE . " u WHERE l.log_id = u.log_id AND l.log_time_start >= " . $start_date . " AND l.log_time_start < " . $end_date . " ORDER BY l.log_time_start ASC";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные завершенных боев...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	// Час - минуты - секунды - месяц - день - год - часовой пояс
//	$a = mktime(24, 0, 0, 11, 18, 05);
//	print date('D d M, Y H:i', $a);

	$log['fighters'] = array();
	$log['team1'] = array();
	$log['team2'] = array();
	$n = 0;

	while( $row = $db->sql_fetchrow($result) )
	{
		if( !isset($log['fighters'][$row['log_id']]) )
		{
			$log['fighters'][$row['log_id']] = '';
		}

		if( !isset($log['team' . $row['log_user_team']][$row['log_id']]) )
		{
			$log['team' . $row['log_user_team']][$row['log_id']] = '';
		}

		// Участвующие в боях персонажи
		$log['fighters'][$row['log_id']] .= $row['log_user_login'] . ', ';
		$log['team' . $row['log_user_team']][$row['log_id']] .= '<script>drwfl("' . $row['log_user_login'] . '", ' . $row['log_user_id'] . ', ' . $row['log_user_level'] . ', ' . $row['log_user_align'] . ',"' . $row['log_user_klan'] . '");</script>, ';

		if( $n == 0 || $log_id[$n - 1] != $row['log_id'] )
		{
			switch( $row['log_battle_type'] )
			{
				// Описание типа поединка
				case 1: $log_fight_desc[$n] = 'Физический бой'; break;
				case 2: $log_fight_desc[$n] = 'Групповой бой'; break;
				case 4: $log_fight_desc[$n] = 'Кулачный бой'; break;
				case 6: $log_fight_desc[$n] = 'Бой без правил'; break;
			}

			// Прочее
			$log_id[$n] = $row['log_id'];
			$log_start_time[$n] = date('d.m.y H:i', $row['log_time_start']);
			$log_winner[$n] = $row['log_winner'];

			$n++;
		}
	}

//	print_r($log);

	$j = 1;

	for( $i = 0; $i < $n; $i++ )
	{
		$fighters = explode(', ', substr($log['fighters'][$log_id[$i]], 0, -2));

		if( in_array($filter, $fighters) )
		{
			$template->assign_block_vars('tklogs', array(
				'FIGHT_DESC'	=> $log_fight_desc[$i],
				'N'						=> $j,
				'START_TIME'		=> $log_start_time[$i],
				'TEAM1'				=> substr($log['team1'][$log_id[$i]], 0, -2) . ( ( $log_winner[$i] == 1 ) ? ' <img align="absmiddle" src="i/flag.gif" width="20" height="20" alt="Победитель">' : '' ),
				'TEAM2'				=> substr($log['team2'][$log_id[$i]], 0, -2) . ( ( $log_winner[$i] == 2 ) ? ' <img align="absmiddle" src="i/flag.gif" width="20" height="20" alt="Победитель">' : '' ),
			
				'U_LOG'				=> append_sid($root_path . 'logs.php?log=' . $log_id[$i]))
			);

			$j++;
		}
	}

	$db->sql_freeresult($result);
}

site_header();

$template->set_filenames(array(
	'body' => 'zayavka.html')
);

if( $level == '' && !$tklogs && !$logs || $warning )
{
	$template->assign_block_vars('level_is_null', array());
}
else
{
	$template->assign_block_vars('level_is_not_null', array());
}

if( $userdata['user_have_zayavka'] == 1 && $level != '' && !$warning )
{
	$template->assign_block_vars('close_zayavka', array());
}
elseif( $userdata['user_have_zayavka'] == 0 && $level != '' && !$warning )
{
	$template->assign_block_vars('open_zayavka', array());
}

if( $tklogs )
{
	$template->assign_block_vars('no_end_logs', array());
}
elseif( $logs )
{
	$template->assign_block_vars('end_logs', array());
}

$template->assign_vars(array(
	'CLASS_BEGIN'			=> ( $level == 'begin' ) ? 's' : 'm',
	'CLASS_FIZ'				=> ( $level == 'fiz' ) ? 's' : 'm',
	'CLASS_DGV'				=> ( $level == 'dgv' ) ? 's' : 'm',
	'CLASS_GROUP'			=> ( $level == 'group' ) ? 's' : 'm',
	'CLASS_HAOS'			=> ( $level == 'haos' ) ? 's' : 'm',
	'CLASS_TKLOGS'			=> ( $tklogs ) ? 's' : 'm',
	'CLASS_LOGS'			=> ( $logs ) ? 's' : 'm',
	'CURRENT_DATE'			=> date('d.m.y'),
	'CURRENT_HP'			=> $userdata['user_current_hp'],
	'DATE'					=> ( $logs ) ? $logs : date('m.d.y'),
	'DRWFL'					=> $user->drwfl($userdata),
	'FILTER'				=> $filter,
	'FILTER_ENCODE'			=> urlencode($filter),
	'HPSPEED'				=> $userdata['user_hpspeed'],
	'LEVEL'					=> $level,
	'MAX_HP'				=> $userdata['user_max_hp'],
	'MESSAGE'				=> $message,
	'PREVIOUS_DAY'			=> ( $day - 1 ) . '.' . $month . '.' . $year,
	'TOTAL_LOGS'			=> ( $n ) ? $n : 0,
	'WARNING'				=> $warning)
);

$template->pparse('body');

site_bottom();

?>