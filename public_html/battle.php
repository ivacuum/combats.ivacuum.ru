<?php
/***************************************************************************
 *								battle.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: battle.php, v 1.00 2005/11/20 14:25:00 V@cuum Exp $			   *
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
// Проверяем лог боя...
//
if( $userdata['user_battle_id'] <= 0 )
{
	redirect($root_path . 'main.php');
}
// ----------

//
// Загружаем необходимые функции
//
if( $userdata['user_level'] >= 5 )
{
	include_once($root_path . 'includes/special_move.php');
}

include_once($root_path . 'includes/user_battle.php');
// ----------

//
// Определяем переменные
//
$message		= '';
$n				= request_var('n', '');
$param			= request_var('param', '');
$special		= request_var('special', '');
$use			= request_var('use', '');
// ----------

//
// Получаем время таймаута
//
$sql = "SELECT log_time_end, log_timeout FROM " . LOGS_TABLE . " WHERE `log_id` = " . $userdata['user_battle_id'];
if( !$result = $db->sql_query($sql) )
{
	site_message('Не могу получить данные боя...', '', __LINE__, __FILE__, $sql);
}

$row = $db->sql_fetchrow($result);

$log_time_end = $row['log_time_end'];
$log_timeout = $row['log_timeout'];
// ----------

// Спецприемы
$special_selected = ( $userdata['user_level'] >= 5 ) ? explode(',', $userdata['user_special_selected']) : '';
$userdata['specials'] = explode(',', $userdata['user_special']);

//
// Спецприемы
//
if( $special )
{
	if( $log_time_end )
	{
		$message = 'Бой закончен... не время для спец-приёмов';
	}
	elseif( $userdata['user_current_hp'] <= 0 )
	{
		$message = 'Вы не в силах выполнить данный спец-приём';
	}
	elseif( $userdata['user_level'] < 5 )
	{
		$message = 'Спец-приёмы можно использовать только с пятого уровня';
	}
	elseif( in_array($special, $userdata['specials']) )
	{
		$message = 'Данный спец-приём уже выбран';
	}
	elseif( $special_move->is_exist($special) )
	{
		$special_requirements = $special_move->requirements($special, 'array');
		$user->use_special($userdata, $special, $special_requirements[0], $special_requirements[1], $special_requirements[2], $special_requirements[3], $special_requirements[4]);
		$message = $special_move->description($special, $userdata['user_level']);
	}
	else
	{
		$message = 'Данный спец-приём пока нельзя использовать';
	}
}
// ----------

//
// Использование заклятий
//
if( $use && !$log_time_end )
{
	if( !preg_match('#^[0-9]+$#', $n) )
	{
		$message = 'Неверно введены данные';
	}

	if( $n > 0 && !$message )
	{
		//
		// Получаем данные вещи
		//
		$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $n;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные используемой вещи...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		//
		// Проверки
		//
		if( !$row )
		{
			$message = 'Вещь не найдена';
		}
		// ----------

		if( $row['item_img'] == $use && !$message )
		{
			include($root_path . 'includes/magic.php');

			switch( $use )
			{
				// Нападалки
				case 'attack':
				case 'attackb':
					$row2 = get_userdata('', $param);

					// Ломаем свиток
					$magic->scroll_damage($row, $userdata);

					// Вероятность срабатывания
					$message = $magic->chance_to_use($userdata, $row);

					if( !$message )
					{
						// Нападаем
						$message = $magic->attack($userdata, $row2, $use);
					}

					break;
				case 'cureHP15':
				case 'cureHP30':
				case 'cureHP45':
				case 'cureHP60':
				case 'cureHP600':
					$row2 = get_userdata('', $param);

					// Ломаем свиток
					$magic->scroll_damage($row, $userdata);

					// Вероятность срабатывания
					$message = $magic->chance_to_use($userdata, $row);

					if( !$message )
					{
						// Лечим
						$message = $magic->cureHP($userdata, $row2, substr($use, 6));
					}

					break;
			}
		}
	}
}
// ----------

if( !$log_time_end )
{
	//
	// Получаем данные персонажей, находящихся в бое
	//
	$sql = "SELECT u.user_battle_team, u.user_bot, u.user_increase_experience, u.user_items_cost, u.user_room, u.user_align, u.user_current_hp, u.user_id, u.user_login, u.user_max_hp, u.user_hit_hp, u.user_level FROM " . USERS_TABLE . " u, " . LOGS_USERS_TABLE . " l WHERE u.user_id = l.log_user_id AND l.log_id = " . $userdata['user_battle_id'] . " ORDER BY l.log_user_uid ASC";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	$j = 0;
	$n = 0;

	//
	// Определяем переменные
	//
	$team[1]['hp'] = 0;
	$team[2]['hp'] = 0;
	$team[1]['items_cost'] = 0;
	$team[2]['items_cost'] = 0;
	$team[1]['level'] = 0;
	$team[2]['level'] = 0;
	// ----------

	while( $row = $db->sql_fetchrow($result) )
	{
		if( $row['user_battle_team'] == $userdata['user_battle_team'] )
		{
			// Остальные
			$team[$row['user_battle_team']]['user_align'][$n] = $row['user_align'];
			$team[$row['user_battle_team']]['user_current_hp'][$n] = intval($row['user_current_hp']);
			$team[$row['user_battle_team']]['user_hit_hp'][$n] = $row['user_hit_hp'];
			$team[$row['user_battle_team']]['user_id'][$n] = $row['user_id'];
			$team[$row['user_battle_team']]['user_increase_experience'][$n] = $row['user_increase_experience'];
			$team[$row['user_battle_team']]['user_items_cost'][$n] = $row['user_items_cost'];
			$team[$row['user_battle_team']]['user_level'][$n] = $row['user_level'];
			$team[$row['user_battle_team']]['user_login'][$n] = $row['user_login'];
			$team[$row['user_battle_team']]['user_max_hp'][$n] = $row['user_max_hp'];
			$team[$row['user_battle_team']][$n]['user_room'] = $row['user_room'];

			$n++;
		}
		else
		{
			if( $row['user_id'] == $userdata['user_last_enemy'] )
			{
				$enemy_id = $row['user_id'];
			}

			// Противники
			$team[$row['user_battle_team']]['user_align'][$j] = $row['user_align'];
			$team[$row['user_battle_team']]['user_bot'][$j] = $row['user_bot'];
			$team[$row['user_battle_team']]['user_current_hp'][$j] = intval($row['user_current_hp']);
			$team[$row['user_battle_team']]['user_hit_hp'][$j] = $row['user_hit_hp'];
			$team[$row['user_battle_team']]['user_id'][$j] = $row['user_id'];
			$team[$row['user_battle_team']]['user_increase_experience'][$n] = $row['user_increase_experience'];
			$team[$row['user_battle_team']]['user_items_cost'][$j] = $row['user_items_cost'];
			$team[$row['user_battle_team']]['user_level'][$j] = $row['user_level'];
			$team[$row['user_battle_team']]['user_login'][$j] = $row['user_login'];
			$team[$row['user_battle_team']]['user_max_hp'][$j] = $row['user_max_hp'];
			$team[$row['user_battle_team']][$j]['user_room'] = $row['user_room'];

			$j++;
		}

		$team[$row['user_battle_team']]['hp'] += ( $row['user_current_hp'] > 0 ) ? $row['user_current_hp'] : 0;
		$team[$row['user_battle_team']]['items_cost'] += $row['user_items_cost'];
		$team[$row['user_battle_team']]['level'] += $row['user_level'];
	}

	$enemy_team_id = ( $userdata['user_battle_team'] == 1 ) ? 2 : 1;

	// Средний уровень
	$team[$userdata['user_battle_team']]['level'] = round($team[$userdata['user_battle_team']]['level'] / $n);
	$team[$enemy_team_id]['level'] = round($team[$enemy_team_id]['level'] / $j);

	$pl2_userdata = get_userdata($enemy_id, '');
	$pl2_userdata['specials'] = explode(',', $pl2_userdata['user_special']);
	$user->obtain_status($pl2_userdata);
}
// ----------

//
// Выход из боя
//
if( isset($_POST['gameover']) || ( $config['fast_game'] && isset($_GET['gameover']) ) )
{
	//
	// Сбрасываем себе необходимые параметры
	//
	$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
		'user_battle_id'				=> 0,
		'user_battle_team'				=> 0,
		'user_count_block'				=> 0,
		'user_count_counterblow'		=> 0,
		'user_count_critical_hit'		=> 0,
		'user_count_hit'				=> 0,
		'user_count_parry'				=> 0,
		'user_gain_exp'					=> 0,
		'user_special'					=> NULL,
		'user_hit_hp'					=> 0)) . " WHERE `user_id` = " . $userdata['user_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные пользователя...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	//
	// Сбрасываем необходимые параметры ботам
	//
	$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
		'user_battle_id'				=> 0,
		'user_battle_team'				=> 0,
		'user_count_block'				=> 0,
		'user_count_counterblow'		=> 0,
		'user_count_critical_hit'		=> 0,
		'user_count_hit'				=> 0,
		'user_count_parry'				=> 0,
		'user_gain_exp'					=> 0,
		'user_special'					=> NULL,
		'user_hit_hp'					=> 0)) . " WHERE `user_bot` = 1 AND `user_battle_id` = " . $userdata['user_battle_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные пользователя...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	// Выходим из боя
	redirect($root_path . 'main.php');
}
// ----------

$fight = ( !$log_time_end ) ? true : false;
$death = ( $userdata['user_current_hp'] <= 0 ) ? true : false;
$win = false;
$loss = false;
$draw = false;

if( $fight )
{
	$win = ( $team[$userdata['user_battle_team']]['hp'] > 0 && $team[$enemy_team_id]['hp'] <= 0 ) ? true : false;
	$draw = ( $team[$userdata['user_battle_team']]['hp'] <= 0 && $team[$enemy_team_id]['hp'] <= 0 ) ? true : false;
	$loss = ( $team[$userdata['user_battle_team']]['hp'] <= 0 && $team[$enemy_team_id]['hp'] > 0 ) ? true : false;

	$fight = ( $win || $draw || $loss ) ? false : true;
}

//
// Получаем данные всех вещей одетых на персонажа
//
if( $userdata['user_w1'] > 0 || $userdata['user_w2'] > 0 || $userdata['user_w3'] > 0 || $userdata['user_w4'] > 0 || $userdata['user_w5'] > 0 || $userdata['user_w6'] > 0 || $userdata['user_w7'] > 0 || $userdata['user_w8'] > 0 || $userdata['user_w9'] > 0 || $userdata['user_w10'] > 0 || $userdata['user_w11'] > 0 || $userdata['user_w12'] > 0 || $userdata['user_w13'] > 0 || $userdata['user_w14'] > 0 || $userdata['user_w15'] > 0 || $userdata['user_w400'] > 0 )
{
	$items = $user->get_equip_items($userdata, false, 'battle');

	if( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w3'] > 0 && $userdata['user_w10'] > 0 && $items['item_type'][10] != 'shield' )
	{
		$userdata['user_attacks'] += 1;
	}
}
else
{
	$items = array();
}
// ----------

//
// Получаем данные всех вещей одетых на второго персонажа
//
if( $fight && ( $pl2_userdata['user_w1'] > 0 || $pl2_userdata['user_w2'] > 0 || $pl2_userdata['user_w3'] > 0 || $pl2_userdata['user_w4'] > 0 || $pl2_userdata['user_w5'] > 0 || $pl2_userdata['user_w6'] > 0 || $pl2_userdata['user_w7'] > 0 || $pl2_userdata['user_w8'] > 0 || $pl2_userdata['user_w9'] > 0 || $pl2_userdata['user_w10'] > 0 || $pl2_userdata['user_w11'] > 0 || $pl2_userdata['user_w12'] > 0 || $pl2_userdata['user_w13'] > 0 || $pl2_userdata['user_w14'] > 0 || $pl2_userdata['user_w15'] > 0 || $pl2_userdata['user_w400'] > 0 ) )
{
	$pl2_items = $user->get_equip_items($pl2_userdata, false, 'battle');

	if( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w3'] > 0 && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] != 'shield' )
	{
		$pl2_userdata['user_attacks'] += 1;
	}
}
else
{
	$pl2_items = array();
}
// ----------

//
// Если произведен удар
//
if( ( isset($_POST['go']) || ( $config['fast_game'] && isset($_GET['go']) ) ) && $fight && !$death && $pl2_userdata['user_current_hp'] > 0 && !$log_time_end )
{
	include($root_path . 'includes/battle.php');
}
// ----------

if( $fight )
{
	$team[1]['hp'] = 0;
	$team[2]['hp'] = 0;
	$team[1]['list'] = '';
	$team[2]['list'] = '';
	$team[1]['private_list'] = '';
	$team[2]['private_list'] = '';

	for( $i = 0; $i < count($team[$userdata['user_battle_team']]['user_login']); $i++ )
	{
		//
		// Первая команда
		//
		$team[$userdata['user_battle_team']]['user_current_hp'][$i] = ( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] ) ? intval($userdata['user_current_hp']) : $team[$userdata['user_battle_team']]['user_current_hp'][$i];

		if( $team[$userdata['user_battle_team']]['user_current_hp'][$i] > 0 )
		{
			$team[$userdata['user_battle_team']]['list'] .= '<span class="B' . $userdata['user_battle_team'] . '">' . $team[$userdata['user_battle_team']]['user_login'][$i] . '</span> [' . $team[$userdata['user_battle_team']]['user_current_hp'][$i] . '/' . $team[$userdata['user_battle_team']]['user_max_hp'][$i] . '], ';
			$team[$userdata['user_battle_team']]['private_list'] .= 'private [' . $team[$userdata['user_battle_team']]['user_login'][$i] . '] ';

			$team[$userdata['user_battle_team']]['hp'] += $team[$userdata['user_battle_team']]['user_current_hp'][$i];
		}
		// ----------
	}

	for( $i = 0; $i < count($team[$enemy_team_id]['user_login']); $i++ )
	{
		//
		// Вторая команда
		//
		$team[$enemy_team_id]['user_current_hp'][$i] = ( $pl2_userdata['user_id'] == $team[$enemy_team_id]['user_id'][$i] ) ? intval($pl2_userdata['user_current_hp']) : $team[$enemy_team_id]['user_current_hp'][$i];

		if( $team[$enemy_team_id]['user_current_hp'][$i] > 0 )
		{
			$team[$enemy_team_id]['list'] .= '<span class="B' . $enemy_team_id . '">' . $team[$enemy_team_id]['user_login'][$i] . '</span> [' . $team[$enemy_team_id]['user_current_hp'][$i] . '/' . $team[$enemy_team_id]['user_max_hp'][$i] . '], ';
			$team[$enemy_team_id]['private_list'] .= 'private [' . $team[$enemy_team_id]['user_login'][$i] . '] ';

			$team[$enemy_team_id]['hp'] += $team[$enemy_team_id]['user_current_hp'][$i];
		}
		// ----------
	}
}

$death = ( $userdata['user_current_hp'] <= 0 ) ? true : false;

if( $fight )
{
	$win = ( $team[$userdata['user_battle_team']]['hp'] > 0 && $team[$enemy_team_id]['hp'] <= 0 ) ? true : false;
	$draw = ( $team[$userdata['user_battle_team']]['hp'] <= 0 && $team[$enemy_team_id]['hp'] <= 0 ) ? true : false;
	$loss = ( $team[$userdata['user_battle_team']]['hp'] <= 0 && $team[$enemy_team_id]['hp'] > 0 ) ? true : false;

	$fight = ( $win || $draw || $loss ) ? false : true;
}

if( $fight && !$death && $pl2_userdata['user_current_hp'] <= 0 )
{
	//
	// Получаем данные нового противника
	//
	$sql = "SELECT user_id FROM " . USERS_TABLE . " WHERE user_battle_id = " . $userdata['user_battle_id'] . " AND user_battle_team = " . $enemy_team_id . " AND user_current_hp > 0";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные противника...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);
	// ----------

	$row['user_id'] = ( empty($row['user_id']) ) ? 0 : $row['user_id'];

	//
	// Ставим нового противника
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_last_enemy = " . $row['user_id'] . " WHERE `user_id` = " . $userdata['user_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}

	$userdata['user_last_enemy'] = $enemy_id;
	$pl2_userdata = get_userdata($row['user_id'], '');
	$pl2_items = $user->get_equip_items($pl2_userdata);
	$user->obtain_status($pl2_userdata);
	// ----------
}

// ---------------
// Бой закончен. Определяем победителя
//
if( $win && !$log_time_end )
{
	// Определяем переменные
	$team[1]['logins'] = '';
	$team[2]['logins'] = '';

	for( $i = 0; $i < count($team[$userdata['user_battle_team']]['user_login']); $i++ )
	{
		// Имена бойцов победившей команды
		$team[$userdata['user_battle_team']]['logins'] .= $team[$userdata['user_battle_team']]['user_login'][$i] . ', ';

		// Команда-победитель
		$winner_team = 1;

		if( $team[$userdata['user_battle_team']]['user_id'][$i] == $userdata['user_id'] )
		{
			// Обновляем набитый урон для игрока
			$team[$userdata['user_battle_team']]['user_hit_hp'][$i] = $userdata['user_hit_hp'];
		}

		// Раздаем опыт
		$userdata['gain_exp'] = $user->get_gained_experience($userdata['user_battle_team'], $team[$userdata['user_battle_team']]['user_hit_hp'][$i], $team[$userdata['user_battle_team']]['user_items_cost'][$i], $team[$userdata['user_battle_team']]['user_level'][$i], $team);

		$sql = "UPDATE " . USERS_TABLE . " SET user_gain_exp = " . $userdata['gain_exp'] . ", user_exp = (user_exp + " . $userdata['gain_exp'] . "), user_wins = (user_wins + 1) WHERE `user_id` = " . $team[$userdata['user_battle_team']]['user_id'][$i];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
		{
			// Системное сообщение о получении опыта
			$userdata['user_gain_exp'] = $userdata['gain_exp'];
			$user->add_chat_message($userdata, '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $userdata['user_hit_hp'] . ' HP</b>. Получено опыта: <b>' . $userdata['user_gain_exp'] . '</b>.', $userdata['user_login'], true, true);
		}
		else
		{
			// Системное сообщение о получении опыта
			$user->add_chat_message($team[$userdata['user_battle_team']][$i], '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $team[$userdata['user_battle_team']]['user_hit_hp'][$i] . ' HP</b>. Получено опыта: <b>' . $userdata['gain_exp'] . '</b>.', $team[$userdata['user_battle_team']]['user_login'][$i], true, true);
		}
	}

	// Сообщение об окончании боя
	$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . date('H:i', time()) . '</font> Бой закончен. Победа за <b>' . substr($team[$userdata['user_battle_team']]['logins'], 0, -2) . '</b><br>');

	for( $i = 0; $i < count($team[$enemy_team_id]['user_login']); $i++ )
	{
		// Имена бойцов проигравшей команды
		$team[$enemy_team_id]['logins'] .= $team[$enemy_team_id]['user_login'][$i] . ', ';

		// Получаем данные
		$enemy_userdata[$i] = get_userdata($team[$enemy_team_id]['user_id'][$i], '');
		$enemy_items[$i] = $user->get_equip_items($enemy_userdata[$i]);

		//
		// Ломаем вещи проигравшим
		//
		for( $k = 1; $k < 16; $k++ )
		{
			$user->damage_items($k, $enemy_userdata[$i], $enemy_items[$i]);
		}

		$user->damage_items(400, $enemy_userdata[$i], $enemy_items[$i]);
		// ----------

		if( $pl2_userdata['user_id'] == $team[$enemy_team_id]['user_id'][$i] )
		{
			// Системное сообщение о получении опыта
			$user->add_chat_message($pl2_userdata, '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $pl2_userdata['user_hit_hp'] . ' HP</b>. Получено опыта: <b>0</b>.', $pl2_userdata['user_login'], true, true);
		}
		else
		{
			// Системное сообщение о получении опыта
			$user->add_chat_message($team[$enemy_team_id][$i], '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $team[$enemy_team_id]['user_hit_hp'][$i] . ' HP</b>. Получено опыта: <b>0</b>.', $team[$enemy_team_id]['user_login'][$i], true, true);
		}
	}

	//
	// Проигравшая команда
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = 0, user_losses = (user_losses + 1) WHERE `user_battle_id` = " . $userdata['user_battle_id'] . " AND `user_battle_team` = " . $enemy_team_id;
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные проигравшей команды...', '', __LINE__, __FILE__, $sql);
	}
	// ----------
}
elseif( $draw && !$log_time_end )
{
	// Определяем переменные
	$team[1]['logins'] = '';
	$team[2]['logins'] = '';

	// Сообщение об окончании боя
	$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . date('H:i', time()) . '</font> Бой закончен. Ничья<br />');

	// Команда-победитель
	$winner_team = 0;

	for( $i = 0; $i < count($team[$userdata['user_battle_team']]['user_login']); $i++ )
	{
		// Имена персонажей, находящихся в команде игрока
		$team[$userdata['user_battle_team']]['logins'] .= $team[$userdata['user_battle_team']]['user_login'][$i] . ', ';

		// Получаем данные
		$ally_userdata[$i] = get_userdata($team[$userdata['user_battle_team']]['user_id'][$i], '');
		$ally_items[$i] = $user->get_equip_items($ally_userdata[$i]);

		//
		// Ломаем вещи
		//
		for( $k = 1; $k < 16; $k++ )
		{
			if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
			{
				$user->damage_items($k, $ally_userdata[$i], $ally_items[$i]);
			}
			else
			{
				$user->damage_items($k, $ally_userdata[$i], $ally_items[$i]);
			}
		}

		if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
		{
			$user->damage_items(400, $ally_userdata[$i], $ally_items[$i]);
		}
		else
		{
			$user->damage_items(400, $ally_userdata[$i], $ally_items[$i]);
		}
		// ----------

		// Обновляем данные одетых вещей
		if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
		{
			$items = $ally_items[$i];
		}

		// Сообщение о получении опыта
		$user->add_chat_message($ally_userdata[$i], '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $ally_userdata[$i]['user_hit_hp'] . ' HP</b>. Получено опыта: <b>0</b>.', $ally_userdata[$i]['user_login'], true, true);
	}

	for( $i = 0; $i < count($team[$enemy_team_id]['user_login']); $i++ )
	{
		// Имена персонажей, находящихся в команде противника
		$team[$enemy_team_id]['logins'] .= $team[$enemy_team_id]['user_login'][$i] . ', ';

		//
		// Получаем данные
		//
		$enemy_userdata[$i] = get_userdata($team[$enemy_team_id]['user_id'][$i], '');
		$enemy_items[$i] = $user->get_equip_items($enemy_userdata[$i]);
		// ----------

		//
		// Ломаем вещи проигравшим
		//
		for( $k = 1; $k < 16; $k++ )
		{
			$user->damage_items($k, $enemy_userdata[$i], $enemy_items[$i]);
		}

		$user->damage_items(400, $enemy_userdata[$i], $enemy_items[$i]);
		// ----------

		// Сообщение о получении опыта
		$user->add_chat_message($enemy_userdata[$i], '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $enemy_userdata[$i]['user_hit_hp'] . ' HP</b>. Получено опыта: <b>0</b>.', $enemy_userdata[$i]['user_login'], true, true);
	}

	//
	// Сбрасываем HP и добавляем ничью
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = 0, user_draws = (user_draws + 1) WHERE `user_battle_id` = " . $userdata['user_battle_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные проигравшей команды...', '', __LINE__, __FILE__, $sql);
	}
	// ----------
}
elseif( $loss && !$log_time_end )
{
	// Определяем переменные
	$logins = '';
	$team[1]['logins'] = '';
	$team[2]['logins'] = '';

	for( $i = 0; $i < count($team[$enemy_team_id]['user_login']); $i++ )
	{
		// Имена персонажей, находящихся в команде противника
		$team[$enemy_team_id]['logins'] .= $team[$enemy_team_id]['user_login'][$i] . ', ';

		if( $team[$enemy_team_id]['user_id'][$i] == $pl2_userdata['user_id'] )
		{
			// Обновляем урон, набитый противником
			$team[$enemy_team_id]['user_hit_hp'][$i] = $pl2_userdata['user_hit_hp'];
		}

		// Раздаем опыт
		$pl2_userdata['gain_exp'] = $user->get_gained_experience($pl2_userdata['user_battle_team'], $team[$enemy_team_id]['user_hit_hp'][$i], $team[$enemy_team_id]['user_items_cost'][$i], $team[$enemy_team_id]['user_level'][$i], $team);

		$sql = "UPDATE " . USERS_TABLE . " SET user_gain_exp = " . $pl2_userdata['gain_exp'] . ", user_exp = (user_exp + " . $pl2_userdata['gain_exp'] . "), user_wins = (user_wins + 1) WHERE `user_id` = " . $team[$enemy_team_id]['user_id'][$i];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------
	}

	$winner_team = 2;

	$user->add_chat_message($userdata, '<font color=red>Внимание!</font> Бой закончен. Всего вами нанесено урона: <b>' . $userdata['user_hit_hp'] . ' HP</b>. Получено опыта: <b>0</b>.', $userdata['user_login'], true, true);
	$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . date('H:i', time()) . '</font> Бой закончен. Победа за <b>' . substr($team[$enemy_team_id]['logins'], 0, strlen($logins) - 2) . '</b><br />');

	for( $i = 0; $i < count($team[$userdata['user_battle_team']]['user_login']); $i++ )
	{
		// Имена персонажей, находящихся в команде противника
		$team[$userdata['user_battle_team']]['logins'] .= $team[$userdata['user_battle_team']]['user_login'][$i] . ', ';

		//
		// Получаем данные
		//
		$ally_userdata[$i] = get_userdata($team[$userdata['user_battle_team']]['user_id'][$i], '');
		$ally_items[$i] = $user->get_equip_items($ally_userdata[$i]);
		// ----------

		//
		// Ломаем вещи проигравшим
		//
		for( $k = 1; $k < 16; $k++ )
		{
			if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
			{
				$user->damage_items($k, $ally_userdata[$i], $ally_items[$i]);
			}
			else
			{
				$user->damage_items($k, $ally_userdata[$i], $ally_items[$i]);
			}
		}

		if( $userdata['user_id'] == $team[$userdata['user_battle_team']]['user_id'][$i] )
		{
			$user->damage_items(400, $ally_userdata[$i], $ally_items[$i]);
		}
		else
		{
			$user->damage_items(400, $ally_userdata[$i], $ally_items[$i]);
		}
		// ----------
	}

	//
	// Проигравшая команда
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = 0, user_losses = (user_losses + 1) WHERE `user_battle_id` = " . $userdata['user_battle_id'] . " AND `user_battle_team` = " . $userdata['user_battle_team'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные проигравшей команды...', '', __LINE__, __FILE__, $sql);
	}
	// ----------
}

//
// Если бой закончен, то начинаем восстановление
//
if( !$fight && !$log_time_end )
{
	//
	// Начинаем восстановление
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_start_regen = " . time() . " WHERE `user_battle_id` = " . $userdata['user_battle_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные пользователя...');
	}
	// ----------

	//
	// Вставляем время окончания боя
	//
	$sql = "UPDATE " . LOGS_TABLE . " SET log_time_end = " . time() . ", log_winner = " . $winner_team . " WHERE `log_id` = " . $userdata['user_battle_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить лог-файл...', '', __LINE__, __FILE__, $sql);
	}

	$user->add_chat_message($userdata, '<a href="logs.php?log=' . $userdata['user_battle_id'] . '" target=_blank>Бой</a> между <b>' . substr($team[1]['logins'], 0, -2) . '</b>' . ( ( $winner_team == 1 ) ? '<img src="i/flag.gif" width=20 height=20 alt="Победитель">' : '' ) . ' и <b>' . substr($team[2]['logins'], 0, -2) . '</b>' . ( ( $winner_team == 2 ) ? '<img src="i/flag.gif" width=20 height=20 alt="Победитель">' : '' ) . ' закончен.<br>', false, false, true);

	$log_time_end = time();
	// ----------
}
// ----------

//
// Получаем данные лога
//
$sql = "SELECT log_text FROM " . LOGS_TEXT_TABLE . " WHERE `log_id` = " . $userdata['user_battle_id'] . " ORDER BY `log_text_id` DESC";
if( !$result = $db->sql_query($sql) )
{
	site_message('Не могу получить данные боя...', '', __LINE__, __FILE__, $sql);
}

$log_text = '';
$n = 0;

while( $row = $db->sql_fetchrow($result) )
{
	if( $n < 20 && !( $n == 0 && $row['log_text'] == '<script>dv();</script>' ) )
	{
		$log_text .= $row['log_text'];

		$n++;
	}
}
// ----------

site_header();

$template->set_filenames(array(
	'body'	=> 'battle_new_body.html')
);

//
// Состояние боя
//
if( !$death && $fight )
{
	$template->assign_block_vars('fight', array());
}
elseif( $death && $fight )
{
	$template->assign_block_vars('death', array());
}
elseif( !$fight )
{
	$template->assign_block_vars('fight_is_over', array());
}
// ----------

if( $fight )
{
	//
	// Уровень жизни
	//
	$hp = intval($userdata['user_current_hp']) . '/' . $userdata['user_max_hp'];

	$hp_length = 240 - ( ( strlen($hp) + 2 ) * 7 );

	$hp_width = (($hp_length - 1) / $userdata['user_max_hp']) * $userdata['user_current_hp'];

	$hp_type = ( $userdata['user_current_hp'] / $userdata['user_max_hp'] < '0.33' ) ? 'red' : ( ( $userdata['user_current_hp'] / $userdata['user_max_hp'] < '0.66' ) ? 'yellow' : 'green' );
	// ----------

	//
	// Уровень маны
	//
	if( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 )
	{
		$mana = intval($userdata['user_current_mana']) . '/' . $userdata['user_max_mana'];

		$mana_length = 240 - ( ( strlen($mana) + 2 ) * 7 );

		$mana_width = ( ( $mana_length - 1 ) / $userdata['user_max_mana'] ) * $userdata['user_current_mana'];
	}
	// ----------

	//
	// Уровень жизни противника
	//
	$pl2_hp = intval($pl2_userdata['user_current_hp']) . '/' . $pl2_userdata['user_max_hp'];

	$pl2_hp_length = 240 - ( ( strlen($pl2_hp) + 2 ) * 7 );

	$pl2_hp_width = (($pl2_hp_length - 1) / $pl2_userdata['user_max_hp']) * $pl2_userdata['user_current_hp'];

	$pl2_hp_type = ( $pl2_userdata['user_current_hp'] / $pl2_userdata['user_max_hp'] < '0.33' ) ? 'red' : ( ( $pl2_userdata['user_current_hp'] / $pl2_userdata['user_max_hp'] < '0.66' ) ? 'yellow' : 'green' );
	// ----------

	//
	// Уровень маны противника
	//
	if( $pl2_userdata['user_level'] >= 7 && $pl2_userdata['user_max_mana'] > 0 )
	{
		$pl2_mana = intval($pl2_userdata['user_current_mana']) . '/' . $pl2_userdata['user_max_mana'];

		$pl2_mana_length = 240 - ( ( strlen($pl2_mana) + 2 ) * 7 );

		$pl2_mana_width = ( ( $pl2_mana_length - 1 ) / $pl2_userdata['user_max_mana'] ) * $pl2_userdata['user_current_mana'];
	}
	// ----------

	$template->assign_vars(array(
		'HP_STRING'			=> $hp . '&nbsp;<img alt="Уровень жизни" height="8" src="i/misc/bk_life_' . $hp_type . '.gif" width="' . $hp_width . '"><img alt="Уровень жизни" height="8" src="i/misc/bk_life_loose.gif" width="' . ( $hp_length - $hp_width ) . '"><span style="height: 10px; width: 1px;"></span><img alt="Уровень жизни" height="9" src="i/herz.gif" width="10">',
		'MANA_STRING'		=> ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? $mana . '&nbsp;<img alt="Уровень маны" height="8" src="i/misc/bk_life_beg_33.gif" width="' . $mana_width . '"><img alt="Уровень маны" height="8" src="i/misc/bk_life_loose.gif" width="' . ( $mana_length - $mana_width ) . '"><span style="height: 10px; width: 1px"></span><img alt="Уровень маны" height="9" src="i/Mherz.gif" width="9">' : '',
			
		'PL2_HP_STRING'		=> $pl2_hp . '&nbsp;<img alt="Уровень жизни" height="8" src="i/misc/bk_life_' . $pl2_hp_type . '.gif" width="' . $pl2_hp_width . '"><img alt="Уровень жизни" height="8" src="i/misc/bk_life_loose.gif" width="' . ( $pl2_hp_length - $pl2_hp_width ) . '"><span style="height: 10px; width: 1px;"></span><img alt="Уровень жизни" height="9" src="i/herz.gif" width="10">',
		'PL2_MANA_STRING'	=> ( $pl2_userdata['user_level'] >= 7 && $pl2_userdata['user_max_mana'] > 0 ) ? $pl2_mana . '&nbsp;<img alt="Уровень маны" height="8" src="i/misc/bk_life_beg_33.gif" width="' . $pl2_mana_width . '"><img alt="Уровень маны" height="8" src="i/misc/bk_life_loose.gif" width="' . ( $pl2_mana_length - $pl2_mana_width ) . '"><span style="height: 10px; width: 1px"></span><img alt="Уровень маны" height="9" src="i/Mherz.gif" width="9">' : '')
	);
}
else
{
	$template->assign_vars(array(
		'HP_STRING'			=> '<span id="HP" style="font-size: 10px; font-weight: normal; cursor: default;"></span>&nbsp;<img alt="Уровень жизни" height="8" id="HP1" name="HP1" src="i/misc/bk_life_loose.gif" width="1"><img alt="Уровень жизни" height="8" id="HP2" name="HP2" src="i/misc/bk_life_loose.gif" width="1"><span style="height: 10px; width: 1px"></span><img alt="Уровень жизни" height="9" src="i/herz.gif" width="10">',
		'MANA_STRING'		=> ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? '<span id="Mana" style="font-size: 10px; font-weight: normal; cursor: default;"></span>&nbsp;<img alt="Уровень маны" height="8" id="Mana1" name="Mana1" src="i/misc/bk_life_loose.gif" width="1"><img alt="Уровень маны" height="8" id="Mana2" name="Mana2" src="i/misc/bk_life_loose.gif" width="1"><span style="height: 10px; width: 1px"></span><img alt="Уровень маны" height="9" src="i/Mherz.gif" width="9">' : '')
	);
}

//
// Картинки окончания боя
//
if( !$fight )
{
	$img['end'] = array('1.jpg', '2.gif', '4.jpg', '5.gif', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg', '12.gif', '13.jpg', '15.gif', '21.jpg', '24.jpg', '27.jpg', '46.jpg', '47.jpg', '52.jpg');

	$template->assign_vars(array(
		'END_BATTLE_IMG'	 => $img['end'][mt_rand(0, count($img['end']) - 1)])
	);
}
// ----------

//
// Вывод свитков (если есть)
//
$have_scrolls = false;

if( ( $userdata['user_w100'] || $userdata['user_w101'] || $userdata['user_w102'] || $userdata['user_w103'] || $userdata['user_w104'] || $userdata['user_w105'] || $userdata['user_w106'] || $userdata['user_w107'] || $userdata['user_w108'] || $userdata['user_w109'] ) && $fight )
{
	$template->assign_block_vars('scrolls', array());
	$have_scrolls = true;
}
// ----------

if( !$fight )
{
	$user->start_regen($userdata, false);
	$user->update_hp($userdata);
	$user->update_mana($userdata);
}

if( $userdata['user_level'] >= 5 )
{
	$template->assign_block_vars('specials', array());
}

$template->assign_vars(array(
	//
	// Первый персонаж
	//
	'ATTACKS'					=> $userdata['user_attacks'],
	'CURRENT_HP'				=> ( $fight ) ? intval($userdata['user_current_hp']) : ( ( $userdata['user_current_hp'] <= 0 ) ? 0 : $userdata['user_current_hp']),
	'DRWFL'						=> $user->drwfl($userdata),
	'GAINED_EXP'				=> ( !$fight ) ? '<font color="red">Бой закончен. Всего вами нанесено урона: <b>' . $userdata['user_hit_hp'] . ' HP</b>. Получено опыта: <b>' . $userdata['user_gain_exp'] . '</b>.</font><br />' : '',
	'HIT_HP'					=> ( $fight && $userdata['user_hit_hp'] > 0 ) ? '<br />На данный момент вами нанесено урона: <b>' . $userdata['user_hit_hp'] . ' HP.</b><br />' : '',
	'HPSPEED'					=> $userdata['user_hpspeed'],
	'LOGIN'						=> $userdata['user_login'],
	'MAX_HP'					=> $userdata['user_max_hp'],
	'OBRAZ'						=> $userdata['user_obraz'],

	'SPECIAL_SELECTED_1'		=> ( $userdata['user_level'] >= 5 && $special_selected[0] ) ? $special_move->html($special_selected[0], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_2'		=> ( isset($special_selected[1]) ) ? $special_move->html($special_selected[1], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_3'		=> ( isset($special_selected[2]) ) ? $special_move->html($special_selected[2], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_4'		=> ( isset($special_selected[3]) ) ? $special_move->html($special_selected[3], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_5'		=> ( isset($special_selected[4]) ) ? $special_move->html($special_selected[4], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_6'		=> ( isset($special_selected[5]) ) ? $special_move->html($special_selected[5], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_7'		=> ( isset($special_selected[6]) ) ? $special_move->html($special_selected[6], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_8'		=> ( isset($special_selected[7]) ) ? $special_move->html($special_selected[7], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_9'		=> ( isset($special_selected[8]) ) ? $special_move->html($special_selected[8], $userdata, 'battle') : '',
	'SPECIAL_SELECTED_10'		=> ( isset($special_selected[9]) ) ? $special_move->html($special_selected[9], $userdata, 'battle') : '',

	'COUNT_HIT'					=> $userdata['user_count_hit'],
	'COUNT_CRITICAL_HIT'		=> $userdata['user_count_critical_hit'],
	'COUNT_COUNTERBLOW'			=> $userdata['user_count_counterblow'],
	'COUNT_BLOCK'				=> $userdata['user_count_block'],
	'COUNT_PARRY'				=> $userdata['user_count_parry'],

	'STRENGTH'					=> $userdata['user_strength'],
	'AGILITY'					=> $userdata['user_agility'],
	'PERCEPTION'				=> $userdata['user_perception'],
	'VITALITY'					=> $userdata['user_vitality'],
	'INTELLECT'					=> ( $userdata['user_level'] >= 4 ) ? 'Интеллект: ' . $userdata['user_intellect'] . '<br>' : '',
	'WISDOM'					=> ( $userdata['user_level'] >= 7 ) ? 'Мудрость: ' . $userdata['user_wisdom'] . '<br>' : '',
	'SPIRITUALITY'				=> ( $userdata['user_level'] >= 10 ) ? 'Духовность: ' . $userdata['user_spirituality'] . '<br>' : '',
	'FREEDOM'					=> ( $userdata['user_level'] >= 13 ) ? 'Воля: ' . $userdata['user_freedom'] . '<br>' : '',
	'FREEDOM_OF_SPIRIT'			=> ( $userdata['user_level'] >= 16 ) ? 'Свобода духа: ' . $userdata['user_freedom_of_spirit'] . '<br>' : '',
	'HOLINESS'					=> ( $userdata['user_level'] >= 19 ) ? 'Божественность: ' . $userdata['user_holiness'] . '<br>' : '',

	'I_SCROLL_1'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w100', 'battle') : '',
	'I_SCROLL_2'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w101', 'battle') : '',
	'I_SCROLL_3'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w102', 'battle') : '',
	'I_SCROLL_4'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w103', 'battle') : '',
	'I_SCROLL_5'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w104', 'battle') : '',
	'I_SCROLL_6'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w105', 'battle') : '',
	'I_SCROLL_7'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w106', 'battle') : '',
	'I_SCROLL_8'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w107', 'battle') : '',
	'I_SCROLL_9'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w108', 'battle') : '',
	'I_SCROLL_10'				=> ( $have_scrolls ) ? $user->show_item($userdata, $items, 'w109', 'battle') : '',

	'I_HELMET'					=> $user->show_item($userdata, $items, 'w9', 'battle'),
	'I_WEAPON'					=> $user->show_item($userdata, $items, 'w3', 'battle'),
	'I_ARMOR'					=> ( $userdata['user_w4'] == 0 && $userdata['user_w400'] > 0 ) ? $user->show_item($userdata, $items, 'w400', 'battle') : $user->show_item($userdata, $items, 'w4', 'battle'),
	'I_BELT'					=> $user->show_item($userdata, $items, 'w5', 'battle'),
	'I_R_KARMAN'				=> $user->show_item($userdata, $items, 'w14', 'battle'),
	'I_L_KARMAN'				=> $user->show_item($userdata, $items, 'w15', 'battle'),
	'I_CLIP'					=> $user->show_item($userdata, $items, 'w1', 'battle'),
	'I_AMULET'					=> $user->show_item($userdata, $items, 'w2', 'battle'),
	'I_NARUCHI'					=> $user->show_item($userdata, $items, 'w13', 'battle'),
	'I_GLOVES'					=> $user->show_item($userdata, $items, 'w11', 'battle'),
	'I_RING1'					=> $user->show_item($userdata, $items, 'w6', 'battle'),
	'I_RING2'					=> $user->show_item($userdata, $items, 'w7', 'battle'),
	'I_RING3'					=> $user->show_item($userdata, $items, 'w8', 'battle'),
	'I_SHIELD'					=> $user->show_item($userdata, $items, 'w10', 'battle'),
	'I_BOOTS'					=> $user->show_item($userdata, $items, 'w12', 'battle'),
	// ----------

	//
	// Блоки (описание)
	//
	'BLOCK1'					=> ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 'головы, груди и живота' : 'головы и груди',
	'BLOCK2'					=> ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 'груди, живота и пояса' : 'груди и живота',
	'BLOCK3'					=> ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 'живота, пояса и ног' : 'живота и пояса',
	'BLOCK4'					=> ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 'пояса, головы и ног' : 'пояса и ног',
	'BLOCK5'					=> ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 'ног, головы и груди' : 'головы и ног',
	// ----------

	//
	// Второй персонаж
	//
	'PL2_CURRENT_HP'			=> ( $fight ) ? intval($pl2_userdata['user_current_hp']) : '',
	'PL2_DRWFL'					=> ( $fight ) ? $user->drwfl($pl2_userdata) : '',
	'PL2_LOGIN'					=> ( $fight ) ? $pl2_userdata['user_login'] : '',
	'PL2_MAX_HP'				=> ( $fight ) ? $pl2_userdata['user_max_hp'] : '',
	'PL2_OBRAZ'					=> ( $fight ) ? $pl2_userdata['user_obraz'] : '',

	'PL2_STRENGTH'				=> ( $fight ) ? $pl2_userdata['user_strength'] : '',
	'PL2_AGILITY'				=> ( $fight ) ? $pl2_userdata['user_agility'] : '',
	'PL2_PERCEPTION'			=> ( $fight ) ? $pl2_userdata['user_perception'] : '',
	'PL2_VITALITY'				=> ( $fight ) ? $pl2_userdata['user_vitality'] : '',
	'PL2_INTELLECT'				=> ( $fight && $pl2_userdata['user_level'] >= 4 ) ? 'Интеллект: ' . $pl2_userdata['user_intellect'] . '<br>' : '',
	'PL2_WISDOM'				=> ( $fight && $pl2_userdata['user_level'] >= 7 ) ? 'Мудрость: ' . $pl2_userdata['user_wisdom'] . '<br>' : '',
	'PL2_SPIRITUALITY'			=> ( $fight && $pl2_userdata['user_level'] >= 10 ) ? 'Духовность: ' . $pl2_userdata['user_spirituality'] . '<br>' : '',
	'PL2_FREEDOM'				=> ( $fight && $pl2_userdata['user_level'] >= 13 ) ? 'Воля: ' . $pl2_userdata['user_freedom'] . '<br>' : '',
	'PL2_FREEDOM_OF_SPIRIT'		=> ( $fight && $pl2_userdata['user_level'] >= 16 ) ? 'Свобода духа: ' . $pl2_userdata['user_freedom_of_spirit'] . '<br>' : '',
	'PL2_HOLINESS'				=> ( $fight && $pl2_userdata['user_level'] >= 19 ) ? 'Божественность: ' . $pl2_userdata['user_holiness'] . '<br>' : '',

	'PL2_I_HELMET'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w9', 'battle') : '',
	'PL2_I_WEAPON'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w3', 'battle') : '',
	'PL2_I_ARMOR'				=> ( $fight && $pl2_userdata['user_w4'] == 0 && $pl2_userdata['user_w400'] > 0 ) ? $user->show_item($pl2_userdata, $pl2_items, 'w400', 'battle') : ( ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w4', 'battle') : ''),
	'PL2_I_BELT'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w5', 'battle') : '',
	'PL2_I_R_KARMAN'			=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w14', 'battle') : '',
	'PL2_I_L_KARMAN'			=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w15', 'battle') : '',
	'PL2_I_CLIP'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w1', 'battle') : '',
	'PL2_I_AMULET'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w2', 'battle') : '',
	'PL2_I_NARUCHI'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w13', 'battle') : '',
	'PL2_I_GLOVES'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w11', 'battle') : '',
	'PL2_I_RING1'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w6', 'battle') : '',
	'PL2_I_RING2'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w7', 'battle') : '',
	'PL2_I_RING3'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w8', 'battle') : '',
	'PL2_I_SHIELD'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w10', 'battle') : '',
	'PL2_I_BOOTS'				=> ( $fight ) ? $user->show_item($pl2_userdata, $pl2_items, 'w12', 'battle') : '',
	// ----------

	'TEAM1_LIST'				=> ( $fight ) ? $team[1]['list'] : '',
	'TEAM2_LIST'				=> ( $fight ) ? $team[2]['list'] : '',

	'CUT_LOG'					=> ( $n >= 20 ) ? '<font color="red">Вырезано, для уменьшения объема информации.</font> Полный лог боя смотрите <a href="logs.php?log=' . $userdata['user_battle_id'] . '" target="_blank">здесь</a><br />' : '',
	'LOG_TEXT'					=> $log_text,
	'MESSAGE'					=> ( $message ) ? '<font color="red">' . $message . '</font>' : '',
	'META'						=> ( $config['fast_game'] && !$death && $fight && $userdata['user_bot'] ) ? '<meta http-equiv="refresh" content="' . $config['fast_game_battle_refresh'] . ';url=battle.php?go">' : ( ( $config['fast_game'] && !$fight && $userdata['user_bot'] ) ? '<meta http-equiv="refresh" content="' . $config['fast_game_battle_exit'] . ';url=battle.php?gameover">' : '' ),
	'SCRIPT_SETHP'				=> ( !$fight ) ? '<script>top.setHP(' . $userdata['user_current_hp'] . ',' . $userdata['user_max_hp'] . ',' . $userdata['user_hpspeed'] . ');' . ( ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? 'top.setMana(' . $userdata['user_current_mana'] . ',' . $userdata['user_max_mana'] . ',' . $userdata['user_manaspeed'] . ');' : '' ) . '</script>' : '',
	'TEAMS_LIST'				=> ( $fight ) ? '<div id="mes" onclick="AddLogin()" oncontextmenu="OpenMenu()"><img src="i/lock3.gif" width="20" height="15" border="0" alt="приват" style="cursor: hand" onclick="Prv(\'' . $team[1]['private_list'] . '\')"> ' . substr($team[1]['list'], 0, strlen($team[1]['list']) -2) . ' против <img src="i/lock3.gif" width="20" height="15" border="0" alt="приват" style="cursor: hand" onclick="Prv(\'' . $team[2]['private_list'] . '\')"> ' . substr($team[2]['list'], 0, strlen($team[2]['list']) -2) . '<hr></div>' : '',
	'TIMEOUT'					=> ( $fight ) ? '<br /><font class="dsc">(Бой идет с таймаутом ' . $log_timeout . ' мин.)</font><br />' : ''));

$template->pparse('body');

site_bottom();

?>