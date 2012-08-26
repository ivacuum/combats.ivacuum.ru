<?php
/**
*
* @package combats
* @copyright (c) 2009 V@cuum
*
*/

// Время удара в формате ЧЧ:СС
$hit_time = date('H:i', time());
// ----------

//
// Получаем данные об ударе и блоке первого персонажа
//
$userdata['attack0'] = ( isset($_POST['attack0']) ) ? intval($_POST['attack0']) : '';
$userdata['attack1'] = ( isset($_POST['attack1']) && $userdata['user_attacks'] >= 2 ) ? intval($_POST['attack1']) : '';
$userdata['attack2'] = ( isset($_POST['attack2']) && $userdata['user_attacks'] >= 3 ) ? intval($_POST['attack2']) : '';
$userdata['attack3'] = ( isset($_POST['attack3']) && $userdata['user_attacks'] >= 4 ) ? intval($_POST['attack3']) : '';
$userdata['attack4'] = ( isset($_POST['attack4']) && $userdata['user_attacks'] >= 5 ) ? intval($_POST['attack4']) : '';
$userdata['attack5'] = ( isset($_POST['attack5']) && $userdata['user_attacks'] >= 6 ) ? intval($_POST['attack5']) : '';
$userdata['defend'] = ( isset($_POST['defend']) ) ? intval($_POST['defend']) : '';

// Для быстрых поединков
if( $config['fast_game'] )
{
	$userdata['attack0'] = mt_rand(1, 5);
	$userdata['attack1'] = ( $userdata['user_attacks'] >= 2 ) ? mt_rand(1, 5) : '';
	$userdata['attack2'] = ( $userdata['user_attacks'] >= 3 ) ? mt_rand(1, 5) : '';
	$userdata['attack3'] = ( $userdata['user_attacks'] >= 4 ) ? 3 : '';
	$userdata['attack4'] = ( $userdata['user_attacks'] >= 5 ) ? 5 : '';
	$userdata['defend'] = mt_rand(1, 5);
}
// ----------

/*$sql = '
	SELECT
		*
	FROM
		' . LOGS_HITS_TABLE . '
	WHERE
		hit_log_id = ' . $userdata['user_battle_id'] . '
	AND
		hit_attacker_id = ' . $userdata['user_id'] . '
	AND
		hit_victim_id = ' . $pl2_userdata['user_id'];
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if( $row )
{
	redirect(append_sid('battle.pl'));
}*/

$sql = '
	SELECT
		*
	FROM
		' . LOGS_HITS_TABLE . '
	WHERE
		hit_log_id = ' . $userdata['user_battle_id'] . '
	AND
		hit_attacker_id = ' . $pl2_userdata['user_id'] . '
	AND
		hit_victim_id = ' . $userdata['user_id'] . '
	AND
		hit_complete = 0';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if( !$row )
{
	$my_attacks = $userdata['attack0'];

	for( $i = 1; $i < $userdata['user_attacks']; $i++ )
	{
		$my_attacks .= ',' . $userdata['attack' . $i];
	}

	$sql = 'INSERT INTO ' . LOGS_HITS_TABLE . ' ' .
		$db->sql_build_array('INSERT', array(
			'hit_log_id'		=> $userdata['user_battle_id'],
			'hit_attacker_id'	=> $userdata['user_id'],
			'hit_victim_id'		=> $pl2_userdata['user_id'],
			'hit_time'			=> time(),
			'hit_attack'		=> $my_attacks,
			'hit_block'			=> $userdata['defend'],
			'hit_complete'		=> 0)
		);
	$db->sql_query($sql);
	redirect(append_sid('battle.pl'));
}

$sql = '
	UPDATE
		' . LOGS_HITS_TABLE . '
	SET
		hit_complete = 1
	WHERE
		hit_log_id = ' . $userdata['user_battle_id'] . '
	AND
		hit_attacker_id = ' . $pl2_userdata['user_id'] . '
	AND
		hit_victim_id = ' . $userdata['user_id'] . '
	AND
		hit_complete = 0';
