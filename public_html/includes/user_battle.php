<?php

class user_battle extends user
{
	// ---------------
	// Поломка вещей
	//
	function damage_items($n, &$userdata, &$items)
	{
		global $db, $user;

		// Определяем шанс поломки
		$chance = mt_rand(1, 100);
		$chance = ( $userdata['user_w3'] == $userdata['user_w10'] && $n == 10 ) ? 0 : $chance;
		$chance -= ( $chance > 0 && $userdata['user_w' . $n] > 0 && $items['item_artefact'][$n] == 1 ) ? 20 : 0;

		if( $userdata['user_w' . $n] > 0 && $chance > 50 )
		{
			$items['item_current_durability'][$n] += 1;

			if( $items['item_current_durability'][$n] >= $items['item_max_durability'][$n] )
			{
				// Удаление вещи (вещь сломалась)
				$user->item_setdown($n, $userdata);
				$user->item_drop($items['item_id'][$n], $userdata);

				// Запись в личное дело
				$user->add_hidden_log_message($userdata['user_id'], '1.9', 'fulldamaged', 'У ' . $user->drwfl($userdata) . ' предмет "' . $items['item_name'][$n] . '" пришел в полную негодность');

				// Сообщаем о поломке
				$user->add_log_message($userdata['user_battle_id'], '<br />Внимание! У "' . $userdata['user_login'] . '" предмет "' . $items['item_name'][$n] . '" пришел в полную негодность!');
			}
			else
			{
				// Ломаем вещь
				$sql = "UPDATE " . ITEMS_TABLE . " SET item_current_durability = (item_current_durability + 1) WHERE `item_id` = " . $items['item_id'][$n];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
				}

				if( $items['item_current_durability'][$n] >= ( $items['item_max_durability'][$n] - 2 ) )
				{
					// Сообщаем о критическом состоянии
					$user->add_log_message($userdata['user_battle_id'], '<br />Внимание! У "' . $userdata['user_login'] . '" предмет "' . $items['item_name'][$n] . '" в критическом состоянии!<br /><small>(на правах рекламы) <b>Мастерская Бойцовского клуба</b>. Мы даем вторую жизнь старым вещам!</small><br />');

					if( $userdata['user_bot'] == 1 )
					{
						// Полностью чиним вещь (только ботам)
						$damage = $user->item_repair($userdata['user_id'], $items['item_id'][$n], $items['item_current_durability'][$n], true);

						// Обновляем параметры предмета
						$items['item_current_durability'][$n] = 0;
						$items['item_max_durability'][$n] -= $damage;
					}
				}
			}
		}
	}
	//
	// ---------------

	// ---------------
	// Удаление использованного спецприема
	function delete_special(&$userdata, $special)
	{
		global $db, $user;

		//
		// Удаляем спецприем из массива
		//
		foreach( $userdata['specials'] as $special_name => $special_value )
		{
			if( $special_value == $special )
			{
				// Удаляем переменную
				unset($userdata['specials'][$special_name]);
			}
		}
		// ----------

		// Сортируем массив
		sort($userdata['specials']);

		$specials_list = '';

		//
		// Создаем новый список спецприемов
		//
		for( $i = 0; $i < count($userdata['specials']); $i++ )
		{
			$specials_list .= $userdata['specials'][$i] . ',';
		}
		// ----------

		// Удаляем спецприем
		$sql = "UPDATE " . USERS_TABLE . " SET user_special = '" . substr($specials_list, 0, -1) . "' WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу удалить использованный спецприем...', '', __LINE__, __FILE__, $sql);
		}
	}
	//
	// ---------------

	// ---------------
	// Расчет получаемого опыта
	//
	function get_gained_experience($battle_team, $hit_hp, $items_cost, $level, $team)
	{
		//
		// Заранее определяем переменные
		//
		$enemy_team = ( $battle_team == 1 ) ? 2 : 1;
		$userdata['gain_exp'] = 0;
		// ----------

		//
		// Базовый опыт
		//
		$experience[0] = 5;
		$experience[1] = 10;
		$experience[2] = 20;
		$experience[3] = 30;
		$experience[4] = 60;
		$experience[5] = 120;
		$experience[6] = 180;
		$experience[7] = 300;
		$experience[8] = 600;
		$experience[9] = 1200;
		$experience[10] = 2400;
		$experience[11] = 4800;
		$experience[12] = 9000;
		$experience[13] = 15000;
		$experience[14] = 25000;
		$experience[15] = 35000;
		$experience[16] = 45000;
		$experience[17] = 55000;
		$experience[18] = 65000;
		$experience[19] = 80000;
		$experience[20] = 90000;
		$experience[21] = 100000;

		if( $team[$battle_team]['level'] == $team[$enemy_team]['level'] )
		{
			$userdata['gain_exp'] = $experience[$level];
		}
		elseif( $team[$battle_team]['level'] > $team[$enemy_team]['level'] || $team[$battle_team]['level'] < $team[$enemy_team]['level'] )
		{
			$userdata['gain_exp'] = $experience[$team[$enemy_team]['level']];
		}
		// ----------

		//
		// Определяем количество получаемого опыта
		//
		if( $team[$battle_team]['items_cost'] > 2 && $team[$enemy_team]['items_cost'] < 2 )
		{
			// Игрок одет, а противник нет
			$userdata['gain_exp'] = ( $userdata['gain_exp'] / 2 ) - round($hit_hp / 10) - round($team[$battle_team]['items_cost'] / 10);
		}
		elseif( $team[$battle_team]['items_cost'] < 2 && $team[$enemy_team]['items_cost'] > 2 )
		{
			// Противник одет, а игрок нет
			$userdata['gain_exp'] = ( $userdata['gain_exp'] * 2 ) + round($hit_hp / 10) - round($team[$enemy_team]['items_cost'] / 10);
		}
		elseif( $team[$battle_team]['items_cost'] > 2 && $team[$enemy_team]['items_cost'] > 2 )
		{
			// Оба одеты
			$userdata['gain_exp'] += round(($team[$enemy_team]['items_cost'] - $team[$battle_team]['items_cost']) / 10) + round($hit_hp / 10);
		}
		// ----------

		// Добавка к опыту от стоимости вещей
		$userdata['gain_exp'] *= ( $items_cost > 1500 ) ? 3 : ( ( $items_cost > 700 ) ? 2 : ( ( $items_cost > 350 ) ? 1.5 : 1 ));

		// Добавка к опыту от склонности
		$userdata['gain_exp'] *= ( ( $team[$battle_team]['user_align'] >= 1 && $team[$battle_team]['user_align'] < 2 && $team[$enemy_team]['user_align'] >= 3 && $team[$enemy_team]['user_align'] < 4 ) || ( $team[$battle_team]['user_align'] >= 3 && $team[$battle_team]['user_align'] < 4 && $team[$enemy_team]['user_align'] >= 1 && $team[$enemy_team]['user_align'] < 2 ) ) ? 1.5 : 1;

		// Разброс
		$userdata['gain_exp'] = ( $level >= 2 ) ? mt_rand(round($userdata['gain_exp'] / 1.2), round($userdata['gain_exp'] * 2)) : round($userdata['gain_exp']);

		// Увеличение/уменьшение (зависит от уровней команд)
		$userdata['gain_exp'] = ( $team[$battle_team]['level'] > $team[$enemy_team]['level'] ) ? round($userdata['gain_exp'] / ( $team[$battle_team]['level'] - $team[$enemy_team]['level'] + 1 )) : ( ( $team[$battle_team]['level'] < $team[$enemy_team]['level'] ) ? round($userdata['gain_exp'] * ( $team[$enemy_team]['level'] - $team[$battle_team]['level'] + 1 )) : $userdata['gain_exp']);

		// Увеличение/уменьшение (зависит от численности команд)
		$userdata['gain_exp'] *= ( intval(count($team[$enemy_team]['user_id']) / count($team[$battle_team]['user_id'])) > 0 ) ? intval(count($team[$enemy_team]['user_id']) / count($team[$battle_team]['user_id'])) : 1;

//		$userdata['gain_exp'] = round($userdata['gain_exp'] * ( 1 + ( $team[$battle_team]['user_increase_experience'][0] / 100 ) ) );

		// Проверка
		$userdata['gain_exp'] = ( $userdata['gain_exp'] < 0 && $level >= 18 ) ? 0 : $userdata['gain_exp'];
		$userdata['gain_exp'] = ( $userdata['gain_exp'] == '' || !$userdata['gain_exp'] || $hit_hp == 0 ) ? 0 : $userdata['gain_exp'];

		return $userdata['gain_exp'];
	}
	//
	// ---------------

	// ---------------
	// Определение шансов крита и т.д.
	//
	function get_mf_chance(&$userdata, $pl2_userdata, $items)
	{
		global $user;

		// ---------------
		// Критический удар
		//
		$userdata['min_critical_hit'] = ( ( ( $userdata['user_perception'] * 2 ) + $userdata['user_mf_critical_hit'] ) - ( ( $pl2_userdata['user_perception'] * 2 ) + $pl2_userdata['user_mf_anticritical_hit'] ) ) / 5;
		$userdata['max_critical_hit'] = $userdata['min_critical_hit'] + 110;

		$userdata['min_critical_hit'] = ( $userdata['min_critical_hit'] < 1 ) ? 1 : ( ( $userdata['min_critical_hit'] > 75 ) ? 75 : $userdata['min_critical_hit']);
		$userdata['max_critical_hit'] = ( $userdata['max_critical_hit'] < 101 ) ? 101 : ( ( $userdata['max_critical_hit'] > 200 ) ? 200 : $userdata['max_critical_hit']);

		$userdata['critical_hit'] = ( in_array('krit_blindluck', $userdata['specials']) || in_array('multi_hiddenpower', $userdata['specials']) ) ? 200 : mt_rand($userdata['min_critical_hit'], $userdata['max_critical_hit']);

//		print 'Шанс крит. удара: ' . $userdata['min_critical_hit'] . ' - ' . $userdata['max_critical_hit'] . ' = ' . $userdata['critical_hit'] . '<br />';

		// Используем спецприем
		if( in_array('krit_blindluck', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'krit_blindluck');
		}
		elseif( in_array('multi_hiddenpower', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'multi_hiddenpower');
		}
		//
		// ---------------

		// ---------------
		// Уворот
		//
		$userdata['min_dodging'] = ( ( ( $userdata['user_agility'] * 5 ) + ( $userdata['user_perception'] * 3 ) + $userdata['user_mf_dodging'] ) - ( ( $pl2_userdata['user_agility'] * 5 ) + ( $pl2_userdata['user_perception'] * 3 ) + $pl2_userdata['user_mf_antidodging'] ) ) / 5;
		$userdata['max_dodging'] = $userdata['min_dodging'] + 110;

		$userdata['min_dodging'] = ( $userdata['min_dodging'] < 1 ) ? 1 : ( ( $userdata['min_dodging'] > 75 ) ? 75 : $userdata['min_dodging']);
		$userdata['max_dodging'] = ( $userdata['max_dodging'] < 101 ) ? 101 : ( ( $userdata['max_dodging'] > 200 ) ? 200 : $userdata['max_dodging']);

		$userdata['dodging'] = ( in_array('counter_winddance', $userdata['specials']) || in_array('counter_bladedance', $userdata['specials']) || in_array('multi_hiddendodge', $userdata['specials']) ) ? 200 : mt_rand($userdata['min_dodging'], $userdata['max_dodging']);

//		print 'Шанс увертывания: ' . $userdata['min_dodging'] . ' - ' . $userdata['max_dodging'] . ' = ' . $userdata['dodging'] . '<br />';

		// Используем спецприем
		if( in_array('counter_winddance', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'counter_winddance');
		}
		//
		// ---------------

		// ---------------
		// Контрудар
		//
		$userdata['min_counterblow'] = 20 + $userdata['user_mf_counterblow'];
		$userdata['max_counterblow'] = $userdata['min_counterblow'] + 110;

		$userdata['min_counterblow'] = ( $userdata['min_counterblow'] < 1 ) ? 1 : ( ( $userdata['min_counterblow'] > 75 ) ? 75 : $userdata['min_counterblow']);
		$userdata['max_counterblow'] = ( $userdata['max_counterblow'] < 101 ) ? 101 : ( ( $userdata['max_counterblow'] > 200 ) ? 200 : $userdata['max_counterblow']);

		$userdata['counterblow'] = ( in_array('counter_bladedance', $userdata['specials']) || in_array('multi_hiddendodge', $userdata['specials']) ) ? 200 : mt_rand($userdata['min_counterblow'], $userdata['max_counterblow']);

//		print 'Шанс контрудара: ' . $userdata['min_counterblow'] . ' - ' . $userdata['max_counterblow'] . ' = ' . $userdata['counterblow'] . '<br />';

		// Используем спецприем
		if( in_array('counter_bladedance', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'counter_bladedance');
		}
		elseif( in_array('multi_hiddendodge', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'multi_hiddendodge');
		}
		//
		// ---------------

		// ---------------
		// Блок щитом
		//
		$userdata['min_shield_block'] = $userdata['user_mf_shield_block'];
		$userdata['max_shield_block'] = $userdata['min_shield_block'] + 110;

		$userdata['min_shield_block'] = ( $userdata['min_shield_block'] < 1 ) ? 1 : ( ( $userdata['min_shield_block'] > 75 ) ? 75 : $userdata['min_shield_block']);
		$userdata['max_shield_block'] = ( $userdata['max_shield_block'] < 101 ) ? 101 : ( ( $userdata['max_shield_block'] > 200 ) ? 200 : $userdata['max_shield_block']);

		$userdata['shield_block'] = ( isset($items['item_type'][10]) && $items['item_type'][10] == 'shield' ) ? mt_rand($userdata['min_shield_block'], $userdata['max_shield_block']) : 0;

//		print 'Шанс блока щитом: ' . $userdata['min_shield_block'] . ' - ' . $userdata['max_shield_block'] . ' = ' . $userdata['shield_block'] . '<br />';
		//
		// ---------------

		// ---------------
		// Парирование
		//
		$userdata['min_parry'] = $userdata['user_mf_parry'] + ( ( $userdata['user_knifes'] + $userdata['user_axes'] + $userdata['user_clubs'] + $userdata['user_swords'] + $userdata['user_staffs'] ) / 2 ) - $pl2_userdata['user_mf_parry'] - ( ( $pl2_userdata['user_knifes'] + $pl2_userdata['user_axes'] + $pl2_userdata['user_clubs'] + $pl2_userdata['user_swords'] + $pl2_userdata['user_staffs'] ) / 2 );
		$userdata['max_parry'] = $userdata['min_parry'] + 110;

		$userdata['min_parry'] = ( $userdata['min_parry'] < 1 ) ? 1 : ( ( $userdata['min_parry'] > 75 ) ? 75 : $userdata['min_parry']);
		$userdata['max_parry'] = ( $userdata['max_parry'] < 101 ) ? 101 : ( ( $userdata['max_parry'] > 200 ) ? 200 : $userdata['max_parry']);

		$userdata['parry'] = ( in_array('parry_prediction', $userdata['specials']) || in_array('parry_secondlife', $userdata['specials']) ) ? 200 : mt_rand($userdata['min_parry'], $userdata['max_parry']);

//		print 'Шанс парирования: ' . $userdata['min_parry'] . ' - ' . $userdata['max_parry'] . ' = ' . $userdata['parry'] . '<br />';

		// Используем спецприем
		if( in_array('parry_prediction', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'parry_prediction');
		}
		//
		// ---------------

		// ---------------
		// Удар сквозь броню
		//
		$userdata['min_hit_through_armour'] = $userdata['user_mf_hit_through_armour'];
		$userdata['max_hit_through_armour'] = $userdata['min_hit_through_armour'] + 110;

		$userdata['min_hit_through_armour'] = ( $userdata['min_hit_through_armour'] < 1 ) ? 1 : ( ( $userdata['min_hit_through_armour'] > 75 ) ? 75 : $userdata['min_hit_through_armour']);
		$userdata['max_hit_through_armour'] = ( $userdata['max_hit_through_armour'] < 101 ) ? 101 : ( ( $userdata['max_hit_through_armour'] > 200 ) ? 200 : $userdata['max_hit_through_armour']);

		$userdata['hit_through_armour'] = ( in_array('multi_skiparmor', $userdata['specials']) ) ? 200 : mt_rand($userdata['min_hit_through_armour'], $userdata['max_hit_through_armour']);

//		print 'Шанс удара сквозь броню: ' . $userdata['min_hit_through_armour'] . ' - ' . $userdata['max_hit_through_armour'] . ' = ' . $userdata['hit_through_armour'] . '<br />';

		// Используем спецприём
		if( in_array('multi_skiparmor', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'multi_skiparmor');
		}
		//
		// ---------------
	}
	//
	// ---------------

	// ---------------
	// Удар
	//
	function hit(&$userdata, &$pl2_userdata, $items, $type)
	{
		global $db, $msg1, $msg2, $msg3, $user;

		//
		// Определяем зону удара
		//
		switch( $userdata['attack' . $userdata['attack_n']] )
		{
			case 1:		$hit_zone = 'head'; break;
			case 2:		$hit_zone = 'body'; break;
			case 3:		$hit_zone = 'body'; break;
			case 4:		$hit_zone = 'waist'; break;
			case 5:		$hit_zone = 'leg'; break;
		}

		$zone = ( $userdata['attack' . $userdata['attack_n']] == 3 ) ? 'belly' : $hit_zone;
		// ----------

		//
		// В случае удара
		//
		if( $type == 'attack' || $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' )
		{
			$userdata['hit_type'] = $type;

			//
			// Расчет силы удара
			//
			if( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] != 'shield' && $userdata['attack_n'] == 1 )
			{
				$user->hit_power($userdata, $pl2_userdata, $items, $hit_zone, true);
			}
			else
			{
				$user->hit_power($userdata, $pl2_userdata, $items, $hit_zone);
			}
			// ----------
		}
		// ----------

		//
		// Оружие, которым наносится удар
		//
		$weapon_type = ( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] != 'shield' && $userdata['attack_n'] == 1 ) ? $items['item_type'][10] : ( ( $userdata['user_w3'] > 0 ) ? $items['item_type'][3] : 'hand');
		// ----------

		//
		// Генератор фраз
		//
		$n1 = mt_rand(0, count($msg1) - 1);
		$n2[1] = mt_rand(0, count($msg2[1]) - 1);
		$n2[2] = mt_rand(0, count($msg2[2]) - 1);
		$n3['weapon'] = mt_rand(0, count($msg3['w_' . $weapon_type]) - 1);

		if( $type == 'critical_hit' )
		{
			$n3['critical_hit'][1] = mt_rand(0, count($msg3['critical_hit'][1]) - 1);
			$n3['critical_hit'][2] = mt_rand(0, count($msg3['critical_hit'][2]) - 1);
			$n3['critical_hit'][3] = mt_rand(0, count($msg3['critical_hit'][3]) - 1);
		}

		$n3['attack'][1] = mt_rand(0, count($msg3['attack'][1]) - 1);
		$n3['attack'][2] = mt_rand(0, count($msg3['attack'][2]) - 1);
		$n3['attack'][3] = mt_rand(0, count($msg3['attack'][3]) - 1);
		$n3['attack'][4] = mt_rand(0, count($msg3['attack'][4]) - 1);
		$n3['attack'][5] = mt_rand(0, count($msg3['attack'][5]) - 1);
		$n3['attack'][6] = mt_rand(0, count($msg3['attack'][6]) - 1);
		$n3['attack'][7] = mt_rand(0, count($msg3['attack'][7]) - 1);
		$n3['attack'][8] = mt_rand(0, count($msg3['attack'][8]) - 1);
		$n3['attack'][$userdata['attack_type']] = ( $userdata['attack_type'] ) ? mt_rand(0, count($msg3['attack'][$userdata['attack_type']]) - 1) : '';
		$n3['block'][1] = mt_rand(0, count($msg3['block'][1]) - 1);
		$n3['block'][2] = mt_rand(0, count($msg3['block'][2]) - 1);
		$n3['counterblow'][1] = mt_rand(0, count($msg3['counterblow'][1]) - 1);
		$n3['critical_hit_in_block'] = mt_rand(0, count($msg3['critical_hit_in_block']) - 1);
		$n3['dodging'][1] = mt_rand(0, count($msg3['dodging'][1]) - 1);
		$n3['parry'][1] = mt_rand(0, count($msg3['parry'][1]) - 1);
		$n3['shield_block'][1] = mt_rand(0, count($msg3['shield_block'][1]) - 1);
		$n3[$zone] = mt_rand(0, count($msg3[$zone]) - 1);

		$part1and2 = $msg1[$n1] . $msg2[1][$n2[1]] . $msg2[2][$n2[2]];

		// Удар
		$part3['attack'] = ( $userdata['user_w3'] > 0 && $userdata['attack_type'] ) ? $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][3][$n3['attack'][3]] . ' ' . $msg3['attack'][$userdata['attack_type']][$n3['attack'][$userdata['attack_type']]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']] : $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Блок
		$part3['block'] = $msg3['block'][1][$n3['block'][1]] . ' ' . $msg3['block'][2][$n3['block'][2]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Контрудар / уворот
		$part3['counterblow'] = $msg3['dodging'][1][$n3['dodging'][1]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];
		$part3['dodging'] = $msg3['dodging'][1][$n3['dodging'][1]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Крит
		$part3['critical_hit'] = ( $type == 'critical_hit' ) ? $msg3['critical_hit'][1][$n3['critical_hit'][1]] . ' ' . $msg3['critical_hit'][2][$n3['critical_hit'][2]] . ' ' . $msg3['critical_hit'][3][$n3['critical_hit'][3]] : '';

		// Крит, пробив блок
		$part3['critical_hit_in_block'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['critical_hit_in_block'][$n3['critical_hit_in_block']] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . $msg3['attack'][5][$n3['attack'][5]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Крит, пробив защиту
		$part3['critical_hit_in_parry'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['critical_hit_in_block'][$n3['critical_hit_in_block']] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . $msg3['attack'][6][$n3['attack'][6]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Крит, пробив блок щита
		$part3['critical_hit_in_shield_block'] = $msg3['attack'][1][$n3['attack'][1]] . ' ' . $msg3['attack'][2][$n3['attack'][2]] . ' ' . $msg3['critical_hit_in_block'][$n3['critical_hit_in_block']] . ' ' . $msg3['attack'][4][$n3['attack'][4]] . $msg3['attack'][7][$n3['attack'][7]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Парирование
		$part3['parry'] = $msg3['parry'][1][$n3['parry'][1]] . ' ' . $msg3['block'][2][$n3['block'][2]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];

		// Блок щитом
		$part3['shield_block'] = $msg3['shield_block'][1][$n3['shield_block'][1]] . ' ' . $msg3['block'][2][$n3['block'][2]] . ' ' . $msg3['w_' . $weapon_type][$n3['weapon']];
		// ----------

		// Определяем некоторые переменные
		$hit_color = ( $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' ) ? '#FF0000' : '#006699';
		$part3['text'] = $part3[$type];
		$zone = $msg3[$zone][$n3[$zone]];

		//
		// Добавляем к 3 части сообщения концовку...
		// P.S. Для каждого случая разная...
		//
		if( $type == 'attack' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' )
		{
			$part3['text'] .= ' ' . $zone . ' ' . $msg3['attack'][8][$n3['attack'][8]] . '. <font color="' . $hit_color . '"><b>-' . $userdata['hit'] . '</b></font> [' . ( ( intval($pl2_userdata['user_current_hp']) <= 0 ) ? 0 : intval($pl2_userdata['user_current_hp']) ) . '/' . $pl2_userdata['user_max_hp'] . ']';
		}
		elseif( $type == 'block' || $type == 'dodging' || $type == 'parry' || $type == 'shield_block' )
		{
			$part3['text'] .= ' ' . $zone . '.';
		}
		elseif( $type == 'counterblow' )
		{
			$part3['text'] .= ' ' . $zone . ' ' . $msg3['counterblow'][1][$n3['counterblow'][1]] . '.';
		}
		elseif( $type == 'critical_hit' )
		{
			$part3['text'] .= ' <font color="' . $hit_color . '"><b>-' . $userdata['hit'] . '</b></font> [' . ( ( intval($pl2_userdata['user_current_hp']) <= 0 ) ? 0 : intval($pl2_userdata['user_current_hp']) ) . '/' . $pl2_userdata['user_max_hp'] . ']';
		}
		// ----------

		switch( $pl2_userdata['defend'] )
		{
			case 1: $blocks = '12'; break;
			case 2: $blocks = '23'; break;
			case 3: $blocks = '34'; break;
			case 4: $blocks = '45'; break;
			case 5: $blocks = '15'; break;
		}

		if( $type == 'attack' || $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' )
		{
			// Удар (любой)
			$msg = '<script>adh(' . $userdata['attack' . $userdata['attack_n']] . ',' . $blocks . ');</script><font class="date">' . date('H:i', time()) . '</font> </span> <span class="B' . $pl2_userdata['user_battle_team'] . '">' . $pl2_userdata['user_login'] . '</span> ' . $part1and2 . ' <span class="B' . $userdata['user_battle_team'] . '">' . $userdata['user_login'] . '</span>' . $part3['text'] . '<br>';
		}
		else
		{
			// Блок / уворот
			$msg = '<script>adh(' . $userdata['attack' . $userdata['attack_n']] . ',' . $blocks . ');</script><font class="date">' . date('H:i', time()) . '</font></span> <span class="B' . $userdata['user_battle_team'] . '">' . $userdata['user_login'] . '</span> ' . $part1and2 . ' <span class="B' . $pl2_userdata['user_battle_team'] . '">' . $pl2_userdata['user_login'] . '</span> ' . $part3['text'] . '<br>';
		}

		if( ( $type == 'block' || $type == 'counterblow' || $type == 'dodging' || $type == 'parry' || $type == 'shield_block' ) && $pl2_userdata['user_level'] >= 5 && ( $userdata['user_level'] - $pl2_userdata['user_level'] >= -1 ) )
		{
			$type = ( $type == 'dodging' ) ? 'block' : $type;
			$type = ( $type == 'shield_block' ) ? 'parry' : $type;

			$sql = "UPDATE " . USERS_TABLE . " SET user_count_" . $type . " = ( user_count_" . $type . " + 1 ) WHERE `user_id` = " . $pl2_userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}

			$pl2_userdata['user_count_' . $type] += 1;
		}
		elseif( ( $type == 'attack' || $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' ) && $userdata['user_level'] >= 5 && ( $pl2_userdata['user_level'] - $userdata['user_level'] >= -1 ) )
		{
			$type2 = ( $type == 'attack' ) ? 'hit' : '';
			$type2 = ( $type2 == '' && ( $type == 'critical_hit' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' ) ) ? 'critical_hit' : $type2;

			$sql = "UPDATE " . USERS_TABLE . " SET user_count_" . $type2 . " = ( user_count_" . $type2 . " + " . ( ( $type == 'attack' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' ) ? 1 : ( ( $type == 'critical_hit' ) ? 2 : 1 )) . " ) WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}

			$userdata['user_count_' . $type2] += ( $type == 'attack' || $type == 'critical_hit_in_block' || $type == 'critical_hit_in_parry' || $type == 'critical_hit_in_shield_block' ) ? 1 : ( ( $type == 'critical_hit' ) ? 2 : 1);
		}

		// Добавляем сообщение
		$user->add_log_message($userdata['user_battle_id'], $msg);

		//
		// Второе дыхание
		//
		if( in_array('parry_secondlife', $pl2_userdata['specials']) )
		{
			if( $pl2_userdata['user_current_hp'] > 0 )
			{
				// Обновляем переменные
				$pl2_userdata['user_current_hp'] += ( $userdata['user_level'] * 5 );
				$pl2_userdata['user_current_hp'] = ( $pl2_userdata['user_current_hp'] > $pl2_userdata['user_max_hp'] ) ? $pl2_userdata['user_max_hp'] : $pl2_userdata['user_current_hp'];

				// Запись в лог боя
				$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . date('H:i', time()) . '</font> <span class="B' . $userdata['user_battle_team'] . '">' . $pl2_userdata['user_login'] . '</span>, благодаря успешным защитным действиям, пополнил уровень жизни <font color="#006699"><b>+' . ( $userdata['user_level'] * 5 ) . '</b></font> [' . $pl2_userdata['user_current_hp'] . '/' . $pl2_userdata['user_max_hp'] . ']<br>');

				$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . $pl2_userdata['user_current_hp'] . " WHERE `user_id` = " . $pl2_userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}
			}

			$user->delete_special($pl2_userdata, 'parry_secondlife');
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Расчет силы удара
	//
	function hit_power(&$userdata, &$pl2_userdata, $items, $zone, $secondhand = false)
	{
		global $user;

		if( $userdata['user_strength'] < 0 )
		{
			$userdata['min_hit'] = 1;
			$userdata['max_hit'] = 2;
		}
		elseif( $userdata['user_strength'] == 0 || $userdata['user_strength'] == 1 )
		{
			$userdata['min_hit'] = 1;
			$userdata['max_hit'] = 5;
		}
		elseif( $userdata['user_strength'] >= 2 )
		{
			$hit_mod = round($userdata['user_strength'] / 2);

			$userdata['min_hit'] = 1 + $hit_mod;
			$userdata['max_hit'] = 5 + $hit_mod;
		}

		//
		// "Нейтральное усиление"
		//
		if( $userdata['user_align'] == 7 && !$userdata['user_w1'] && !$userdata['user_w2'] && !$userdata['user_w3'] && !$userdata['user_w4'] && !$userdata['user_w5'] && !$userdata['user_w6'] && !$userdata['user_w7'] && !$userdata['user_w8'] && !$userdata['user_w9'] && !$userdata['user_w10'] && !$userdata['user_w11'] && !$userdata['user_w12'] && !$userdata['user_w13'] && !$userdata['user_w14'] && !$userdata['user_w15'] )
		{
			$userdata['min_hit'] += $userdata['user_level'];
			$userdata['max_hit'] += $userdata['user_level'];
		}
		// ----------

		//
		// Если у персонажа есть оружие...
		//
		$weapon_slot = ( $secondhand ) ? 10 : 3;

		if( $userdata['user_w' . $weapon_slot] != 0 )
		{
			//
			// Прибавление к основному урону урона вещи и мастерства владения
			//
			$userdata['min_hit'] += $items['item_min_hit'][$weapon_slot] + $userdata['user_' . $items['item_type'][$weapon_slot] . 's'];
			$userdata['max_hit'] += $items['item_max_hit'][$weapon_slot] + ( $userdata['user_' . $items['item_type'][$weapon_slot] . 's'] * 2 );
			// ----------
		}
		// ----------

		//
		// Добавляем урон с вещей
		//

		// Шлем
		if( $userdata['user_w9'] > 0 )
		{
			$userdata['min_hit'] += $items['item_min_hit'][9];
			$userdata['max_hit'] += $items['item_max_hit'][9];
		}

		// Щит
		if( $userdata['user_w3'] != $userdata['user_w10'] && $userdata['user_w10'] > 0 && $items['item_type'][10] == 'shield' )
		{
			$userdata['min_hit'] += $items['item_min_hit'][10];
			$userdata['max_hit'] += $items['item_max_hit'][10];
		}

		// Перчатки
		if( $userdata['user_w11'] > 0 )
		{
			$userdata['min_hit'] += $items['item_min_hit'][11];
			$userdata['max_hit'] += $items['item_max_hit'][11];
		}
		
		// Наручи
		if( $userdata['user_w13'] > 0 )
		{
			$userdata['min_hit'] += $items['item_min_hit'][13];
			$userdata['max_hit'] += $items['item_max_hit'][13];
		}
		// ----------

		//
		// Сила удара персонажа
		//
		$userdata['hit'] = ( in_array('krit_wildluck', $userdata['specials']) ) ? $userdata['max_hit'] : mt_rand($userdata['min_hit'], $userdata['max_hit']);
		// ----------

		if( in_array('krit_wildluck', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'krit_wildluck');
		}

		// Добавляем урон от спецприема
		$userdata['hit'] += ( in_array('hit_luck', $userdata['specials']) ) ? $userdata['user_level'] * 4 : ( ( in_array('hit_strong', $userdata['specials']) ) ? $userdata['user_level'] * 2 : 0);

		//
		// Использование спецприемов
		//
		if( in_array('hit_luck', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'hit_luck');
		}
		elseif( in_array('hit_strong', $userdata['specials']) )
		{
			$user->delete_special($userdata, 'hit_strong');
		}
		// ----------

		$userdata['attacks'] = array();

		if( $userdata['user_w3'] || ( $userdata['user_w10'] > 0 && $items['item_type'][10] != 'shield' ) )
		{
			//
			// Определяем тип удара
			//
			if( $items['item_ice_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'water';
			}
			if( $items['item_fire_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'fire';
			}
			if( $items['item_electric_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'air';
			}
			if( $items['item_light_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'light';
			}
			if( $items['item_dark_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'dark';
			}
			if( $items['item_piercing_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'piercing';
			}
			if( $items['item_chopping_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'chopping';
			}
			if( $items['item_crushing_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'crushing';
			}
			if( $items['item_cutting_attacks'][$weapon_slot] != '' )
			{
				$userdata['attacks'][] = 'cutting';
			}
			// ----------
		}

		if( count($userdata['attacks']) == 0 )
		{
			$userdata['attacks'] = array(
				'0'	 => 'piercing',
				'1'	 => 'chopping',
				'2'	 => 'crushing',
				'3'	 => 'cutting');
		}

		$userdata['attack_type'] = $userdata['attacks'][mt_rand(0, (count($userdata['attacks']) - 1))];

		//
		// Вычисляем силу удара (для каждого типа удара урон разный)
		//
		switch( $userdata['attack_type'] )
		{
			case 'ice':
			case 'fire':
			case 'electric':
				// Защита от магии
				$userdata['hit'] = ( $pl2_userdata['user_protect_magic'] != 0 ) ? $userdata['hit'] * ( 1 - ( $pl2_userdata['user_protect_magic'] / 200 ) ) : $userdata['hit'];

				// Защита какой-либо магии
				$userdata['hit'] = ( $pl2_userdata['user_protect_' . $userdata['attack_type']] != 0 ) ? $userdata['hit'] * ( 1 - ( $userdata['user_protect_' . $userdata['attack_type']] / 200 ) ) : $userdata['hit'];

				// Минимальный удар
				$userdata['hit'] = ( $userdata['hit'] <= 0 ) ? mt_rand(1, 3) : $userdata['hit'];

				// Прибавляем мастерство владения магией
				$userdata['hit'] += mt_rand($userdata['user_magic_' . $userdata['attack_type']], $userdata['user_magic_' . $userdata['attack_type']] * 2);

				// Мощность магии
				$userdata['hit'] = ( $userdata['user_mf_power_magic'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_magic'] / 100 ) ) : $userdata['hit'];

				// Мощность какой-либо магии
				$userdata['hit'] = ( $userdata['user_mf_power_' . $userdata['attack_type']] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_' . $userdata['attack_type']] / 100 ) ) : $userdata['hit'];

				// Крит
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' ) ? $userdata['hit'] * 2 : $userdata['hit'];

				// Мощность крита
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' && $userdata['user_mf_power_critical_hit'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_critical_hit'] / 100 ) ) : $userdata['hit'];

				// Сила удара
				$userdata['hit'] = round($userdata['hit']);
				break;
			case 'light':
			case 'dark':
				// Минимальный удар
				$userdata['hit'] = ( $userdata['hit'] <= 0 ) ? mt_rand(1, 3) : $userdata['hit'];

				// Мощность урона
				$userdata['hit'] = ( $userdata['user_mf_power_damage'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_damage'] / 100 ) ) : $userdata['hit'];

				// Крит
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' ) ? $userdata['hit'] * 2 : $userdata['hit'];

				// Мощность крита
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' && $userdata['user_mf_power_critical_hit'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_critical_hit'] / 100 ) ) : $userdata['hit'];

				// Сила удара
				$userdata['hit'] = round($userdata['hit']);
				break;
			case 'piercing':
			case 'chopping':
			case 'crushing':
			case 'cutting':
				// Защита от урона
				$userdata['hit'] = ( ( $pl2_userdata['user_protect_damage'] + $pl2_userdata['user_vitality'] ) != 0 && $userdata['hit_through_armour'] < 100 ) ? $userdata['hit'] * ( 1 - ( ( $pl2_userdata['user_protect_damage'] + $pl2_userdata['user_vitality'] ) / 200 ) ) : $userdata['hit'];

				// Защита от какого-либо урона
				$userdata['hit'] = ( $pl2_userdata['user_protect_' . $userdata['attack_type']] != 0 && $userdata['hit_through_armour'] < 100 ) ? $userdata['hit'] * ( 1 - ( $userdata['user_protect_' . $userdata['attack_type']] / 200 ) ) : $userdata['hit'];

				// Броня
				$userdata['hit'] -= ( $pl2_userdata['user_mf_armour_' . $zone] != 0 && $userdata['hit_through_armour'] < 100 ) ? mt_rand($pl2_userdata['user_mf_armour_' . $zone], $pl2_userdata['user_armour_' . $zone]) : ( ( $userdata['hit_through_armour'] > 100 || $pl2_userdata['user_armour_' . $zone] == 0 ) ? 0 : mt_rand(1, $pl2_userdata['user_armour_' . $zone]) );

				$userdata['hit'] += 0;
				// Минимальный удар
				$userdata['hit'] = ( $userdata['hit'] <= 0 ) ? mt_rand(1, 3) : $userdata['hit'];

				// Мощность урона
				$userdata['hit'] = ( $userdata['user_mf_power_damage'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_damage'] / 100 ) ) : $userdata['hit'];

				// Мощность какого-либо урона
				$userdata['hit'] = ( $userdata['user_mf_power_' . $userdata['attack_type']] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_' . $userdata['attack_type']] / 100 ) ) : $userdata['hit'];

				// Крит
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' ) ? $userdata['hit'] * 2 : $userdata['hit'];

				// Мощность крита
				$userdata['hit'] = ( $userdata['hit_type'] == 'critical_hit' && $userdata['user_mf_power_critical_hit'] != 0 ) ? $userdata['hit'] * ( 1 + ( $userdata['user_mf_power_critical_hit'] / 100 ) ) : $userdata['hit'];


				// Если использован спецприем
				$userdata['hit'] = ( in_array('block_fullshield', $pl2_userdata['specials']) ) ? 1 : ( ( in_array('block_activeshield', $pl2_userdata['specials']) ) ? $userdata['hit'] / 2 : $userdata['hit']);

				if( in_array('block_fullshield', $pl2_userdata['specials']) )
				{
					$user->delete_special($pl2_userdata, 'block_fullshield');
				}
				elseif( in_array('block_activeshield', $pl2_userdata['specials']) )
				{
					$user->delete_special($pl2_userdata, 'block_activeshield');
				}

				// Сила удара
				$userdata['hit'] = round($userdata['hit']);
				break;
		}
		// ----------

		// Обновляем некоторые параметры
		$userdata['user_hit_hp'] += $userdata['hit'];
		$userdata['overall_damage'] += $userdata['hit'];
		$pl2_userdata['user_current_hp'] -= $userdata['hit'];
	}
	//
	// ---------------

	// ---------------
	// Магический удар
	//
	function magic_hit(&$userdata, &$pl2_userdata, $items)
	{
		global $config, $db, $user;

		//
		// Фразы для магического удара
		//
		// Воздух
		$txt['air']['text'][] = 'разряд';
		$txt['air']['text'][] = 'удар искрами';
		$txt['air']['text'][] = 'удар током';
		$txt['air']['text'][] = 'шокирующий разряд';
		$txt['air']['text'][] = 'энергетический удар';

		$txt['air']['axe'][] = 'топора';
		$txt['air']['gloves'][] = 'магических перчаток';
		$txt['air']['knife'][] = 'кинжала';
		$txt['air']['ring'][] = 'кольца';
		$txt['air']['ring'][] = 'красивого кольца';
		$txt['air']['ring'][] = 'магического кольца';

		// Огонь
		$txt['fire']['text'][] = 'ожог потоком пламени';
		$txt['fire']['text'][] = 'удар огнем';

		$txt['fire']['axe'][] = 'топора';
		$txt['fire']['gloves'][] = 'магических перчаток';
		$txt['fire']['knife'][] = 'кинжала';
		$txt['fire']['ring'][] = 'магического кольца';
		$txt['fire']['ring'][] = 'кольца';
		$txt['fire']['ring'][] = 'красивого кольца';
		$txt['fire']['ring'][] = 'раскаленного кольца';

		// Вода
		$txt['water']['text'][] = 'обморожение';
		$txt['water']['text'][] = 'поток холода';
		$txt['water']['text'][] = 'стылок касание';

		$txt['water']['axe'][] = 'топора';
		$txt['water']['gloves'][] = 'ледяных перчаток';
		$txt['water']['knife'][] = 'кинжала';
		$txt['water']['ring'][] = 'кольца';
		$txt['water']['ring'][] = 'магического кольца';
		$txt['water']['ring'][] = 'холодного кольца';
		// ----------

		$item_type = array();
		$magic_hit = 0;
		$magic_hits = array();
		$n = 0;

		for( $i = 1; $i < 16; $i++ )
		{
			if( $userdata['user_w' . $i] > 0 && $userdata['user_w3'] != $userdata['user_w10'] )
			{
				if( $items['item_req_inbuild_spell'][$i] != '' )
				{
					switch( $items['item_req_inbuild_spell'][$i] )
					{
						case 'стихия воздуха':	$magic_hit_type[$n] = 'air'; $item_type[$n] = $items['item_type'][$i]; $n++; break;
						case 'стихия земли':	$magic_hit_type[$n] = 'earth'; $item_type[$n] = $items['item_type'][$i]; $n++; break;
						case 'стихия огня':		$magic_hit_type[$n] = 'fire'; $item_type[$n] = $items['item_type'][$i]; $n++; break;
						case 'стихия воды':		$magic_hit_type[$n] = 'water'; $item_type[$n] = $items['item_type'][$i]; $n++; break;
					}
				}
			}
		}

		for( $i = 0; $i < $n; $i++ )
		{
			if( mt_rand(1, 100) > 65 )
			{
				$j['air']['text'] = mt_rand(0, count($txt['air']['text']) - 1);
				$j['air'][$item_type[$i]] = mt_rand(0, count($txt['air'][$item_type[$i]]) - 1);
				$j['fire']['text'] = mt_rand(0, count($txt['fire']['text']) - 1);
				$j['fire'][$item_type[$i]] = mt_rand(0, count($txt['fire'][$item_type[$i]]) - 1);
				$j['water']['text'] = mt_rand(0, count($txt['water']['text']) - 1);
				$j['water'][$item_type[$i]] = mt_rand(0, count($txt['water'][$item_type[$i]]) - 1);

				// Усиление
				$magic_hits[$i] = ( 7 + $userdata['user_intellect'] + $userdata['user_magic_' . $magic_hit_type[$i]] ) * ( 1 + ( $userdata['user_mf_power_magic'] / 100 ) ) * ( 1 + ( $userdata['user_mf_power_' . $magic_hit_type[$i]] / 100 ) );

				// Ослабление
				$magic_hits[$i] -= ( $pl2_userdata['user_intellect'] * ( 1 + ( $pl2_userdata['user_protect_magic'] / 200 ) ) * ( 1 + ( $pl2_userdata['user_protect_' . $magic_hit_type[$i]] / 200 )));

				if( $magic_hits[$i] > 0 )
				{
					// Сила удара
					$magic_hits[$i] = mt_rand(1, $magic_hits[$i]);

					// Обновляем переменные
					$userdata['overall_damage'] += $magic_hits[$i];
					$userdata['user_hit_hp'] += $magic_hits[$i];
					$pl2_userdata['user_current_hp'] -= $magic_hits[$i];

					$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . gmdate('H:i', time() + (3600 * $config['timezone'])) . '</font> <font class="B' . $pl2_userdata['user_battle_team'] . '">' . $pl2_userdata['user_login'] . '</font> получил ' . $txt[$magic_hit_type[$i]]['text'][$j[$magic_hit_type[$i]]['text']] . ' от ' . $txt[$magic_hit_type[$i]][$item_type[$i]][$j[$magic_hit_type[$i]][$item_type[$i]]] . ' <font color="#FF0000"><b>-' . $magic_hits[$i] . '</b></font> [' . ( ( $pl2_userdata['user_current_hp'] <= 0 ) ? 0 : $pl2_userdata['user_current_hp']) . '/' . $pl2_userdata['user_max_hp'] . ']<br />');
				}
				elseif( $magic_hits[$i] < 0 && $pl2_userdata['user_current_hp'] > 0 && mt_rand(1, 100) > 90 )
				{
					// Сила удара
					$magic_hits[$i] = mt_rand(1, substr($magic_hits[$i], 1));

					// Обновляем уровень HP
					$pl2_userdata['user_current_hp'] += $magic_hits[$i];
					$pl2_userdata['user_current_hp'] = ( $pl2_userdata['user_current_hp'] > $pl2_userdata['user_max_hp'] ) ? $pl2_userdata['user_max_hp'] : $pl2_userdata['user_current_hp'];

					$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . gmdate('H:i', time() + (3600 * $config['timezone'])) . '</font> <font class="B' . $pl2_userdata['user_battle_team'] . '">' . $pl2_userdata['user_login'] . '</font> получил ' . $txt[$magic_hit_type[$i]]['text'][$j[$magic_hit_type[$i]]['text']] . ' от ' . $txt[$magic_hit_type[$i]][$item_type[$i]][$j[$magic_hit_type[$i]][$item_type[$i]]] . ' <font color="#FF0000"><b>+' . $magic_hits[$i] . '</b></font> [' . ( ( $pl2_userdata['user_current_hp'] <= 0 ) ? 0 : $pl2_userdata['user_current_hp']) . '/' . $pl2_userdata['user_max_hp'] . ']<br />');
				}
			}
		}
	}
	//
	// ---------------

	// ---------------
	// Загружаем бонусы
	//
	function obtain_bonuses(&$userdata)
	{
		//
		// Чудовищная Сила
		//
		if( $userdata['user_strength'] >= 100 )
		{
			$userdata['user_mf_power_damage'] += 25;
		}
		elseif( $userdata['user_strength'] >= 75 )
		{
			$userdata['user_mf_power_damage'] += 17;
		}
		elseif( $userdata['user_strength'] >= 50 )
		{
			$userdata['user_mf_power_damage'] += 10;
		}
		elseif( $userdata['user_strength'] >= 25 )
		{
			$userdata['user_mf_power_damage'] += 5;
		}
		// ----------

		//
		// Скорость Молнии
		//
		if( $userdata['user_agility'] >= 100 )
		{
			$userdata['user_mf_anticritical_hit'] += 30;
			$userdata['user_mf_dodging'] += 70;
			$userdata['user_mf_parry'] += 15;
		}
		elseif( $userdata['user_agility'] >= 75 )
		{
			$userdata['user_mf_anticritical_hit'] += 15;
			$userdata['user_mf_dodging'] += 35;
			$userdata['user_mf_parry'] += 15;
		}
		elseif( $userdata['user_agility'] >= 50 )
		{
			$userdata['user_mf_anticritical_hit'] += 15;
			$userdata['user_mf_dodging'] += 35;
			$userdata['user_mf_parry'] += 5;
		}
		elseif( $userdata['user_agility'] >= 25 )
		{
			$userdata['user_mf_parry'] += 5;
		}
		// ----------

		//
		// Предчувствие
		//
		if( $userdata['user_perception'] >= 100 )
		{
			$userdata['user_mf_power_critical_hit'] += 25;
			$userdata['user_mf_critical_hit'] += 70;
			$userdata['user_mf_antidodging'] += 30;
		}
		elseif( $userdata['user_perception'] >= 75 )
		{
			$userdata['user_mf_power_critical_hit'] += 25;
			$userdata['user_mf_critical_hit'] += 35;
			$userdata['user_mf_antidodging'] += 15;
		}
		elseif( $userdata['user_perception'] >= 50 )
		{
			$userdata['user_mf_power_critical_hit'] += 10;
			$userdata['user_mf_critical_hit'] += 35;
			$userdata['user_mf_antidodging'] += 15;
		}
		elseif( $userdata['user_perception'] >= 25 )
		{
			$userdata['user_mf_power_critical_hit'] += 10;
		}
		// ----------

		//
		// Каменное тело
		//
		if( $userdata['user_vitality'] >= 100 )
		{
			$userdata['user_max_hp'] += 250;
		}
		elseif( $userdata['user_vitality'] >= 75 )
		{
			$userdata['user_max_hp'] += 175;
		}
		elseif( $userdata['user_vitality'] >= 50 )
		{
			$userdata['user_max_hp'] += 100;
		}
		elseif( $userdata['user_vitality'] >= 25 )
		{
			$userdata['user_max_hp'] += 50;
		}
		// ----------

		//
		// Разум
		//
		if( $userdata['user_intellect'] >= 100 )
		{
			$userdata['user_mf_power_magic'] += 25;
		}
		elseif( $userdata['user_intellect'] >= 75 )
		{
			$userdata['user_mf_power_magic'] += 17;
		}
		elseif( $userdata['user_intellect'] >= 50 )
		{
			$userdata['user_mf_power_magic'] += 10;
		}
		elseif( $userdata['user_intellect'] >= 25 )
		{
			$userdata['user_mf_power_magic'] += 5;
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Использование спецприемов
	//
	function use_special(&$userdata, $special_name, $hit_count, $krit_count, $counter_count, $block_count, $parry_count)
	{
		global $db, $special_move, $user;

		// Обновляем переменные
		$userdata['user_count_hit'] -= $hit_count;
		$userdata['user_count_critical_hit'] -= $krit_count;
		$userdata['user_count_counterblow'] -= $counter_count;
		$userdata['user_count_block'] -= $block_count;
		$userdata['user_count_parry'] -= $parry_count;
		$userdata['user_special'] = ( $userdata['user_special'] ) ? $userdata['user_special'] . ',' . $special_name : $special_name;

		//
		// Добавляем спецприем к остальным
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_count_hit'				=> $userdata['user_count_hit'],
			'user_count_critical_hit'		=> $userdata['user_count_critical_hit'],
			'user_count_counterblow'		=> $userdata['user_count_counterblow'],
			'user_count_block'				=> $userdata['user_count_block'],
			'user_count_parry'				=> $userdata['user_count_parry'],
			'user_special'					=> $userdata['user_special'])) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу использовать спецприем...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		// Определяем необходимые переменные
		$special_title = $special_move->name($special_name);
		$username = '<span class="B' . $userdata['user_battle_team'] . '">' . $userdata['user_login'] . '</span>';

		if( $special_name == 'hit_strong' || $special_name == 'hit_luck' || $special_name == 'krit_wildluck' || $special_name == 'krit_blindluck' || $special_name == 'multi_doom' || $special_name == 'multi_skiparmor' || $special_name == 'multi_hiddendodge' || $special_name == 'multi_hiddenpower' || $special_name == 'krit_crush' || $special_name == 'multi_cowardshift' )
		{
			// Сообщение
			$message[] = $username . ', вспомнив слова своего сэнсея, из последних сил исполнил прием "' . $special_title . '".<br />';
			$message[] = $username . ', выкрикнув: «А ещё я вот так могу!», показал, что такое прием "' . $special_title . '".<br />';
			$message[] = $username . ', понимая, что ситуация становится критической, решился на прием "' . $special_title . '".<br />';
			$message[] = $username . ', сам не поняв зачем, применил прием "' . $special_title . '".<br />';
		}
		elseif( $special_name == 'hit_overhit' )
		{
			$message[] = $username . ' наносит неожиданный удар по противнику. <font color="#006699"><b>-' . ( $user_level * 5 ) . '</b></font>';
		}
		elseif( $special_name == 'counter_winddance' || $special_name == 'counter_bladedance' || $special_name == 'block_activeshield' || $special_name == 'block_fullshield' || $special_name == 'parry_prediction' || $special_name == 'parry_secondlife' || $special_name == 'block_absolute' )
		{
			// Сообщение
			$message[] = $username . ', нетрезво оценив положение, решил, что его спасение это прием "' . $special_title . '".<br />';
			$message[] = $username . ', нетрезво оценив положение, решил, что поможет ему только прием "' . $special_title . '".<br />';
			$message[] = $username . ', понял, пропустив очередной удар в голову, что поможет ему только прием "' . $special_title . '".<br />';
			$message[] = $username . ', понял, пропустив очередной удар в голову, что его спасение это прием "' . $special_title . '".<br />';
			$message[] = 'Кроличья лапка, подкова в перчатке и прием "' . $special_title . '" помогли ' . $username . ' продержаться ещё немного.<br />';
			$message[] = $username . ', пораскинув мозгами по земле, сообразил, что его выручат или прием "' . $special_title . '" или вмешательство Мусорщика.<br />';
		}

		// Запись в лог боя
		$user->add_log_message($userdata['user_battle_id'], '<font class="date">' . date('H:i', time()) . '</font> ' . $message[mt_rand(0, count($message) -1)]);
	}
	//
	// ---------------
}

$user = new user_battle();

?>