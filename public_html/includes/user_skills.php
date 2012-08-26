<?php

class user_skills extends user
{
	// ---------------
	// Определяем бонусы
	//
	function obtain_bonuses()
	{
		global $userdata;

		$bonus = '';
		$bonuses[0] = $bonuses[1] = $bonuses[2] = $bonuses[3] = $bonuses[4] = $bonuses[5] = '';

		// Чудовищная Сила
		$bonuses[0] = ( $userdata['user_strength'] >= 25 ) ? '<li>Мф. мощности урона (%): +5<br />' : $bonuses[0];
		$bonuses[0] = ( $userdata['user_strength'] >= 50 ) ? '<li>Мф. мощности урона (%): +10<br />' : $bonuses[0];
		$bonuses[0] = ( $userdata['user_strength'] >= 75 ) ? '<li>Мф. мощности урона (%): +17<br />' : $bonuses[0];
		$bonuses[0] = ( $userdata['user_strength'] >= 100 ) ? '<li>Мф. мощности урона (%): +25<br />' : $bonuses[0];

		// Скорость Молнии
		$bonuses[1] = ( $userdata['user_agility'] >= 25 ) ? '<li>Мф. парирования (%): +5<br />' : $bonuses[1];
		$bonuses[1] = ( $userdata['user_agility'] >= 50 ) ? '<li>Мф. парирования (%): +5<li>Мф. против критического удара (%): +15<li>Мф. увертывания (%): +35<br />' : $bonuses[1];
		$bonuses[1] = ( $userdata['user_agility'] >= 75 ) ? '<li>Мф. парирования (%): +15<li>Мф. против критического удара (%): +15<li>Мф. увертывания (%): +35<br />' : $bonuses[1];
		$bonuses[1] = ( $userdata['user_agility'] >= 100 ) ? '<li>Мф. парирования (%): +15<li>Мф. против критического удара (%): +30<li>Мф. увертывания (%): +70<br />' : $bonuses[1];

		// Предчувствие
		$bonuses[2] = ( $userdata['user_perception'] >= 25 ) ? '<li>Мф. мощности критического удара (%): +10<br />' : $bonuses[2];
		$bonuses[2] = ( $userdata['user_perception'] >= 50 ) ? '<li>Мф. мощности критического удара (%): +10<li>Мф. критического удара (%): +35<li>Мф. против увертывания (%): +15<br />' : $bonuses[2];
		$bonuses[2] = ( $userdata['user_perception'] >= 75 ) ? '<li>Мф. мощности критического удара (%): +25<li>Мф. критического удара (%): +35<li>Мф. против увертывания (%): +15<br />' : $bonuses[2];
		$bonuses[2] = ( $userdata['user_perception'] >= 100 ) ? '<li>Мф. мощности критического удара (%): +25<li>Мф. критического удара (%): +70<li>Мф. против увертывания (%): +30<br />' : $bonuses[2];

		// Стальное Тело
		$bonuses[3] = ( $userdata['user_vitality'] >= 25 ) ? '<li>Уровень жизни (HP): +50<br />' : $bonuses[3];
		$bonuses[3] = ( $userdata['user_vitality'] >= 50 ) ? '<li>Уровень жизни (HP): +100<br />' : $bonuses[3];
		$bonuses[3] = ( $userdata['user_vitality'] >= 75 ) ? '<li>Уровень жизни (HP): +175<br />' : $bonuses[3];
		$bonuses[3] = ( $userdata['user_vitality'] >= 100 ) ? '<li>Уровень жизни (HP): +250<br />' : $bonuses[3];

		// Разум
		$bonuses[4] = ( $userdata['user_intellect'] >= 25 ) ? '<li>Мф. мощности магии стихий (%): +5<br />' : $bonuses[4];
		$bonuses[4] = ( $userdata['user_intellect'] >= 50 ) ? '<li>Мф. мощности магии стихий (%): +10<br />' : $bonuses[4];
		$bonuses[4] = ( $userdata['user_intellect'] >= 75 ) ? '<li>Мф. мощности магии стихий (%): +17<br />' : $bonuses[4];
		$bonuses[4] = ( $userdata['user_intellect'] >= 100 ) ? '<li>Мф. мощности магии стихий (%): +25<br />' : $bonuses[4];

		// Сила Мудрости
		$bonuses[5] = ( $userdata['user_wisdom'] >= 25 ) ? '<li>Уменьшение расхода маны (%): +2<br />' : $bonuses[5];
		$bonuses[5] = ( $userdata['user_wisdom'] >= 50 ) ? '<li>Уменьшение расхода маны (%): +5<br />' : $bonuses[5];
		$bonuses[5] = ( $userdata['user_wisdom'] >= 75 ) ? '<li>Уменьшение расхода маны (%): +8<br />' : $bonuses[5];
		$bonuses[5] = ( $userdata['user_wisdom'] >= 100 ) ? '<li>Уменьшение расхода маны (%): +12<br />' : $bonuses[5];

		$bonus .= ( $bonuses[0] ) ? '<br /><b>Чудовищная Сила:</b><br />' . $bonuses[0] : '';
		$bonus .= ( $bonuses[1] ) ? '<br /><b>Скорость Молнии:</b><br />' . $bonuses[1] : '';
		$bonus .= ( $bonuses[2] ) ? '<br /><b>Предчувствие:</b><br />' . $bonuses[2] : '';
		$bonus .= ( $bonuses[3] ) ? '<br /><b>Стальное Тело:</b><br />' . $bonuses[3] : '';
		$bonus .= ( $bonuses[4] ) ? '<br /><b>Разум:</b><br />' . $bonuses[4] : '';
		$bonus .= ( $bonuses[5] ) ? '<br /><b>Сила Мудрости:</b><br />' . $bonuses[5] : '';

		return $bonus;
	}
	//
	// ---------------