$db->sql_query($sql);

// Атака и защита второго персонажа случайны
$pl2_userdata['attack0'] = mt_rand(1, 5);
$pl2_userdata['attack1'] = ( $pl2_userdata['user_attacks'] >= 2 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack2'] = ( $pl2_userdata['user_attacks'] >= 3 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack3'] = ( $pl2_userdata['user_attacks'] >= 4 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack4'] = ( $pl2_userdata['user_attacks'] >= 5 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack5'] = ( $pl2_userdata['user_attacks'] >= 6 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack6'] = ( $pl2_userdata['user_attacks'] >= 7 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack7'] = ( $pl2_userdata['user_attacks'] >= 8 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack8'] = ( $pl2_userdata['user_attacks'] >= 9 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack9'] = ( $pl2_userdata['user_attacks'] >= 10 ) ? mt_rand(1, 5) : '';
$pl2_userdata['attack10'] = ( $pl2_userdata['user_attacks'] >= 11 ) ? mt_rand(1, 5) : '';
$pl2_userdata['defend'] = mt_rand(1, 5);
// ----------

$enemy_attacks = explode(',', $row['hit_attack']);
$pl2_userdata['defend'] = $row['hit_block'];
//$enemy_blocks = explode(',', $row['hit_block']);

for( $i = 0, $len = count($enemy_attacks); $i < $len; $i++ )
{
	$pl2_userdata['attack' . $i] = $enemy_attacks[$i];
}

/*for( $i = 0, $len = count($enemy_blocks); $i < $len; $i++ )
{
	$pl2_userdata['defend'] = $enemy_blocks[$i];
}*/

// Настраиваем генератор случайных чисел на новую частоту
mt_srand(time() + (double)microtime() * 1000000);
// ----------

// Загружаем фразы
include($root_path . 'includes/battle_msg.php');

$userdata['overall_damage'] = 0;
$userdata['user_attacks_original'] = $userdata['user_attacks'];
$pl2_userdata['overall_damage'] = 0;
$pl2_userdata['user_attacks_original'] = $pl2_userdata['user_attacks'];

$max_attacks = ( $userdata['user_attacks'] > $pl2_userdata['user_attacks'] ) ? $userdata['user_attacks'] : $pl2_userdata['user_attacks'];

// Бонусы
$user->obtain_bonuses($userdata);
$user->obtain_bonuses($pl2_userdata);

