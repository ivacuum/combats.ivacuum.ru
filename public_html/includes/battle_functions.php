<?php

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

// ---------------
// Удара в поединке
//
function hit($userdata, $pl2_userdata, $items, $type, $zone)
{
	global $db, $user;

	$userdata['hit_type'] = $type;

	if( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] != 'shield' && $userdata['attack_n'] == 1)
	{
		$userdata = $user->hit_power($userdata, $pl2_userdata, $items, $zone, true);
	}
	else
	{
		$userdata = $user->hit_power($userdata, $pl2_userdata, $items, $zone);
	}

	$hit = $userdata['hit'];

	//
	// Обновляем нанесенный урон
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_hit_hp = (user_hit_hp + " . $hit . ") WHERE `user_id` = " . $userdata['user_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	//
	// Обновляем уровень HP
	//
	if( ( $pl2_userdata['user_current_hp'] - $hit ) < 0 )
	{
		$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = 0 WHERE `user_id` = " . $pl2_userdata['user_id'];
	}
	else
	{
		$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = (user_current_hp - " . $hit . ") WHERE `user_id` = " . $pl2_userdata['user_id'];
	}

	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	$userdata['user_hit_hp'] += $hit;
	$userdata['hit'] = $hit;

	return $userdata;
}
// ---------------