	// ---------------
	// Распределение умений
	//
	function skill_up($upr)
	{
		global $db, $userdata;

		//
		// Обновляем данные
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_' . $upr			=> $userdata['user_' . $upr] + 1,
			'user_free_skills'		=> $userdata['user_free_skills'] - 1)) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$userdata['user_' . $upr] += 1;
		$userdata['user_free_skills'] -= 1;
		// ----------

		return $userdata;
	}
	//
	// ---------------

	// ---------------
	// Названия особенностей
	//
	function spc_name($spc)
	{
		switch( $spc )
		{
			case 'decrease_transfers_price':	$name = 'Изворотливый'; break;
			case 'decrease_injury':				$name = 'Стойкий'; break;
			case 'homeworld_time':				$name = 'Быстрый'; break;
			case 'increase_experience':			$name = 'Сообразительный'; break;
			case 'increase_friends':			$name = 'Дружелюбный'; break;
			case 'increase_hobby':				$name = 'Общительный'; break;
			case 'max_mass':					$name = 'Запасливый'; break;
			case 'transfers':					$name = 'Коммуникабельный'; break;
			case 'hpspeed':						$name = 'Двужильный'; break;
			case 'manaspeed':					$name = 'Здравомыслящий'; break;
			default:							$name = ''; break;
		}

		return $name;
	}
	// ---------------

	// ---------------
	// Выбранные особенности
	//
	function spc_selected()
	{
		global $user, $userdata;

		if( $userdata['user_spc'] )
		{
			// Определяем переменные
			$spc = explode(',', $userdata['user_spc']);
			$specials = '';

			for( $i = 0; $i < count($spc); $i++ )
			{
				// Заполняем данными…
				$specials .= '&bull; ' . $user->spc_name(substr($spc[$i], 0, -1)) . ( ( substr($spc[$i], -1) > 1 ) ? '-' . substr($spc[$i], -1) : '' ) . '<br />';
			}

			return $specials;
		}
		else
		{
			return false;
		}
	}
	//
	// ---------------

	// ---------------
	// Вывод особенностей
	//
	function spc_show($spc)
	{
		global $user, $userdata;

		// Список особенностей
		$specials = explode(',', $userdata['user_spc']);

		//
		// Определяем уровень прокачанной особенности
		//
		if( $userdata['user_spc'] )
		{
			if( in_array($spc . '5', $specials) )
			{
				$spc_level = 5;
			}
			elseif( in_array($spc . '4', $specials) )
			{
				$spc_level = 4;
			}
			elseif( in_array($spc . '3', $specials) )
			{
				$spc_level = 3;
			}
			elseif( in_array($spc . '2', $specials) )
			{
				$spc_level = 2;
			}
			elseif( in_array($spc . '1', $specials) )
			{
				$spc_level = 1;
			}
			else
			{
				$spc_level = 0;
			}
		}
		else
		{
			$spc_level = 0;
		}

		$spc_new_level = $spc_level + 1;
		// ----------

		$message = ( $spc_level < 5 ) ? '&bull; <a href="main.php?set_special=' . $spc . $spc_new_level . '" onclick=\'return confirm("Вы уверены, что хотите выбрать особенность \"' . ( $user->spc_name($spc) . ( ( $spc_level > 0 ) ? '-' . $spc_new_level : '' ) ) . '\"?")\'>' . ( $user->spc_name($spc) . ( ( $spc_level > 0 ) ? '-' . $spc_new_level : '' ) ) . '</a><br />' : '';

		switch( $spc )
		{
			case 'decrease_transfers_price':
				$message .= ( $message ) ? '<small>Снижение стоимости передач на 0.' . ( $spc_new_level ) . ' кр.</small><br /><br />' : '';
				break;
			case 'decrease_injury':
				$message .= ( $message ) ? '<small>Время травмы меньше на ' . ( $spc_new_level * 5 ) . '%.</small><br /><br />' : '';
				break;
			case 'homeworld_time':
				$message .= ( $message ) ? '<small>Кнопка «Возврат» появляется раньше на ' . ( $spc_new_level * 5 ) . ' минут</small><br /><br />' : '';
				break;
			case 'increase_experience':
				$message .= ( $message ) ? '<small>Получаемый опыт больше на ' . ( $spc_new_level ) . '%</small><br /><br />' : '';
				break;
			case 'increase_friends':
				$message .= ( $message ) ? '<small>Cписок друзей больше на ' . ( $spc_new_level * 5 ) . '</small><br /><br />' : '';
				break;
			case 'increase_hobby':
				$message .= ( $message ) ? '<small>Увеличение максимального размера раздела "Увлечения / хобби" на ' . ( $spc_new_level * 200 ) . ' символов</small><br /><br />' : '';
				break;
			case 'max_mass':
				$message .= ( $message ) ? '<small>Больше места в рюкзаке на ' . ( $spc_new_level * 10 ) . ' единиц</small><br /><br />' : '';
				break;
			case 'transfers':
				$message .= ( $message ) ? '<small>Лимит передач в день +' . ( $spc_new_level * 20 ) . '</small><br /><br />' : '';
				break;
			case 'hpspeed':
				$message .= ( $message ) ? '<small>Здоровье восстанавливается быстрее. +' . ( $spc_new_level * 5 ) . '%</small><br /><br />' : '';
				break;
			case 'manaspeed':
				$message = ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? $message : '';
				$message .= ( $message ) ? '<small>Мана восстанавливается быстрее +' . ( $spc_new_level * 5 ) . '%</small><br /><br />' : '';
				break;
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Распределение особенностей
	//
	function spc_up($spc)
	{
		global $db, $user, $userdata;

		// Определяем уровень
		$spc_level = substr($spc, -1);

		$specials = explode(',', $userdata['user_spc']);

		//
		// Проверки
		//
		if( !preg_match('#^[1-5]+$#', $spc_level) )
		{
			site_message('Неверно введены данные');
		}
		elseif( $userdata['user_spc'] && in_array($spc, $specials) )
		{
			site_message('Данная особенность уже выбрана');
		}
		elseif( $spc_level > 1 && !in_array(substr($spc, 0, -1) . ( $spc_level - 1 ), $specials) )
		{
			site_message('Ошибка прокачки особенности');
		}
		elseif( substr($spc, -1) == 'manaspeed' && ( $userdata['user_level'] < 7 || $userdata['user_max_mana'] <= 0 ) )
		{
			site_message('Вы не можете прокачать эту особенность');
		}
		// ----------

		//
		// Модификатор прокачки
		//
		switch( substr($spc, 0, -1) )
		{
			case 'decrease_transfers_price':	$mf = '0.1'; break;
			case 'decrease_injury':				$mf = '5'; break;
			case 'homeworld_time':				$mf = '5'; break;
			case 'increase_experience':			$mf = '1'; break;
			case 'increase_friends':			$mf = '5'; break;
			case 'increase_hobby':				$mf = '200'; break;
			case 'max_mass':					$mf = '10'; break;
			case 'transfers':					$mf = '20'; break;
			case 'hpspeed':						$mf = '5'; break;
			case 'manaspeed':					$mf = '5'; break;
		}
		// ----------

		//
		// Прокачиваем особенность
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_' . substr($spc, 0, -1)		=> $userdata['user_' . substr($spc, 0, -1)] + $mf,
			'user_spc'							=> ( $userdata['user_spc'] ) ? $userdata['user_spc'] . ',' . $spc : $spc,
			'user_free_spc'						=> $userdata['user_free_spc'] - 1)) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		// Обновляем параметры
		$userdata['user_' . substr($spc, 0, -1)] += $mf;
		$userdata['user_spc'] .= ',' . $spc;
		$userdata['user_free_spc'] -= 1;

		return $userdata;
	}
	//
	// ---------------

	// ---------------
	// Распределение способностей
	//
	function stat_up($upr)
	{
		global $db, $userdata;

		switch( $upr )
		{
			case 'strength':
			case 'agility':
			case 'perception':
			case 'intellect':
			case 'spirituality':
			case 'freedom':
			case 'freedom_of_spirit':
			case 'holiness':
				// ---------------
				// Увеличиваем основные статы
				//
				$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
					'user_' . $upr		=> $userdata['user_' . $upr] + 1,
					'user_free_upr'		=> $userdata['user_free_upr'] - 1)) . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$userdata['user_' . $upr] += 1;
				$userdata['user_free_upr'] -= 1;
				break;
				//
				// ---------------
			case 'vitality':
				// ---------------
				// Увеличиваем выносливость
				//
				$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
					'user_max_hp'		=> $userdata['user_max_hp'] + 6,
					'user_vitality'		=> $userdata['user_vitality'] + 1,
					'user_free_upr'		=> $userdata['user_free_upr'] - 1)) . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$userdata['user_max_hp'] += 6;
				$userdata['user_vitality'] += 1;
				$userdata['user_free_upr'] -= 1;
				break;
				//
				// ---------------
			case 'wisdom':
				// ---------------
				// Увеличиваем мудрость
				//
				$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
					'user_max_mana'		=> $userdata['user_max_mana'] + 10,
					'user_wisdom'		=> $userdata['user_wisdom'] + 1,
					'user_free_upr'		=> $userdata['user_free_upr'] - 1)) . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$userdata['user_max_mana'] += 10;
				$userdata['user_wisdom'] += 1;
				$userdata['user_free_upr'] -= 1;
				break;
				//
				// ---------------
		}
		
		return $userdata;
	}
	//
	// ---------------

	// ---------------
	// Удаление выбранного спец-приёма
	//
	function special_clear($n)
	{
		global $db, $userdata;

		if( $n < 1 || $n > 10 )
		{
			$message = 'Выбранный приём не существует';
		}
		else
		{
			$special_selected = explode(',', $userdata['user_special_selected']);

			unset($special_selected[$n - 1]);

			$userdata['user_special_selected'] = implode(',', $special_selected);

			$sql = "UPDATE " . USERS_TABLE . " SET user_special_selected = '" . $userdata['user_special_selected'] . "' WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не удалить выбранный спец-приём...', '', __LINE__, __FILE__, $sql);
			}

			$message = 'Выбранный приём успешно удалён';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Выбор спец-приёмов
	//
	function special_select($special_name)
	{
		global $db, $userdata;

		// Выбранные спец-приёмы
		$special_selected = explode(',', $userdata['user_special_selected']);

		//
		// Проверки
		//
		if( in_array($special_name, $special_selected) )
		{
			$message = 'Данный спец-приём уже выбран';
		}
		elseif( count($special_selected) >= 10 )
		{
			$message = 'Достигнут максимум';
		}
		else
		{
			$userdata['user_special_selected'] .= ( $userdata['user_special_selected'] ) ? ',' . $special_name : $special_name;

			// Обновляем данные
			$sql = "UPDATE " . USERS_TABLE . " SET user_special_selected = '" . $userdata['user_special_selected'] . "' WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу выбрать приём...', '', __LINE__, __FILE__, $sql);
			}

			$message = 'Приём успешно выбран';
		}
		// ----------

		return $message;
	}
	//
	// ---------------
}

$user = new user_skills();

?>