for( $i = 0; $i < $max_attacks; $i++ )
{
	$userdata['attack_n'] = $i;
	$userdata['attack_type'] = '';
	$pl2_userdata['attack_n'] = $i;
	$pl2_userdata['attack_type'] = '';

	$user->get_mf_chance($userdata, $pl2_userdata, $items);
	$user->get_mf_chance($pl2_userdata, $userdata, $pl2_items);

	if( $userdata['attack' . $i] != '' )
	{
		switch( $userdata['attack' . $i] )
		{
			case 1: $block1 = 5; $block2 = 1; $block3 = ( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' ) ? 4 : 1; break;
			case 2: $block1 = 1; $block2 = 2; $block3 = ( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' ) ? 5 : 2; break;
			case 3: $block1 = 2; $block2 = 3; $block3 = ( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' ) ? 1 : 3; break;
			case 4: $block1 = 3; $block2 = 4; $block3 = ( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' ) ? 2 : 4; break;
			case 5: $block1 = 4; $block2 = 5; $block3 = ( $pl2_userdata['user_w3'] != $pl2_userdata['user_w10'] && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' ) ? 3 : 5; break;
		}

		if( in_array('multi_cowardshift', $userdata['specials']) )
		{
			// Коварный уход
			$user->hit($pl2_userdata, $pl2_userdata, $pl2_items, 'critical_hit');
			$user->delete_special($userdata, 'multi_cowardshift');
		}
		elseif( $pl2_userdata['dodging'] > 100 && $pl2_userdata['counterblow'] > 100 )
		{
			// Контрудар
			$user->hit($userdata, $pl2_userdata, $items, 'counterblow');
			$max_attacks += ( $max_attacks == $pl2_userdata['user_attacks'] ) ? 1 : 0;
			$pl2_userdata['user_attacks'] += 1;
			$pl2_userdata['attack' . ( $pl2_userdata['user_attacks'] - 1 )] = $userdata['attack' . $i];
		}
		elseif( $pl2_userdata['dodging'] > 100 && $pl2_userdata['counterblow'] < 100 )
		{
			// Уворот
			$user->hit($userdata, $pl2_userdata, $items, 'dodging');
		}
		elseif( $userdata['critical_hit'] > 100 && $pl2_userdata['shield_block'] > 100 && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' )
		{
			// Крит, пробив блок щита
			$user->hit($userdata, $pl2_userdata, $items, 'critical_hit_in_shield_block');
		}
		elseif( $userdata['critical_hit'] < 100 && $pl2_userdata['shield_block'] > 100 && $pl2_userdata['user_w10'] > 0 && $pl2_items['item_type'][10] == 'shield' )
		{
			// Блок щитом
			$user->hit($userdata, $pl2_userdata, $items, 'shield_block');
		}
		elseif( $userdata['critical_hit'] > 100 && $pl2_userdata['parry'] > 100 )
		{
			// Крит, пробив защиту
			$user->hit($userdata, $pl2_userdata, $items, 'critical_hit_in_parry');
		}
		elseif( $userdata['critical_hit'] < 100 && $pl2_userdata['parry'] > 100 )
		{
			// Парирование
			$user->hit($userdata, $pl2_userdata, $items, 'parry');
		}
		elseif( $userdata['critical_hit'] > 100 && ( $pl2_userdata['defend'] == $block1 || $pl2_userdata['defend'] == $block2 || $pl2_userdata['defend'] == $block3 ) )
		{
			// Крит, пробив блок
			$user->hit($userdata, $pl2_userdata, $items, 'critical_hit_in_block');
		}
		elseif( $userdata['critical_hit'] > 100 && $pl2_userdata['defend'] != $block1 && $pl2_userdata['defend'] != $block2 && $pl2_userdata['defend'] != $block3 )
		{
			// Крит
			$user->hit($userdata, $pl2_userdata, $items, 'critical_hit');
		}
		elseif( $pl2_userdata['defend'] == $block1 || $pl2_userdata['defend'] == $block2 || $pl2_userdata['defend'] == $block3 )
		{
			// Блок
			$user->hit($userdata, $pl2_userdata, $items, 'block');
		}
		elseif( $pl2_userdata['defend'] != $block1 && $pl2_userdata['defend'] != $block2 && $pl2_userdata['defend'] != $block3 )
		{
			// Удар
			$user->hit($userdata, $pl2_userdata, $items, 'attack');
		}
	}

	if( $pl2_userdata['attack' . $i] != '' )
	{
		switch( $pl2_userdata['attack' . $i] )
		{
			case 1: $block1 = 1; $block2 = 5; $block3 = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 4 : 5; break;
			case 2: $block1 = 1; $block2 = 2; $block3 = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 5 : 2; break;
			case 3: $block1 = 2; $block2 = 3; $block3 = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 1 : 3; break;
			case 4: $block1 = 3; $block2 = 4; $block3 = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 2 : 4; break;
			case 5: $block1 = 4; $block2 = 5; $block3 = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' ) ? 3 : 5; break;
		}

		if( $userdata['dodging'] > 100 && $userdata['counterblow'] > 100 )
		{
			// Контрудар
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'counterblow');
			$max_attacks += ( $max_attacks == $userdata['user_attacks'] ) ? 1 : 0;
			$userdata['user_attacks'] += 1;
			$userdata['attack' . ( $userdata['user_attacks'] - 1 )] = $userdata['attack' . $i];
		}
		elseif( $userdata['dodging'] > 100 && $userdata['counterblow'] < 100 )
		{
			// Уворот
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'dodging');
		}
		elseif( $pl2_userdata['critical_hit'] > 100 && $userdata['shield_block'] > 100 && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' )
		{
			// Крит, пробив блок щита
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'critical_hit_in_shield_block');
		}
		elseif( $pl2_userdata['critical_hit'] < 100 && $userdata['shield_block'] > 100 && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' )
		{
			// Блок щитом
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'shield_block');
		}
		elseif( $pl2_userdata['critical_hit'] > 100 && $userdata['parry'] > 100 )
		{
			// Крит, пробив защиту
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'critical_hit_in_parry');
		}
		elseif( $pl2_userdata['critical_hit'] < 100 && $userdata['parry'] > 100 )
		{
			// Парирование
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'parry');
		}
		elseif( $pl2_userdata['critical_hit'] > 100 && ( $userdata['defend'] == $block1 || $userdata['defend'] == $block2 || $userdata['defend'] == $block3 ) )
		{
			// Крит, пробив блок
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'critical_hit_in_block');
		}
		elseif( $pl2_userdata['critical_hit'] > 100 && $userdata['defend'] != $block1 && $userdata['defend'] != $block2 && $userdata['defend'] != $block3 )
		{
			// Крит
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'critical_hit');
		}
		elseif( $userdata['defend'] == $block1 || $userdata['defend'] == $block2 || $userdata['defend'] == $block3 )
		{
			// Блок
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'block');
		}
		elseif( $userdata['defend'] != $block1 && $userdata['defend'] != $block2 && $userdata['defend'] != $block3 )
		{
			// Удар
			$user->hit($pl2_userdata, $userdata, $pl2_items, 'attack');
		}
	}

	$user->magic_hit($userdata, $pl2_userdata, $items);
	$user->magic_hit($pl2_userdata, $userdata, $pl2_items);
}