// ---------------
// Добавляем запись в лог боя
//
function log_msg($userdata, $pl2_userdata, $hit, $type, $team, $time, $zone)
{
	global $db, $items, $pl2_items, $msg1, $msg2, $msg3;

	//
	// Оружие, которым наносится удар
	//
	if( $userdata['user_w3'] > 0 )
	{
		if( $userdata['user_w3'] == $pl2_items['item_id'][3] )
		{
			$log_items = $pl2_items;
		}
		else
		{
			$log_items = $items;
		}

		if( $log_items['item_type'][3] == 'knife' )
		{
			$msg3['w1'][] = 'лезвием ножа';
			$msg3['w1'][] = 'острым ножом';
			$msg3['w1'][] = 'рукояткой ножа';
		}
		elseif( $log_items['item_type'][3] == 'axe' )
		{
			$msg3['w1'][] = 'занозой на рукоятке топора';
			$msg3['w1'][] = 'лезвием топора';
			$msg3['w1'][] = 'маленьким топорищем';
			$msg3['w1'][] = 'обухом топора';
			$msg3['w1'][] = 'ручкой топора';
			$msg3['w1'][] = 'топором';
			$msg3['w1'][] = 'тяжелым топором';
		}
		elseif( $log_items['item_type'][3] == 'club' )
		{
			$msg3['w1'][] = 'гардой';
			$msg3['w1'][] = 'мощным молотом';
			$msg3['w1'][] = 'ручкой молота';
			$msg3['w1'][] = 'тяжелым молотом';
		}
		elseif( $log_items['item_type'][3] == 'staff' )
		{
			$msg3['w1'][] = 'посохом';
		}
		elseif( $log_items['item_type'][3] == 'sword' )
		{
			$msg3['w1'][] = 'гардой';
			$msg3['w1'][] = 'клинком меча';
			$msg3['w1'][] = 'лезвием меча';
			$msg3['w1'][] = 'ножнами';
			$msg3['w1'][] = 'острым мечом';
			$msg3['w1'][] = 'плоской стороной лезвия';
			$msg3['w1'][] = 'рукояткой меча';
		}
		elseif( $log_items['item_type'][3] == 'flowers' )
		{
			$msg3['w1'][] = 'красивым букетом';
			$msg3['w1'][] = 'лепестками цветов';
		}
	}
	else
	{
		$msg3['w1'][] = 'головой';
		$msg3['w1'][] = 'грудью';
		$msg3['w1'][] = 'кистью руки';
		$msg3['w1'][] = 'коленом';
		$msg3['w1'][] = 'копчиком';
		$msg3['w1'][] = 'кулаком';
		$msg3['w1'][] = 'ладонью';
		$msg3['w1'][] = 'локтем';
		$msg3['w1'][] = 'ногой';
		$msg3['w1'][] = 'открытой ладонью';
		$msg3['w1'][] = 'пальцем';
	}
	// ----------

	//
	// Генератор фраз
	//
	$n1 = mt_rand(0, count($msg1) - 1);
	$n2[1] = mt_rand(0, count($msg2[1]) - 1);
	$n2[2] = mt_rand(0, count($msg2[2]) - 1);
	$n3['w1'] = mt_rand(0, count($msg3['w1']) - 1);
	$n3['critical_hit'][1] = mt_rand(0, count($msg3['critical_hit'][1]) - 1);
	$n3['critical_hit'][2] = mt_rand(0, count($msg3['critical_hit'][2]) - 1);
	$n3['critical_hit'][3] = mt_rand(0, count($msg3['critical_hit'][3]) - 1);
	$n3['attack'][1] = mt_rand(0, count($msg3['attack'][1]) - 1);
	$n3['attack'][2] = mt_rand(0, count($msg3['attack'][2]) - 1);
	$n3['attack'][3] = mt_rand(0, count($msg3['attack'][3]) - 1);
	$n3['attack'][4] = mt_rand(0, count($msg3['attack'][4]) - 1);
	$n3['attack'][5] = mt_rand(0, count($msg3['attack'][5]) - 1);
	$n3['attack'][6] = mt_rand(0, count($msg3['attack'][6]) - 1);
	$n3['attack'][7] = mt_rand(0, count($msg3['attack'][7]) - 1);

	if( $userdata['attack_type'] )
	{
		$n3['attack'][$userdata['attack_type']] = mt_rand(0, count($msg3['attack'][$userdata['attack_type']]) - 1);
	}

	$n3['block'][1] = mt_rand(0, count($msg3['block'][1]) - 1);
	$n3['block'][2] = mt_rand(0, count($msg3['block'][2]) - 1);
	$n3['counterblow'][1] = mt_rand(0, count($msg3['counterblow'][1]) - 1);
	$n3['dodging'][1] = mt_rand(0, count($msg3['dodging'][1]) - 1);
	$n3['parry'][1] = mt_rand(0, count($msg3['parry'][1]) - 1);
	$n3[$zone] = mt_rand(0, count($msg3[$zone]) - 1);

	$part1and2 = $msg1[$n1] . $msg2[1][$n2[1]] . $msg2[2][$n2[2]];

	// Удар
	if( $userdata['user_w3'] > 0 && $userdata['attack_type'] )
	{
		$part3['attack'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][3][$n3['attack'][3]] . ' ' . $msg3['attack'][$userdata['attack_type']][$n3['attack'][$userdata['attack_type']]] . ' ' . $msg3['w1'][$n3['w1']];
	}
	else
	{
		$part3['attack'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . ' ' . $msg3['w1'][$n3['w1']];
	}

	// Блок
	$part3['block'] = $msg3['block'][1][$n3['block'][1]] . ' ' . $msg3['block'][2][$n3['block'][2]] . ' ' . $msg3['w1'][$n3['w1']];

	// Контрудар / уворот
	$part3['counterblow'] = $msg3['dodging'][1][$n3['dodging'][1]] . ' ' . $msg3['w1'][$n3['w1']];
	$part3['dodging'] = $msg3['dodging'][1][$n3['dodging'][1]] . ' ' . $msg3['w1'][$n3['w1']];

	// Крит
	$part3['critical_hit'] = $msg3['critical_hit'][1][$n3['critical_hit'][1]] . ' ' . $msg3['critical_hit'][2][$n3['critical_hit'][2]] . ' ' . $msg3['critical_hit'][3][$n3['critical_hit'][3]];

	// Крит, пробив блок
	$part3['critical_hit_in_block'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][3][$n3['attack'][3]] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . $msg3['attack'][5][$n3['attack'][5]] . ' ' . $msg3['w1'][$n3['w1']];

	// Крит, пробив защиту
	$part3['critical_hit_in_parry'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][3][$n3['attack'][3]] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . $msg3['attack'][6][$n3['attack'][6]] . ' ' . $msg3['w1'][$n3['w1']];

	// Парирование
	$part3['parry'] = $msg3['parry'][1][$n3['parry'][1]] . ' ' . $msg3['block'][2][$n3['block'][2]] . ' ' . $msg3['w1'][$n3['w1']];
	// ----------

	//
	// Определяем цвета и раскраску
	//
	$hit_color = ( $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' ) ? '#FF0000' : '#006699';
	$login_color = $team;
	$login2_color = ( $team == 1 ) ? 2 : 1;
	$time_color = ( $time == 'sys' ) ? 'sysdate' : 'date';
	// ----------

	//
	// Уменьшаем уровень жизни
	//
	$pl2_userdata['user_current_hp'] -= $hit;
	$pl2_userdata['user_current_hp'] = ( $pl2_userdata['user_current_hp'] < 0 ) ? 0 : $pl2_userdata['user_current_hp'];
	// ----------

	$part3['text'] = $part3[$type];

	//
	// Зона удара / блока
	//
	$zone = $msg3[$zone][$n3[$zone]];
	// ----------

	//
	// Добавляем к 3 части сообщения концовку...
	// P.S. Для каждого случая разная...
	//
	if( $type == 'attack' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' )
	{
		$part3['text'] .= ' ' . $zone . ' ' . $msg3['attack'][7][$n3['attack'][7]] . '. <font color="' . $hit_color . '"><b>-' . $hit . '</b></font> [' . intval($pl2_userdata['user_current_hp']) . '/' . intval($pl2_userdata['user_max_hp']) . ']';
	}
	elseif( $type == 'block' || $type == 'dodging' || $type == 'parry' )
	{
		$part3['text'] .= ' ' . $zone . '.';
	}
	elseif( $type == 'counterblow' )
	{
		$part3['text'] .= ' ' . $zone . ' ' . $msg3['counterblow'][1][$n3['counterblow'][1]] . '.';
	}
	elseif( $type == 'critical_hit' )
	{
		$part3['text'] .= ' <font color="' . $hit_color . '"><b>-' . $hit . '</b></font> [' . intval($pl2_userdata['user_current_hp']) . '/' . intval($pl2_userdata['user_max_hp']) . ']';
	}
	// ----------

	if( !empty($hit) )
	{
		//
		// Удар (любой)
		//
		$msg = '<font class="' . $time_color . '">' . gmdate('H:i', time() + (3600 * 3)) . '</font> <font class="B' . $login2_color . '">' . $pl2_userdata['user_login'] . '</font> ' . $part1and2 . ' <font class="B' . $login_color . '">' . $userdata['user_login'] . '</font>' . $part3['text'] . '<br>';
		// ----------
	}
	else
	{
		//
		// Блок / уворот
		//
		$msg = '<font class="' . $time_color . '">' . gmdate('H:i', time() + (3600 * 3)) . '</font> <font class="B' . $login_color . '">' . $userdata['user_login'] . '</font> ' . $part1and2 . ' <font class="B' . $login2_color . '">' . $pl2_userdata['user_login'] . '</font> ' . $part3['text'] . '<br>';
		// ----------
	}

	$sql = "INSERT INTO " . LOGS_TEXT_TABLE . " " . $db->sql_build_array('INSERT', array(
		'log_id'		=> $userdata['user_battle_id'],
		'log_text'	=> $msg));
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу вставить запись в лог боя...', '', __LINE__, __FILE__, $sql);
	}

	if( empty($hit) )
	{
		$pl2_userdata['hit'] = 0;
	}

	return $pl2_userdata;
}
//
// ---------------

?>