$userdata['user_attacks'] = $userdata['user_attacks_original'];
$pl2_userdata['user_attacks'] = $pl2_userdata['user_attacks_original'];

if( $userdata['overall_damage'] > 0 || $pl2_userdata['overall_damage'] > 0 )
{
	//
	// Первый персонаж
	//
	$userdata['user_current_hp'] = ( $userdata['user_current_hp'] <= 0 ) ? 0 : $userdata['user_current_hp'];

	$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . $userdata['user_current_hp'] . ", user_hit_hp = ( user_hit_hp + " . $userdata['overall_damage'] . " ) WHERE `user_id` = " . $userdata['user_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	//
	// Второй персонаж
	//
	$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . ( ( $pl2_userdata['user_current_hp'] <= 0 ) ? 0 : $pl2_userdata['user_current_hp']) . ", user_hit_hp = ( user_hit_hp + " . $pl2_userdata['overall_damage'] . " ) WHERE `user_id` = " . $pl2_userdata['user_id'];
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}
	// ----------
}


//
// Сообщение о смерти
//
$death_message[] = 'мертв';
$death_message[] = 'повержен';
$death_message[] = 'проиграл бой';
$death_message[] = 'убит';

if( $userdata['user_current_hp'] <= 0 )
{
	$user->add_log_message($userdata['user_battle_id'], '<font class="sysdate">' . date('H:i', time()) . '</font> <b>' . $userdata['user_login'] . '</b> ' . $death_message[mt_rand(0, count($death_message) - 1)] . '<br />');
}

if( $pl2_userdata['user_current_hp'] <= 0 )
{
	$user->add_log_message($userdata['user_battle_id'], '<font class="sysdate">' . date('H:i', time()) . '</font> <b>' . $pl2_userdata['user_login'] . '</b> ' . $death_message[mt_rand(0, count($death_message) - 1)] . '<br />');
}
// ----------

$user->add_log_message($userdata['user_battle_id'], '<script>dv();</script>');

?>