<?php
/***************************************************************************
 *							   main_edit.php							   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: main_edit.php, v 1.00 2006/03/12 15:54:00 V@cuum Exp $		   *
 *																		   *
 *																		   *
 ***************************************************************************/

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

$root_path = './';

include($root_path . 'includes/main_exp.php');

// Отдел в инвентаре
$edit = ( !$edit ) ? $userdata['user_main_edit'] : intval($_GET['edit']);

if( $use )
{
	if( !preg_match('#^[0-9]+$#', $n) )
	{
		// Проверяем правильность заполнения
		$message = 'Неверно введены данные';
	}
	elseif( $n > 0 )
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

		if( !$row )
		{
			$message = 'Вещь не найдена';
		}
		elseif( $row['item_img'] == $use )
		{
			switch( $use )
			{
				// Нападалки
				case 'attack':
				case 'attackb':
					$row2 = get_userdata('', $param);

					require($root_path . 'includes/magic.php');

					// Ломаем свиток
					$magic->scroll_damage($row);

					// Вероятность срабатывания
					$message = $magic->chance_to_use($userdata, $row);

					if( !$message )
					{
						// Нападаем
						$message = $magic->attack($userdata, $row2, $use);
					}

					break;
				case 'pot_base_50_regeneration':
					// Эликсир Восстановления
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'hpspeed', 100, false, 120, 'эликсир');
					break;
				case 'pot_base_50_str':
					// Зелье Могущества
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'strength', 10, false, 360, 'эликсир');
					break;
				case 'pot_base_50_dex':
					// Зелье Стремительности
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'agility', 10, false, 360, 'эликсир');
					break;
				case 'pot_base_50_inst':
					// Зелье Прозрения
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'perception', 10, false, 360, 'эликсир');
					break;
				case 'pot_base_50_kolproof':
					// Зелье Пронзающих Игл
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_piercing', 15, false, 360, 'эликсир');
					break;
				case 'pot_base_50_drobproof':
					// Зелье Тяжелых Молотов
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_crushing', 12, false, 360, 'эликсир');
					break;
				case 'pot_base_50_rubproof':
					// Зелье Свистящих Секир
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_chopping', 14, false, 360, 'эликсир');
					break;
				case 'pot_base_50_rezproof':
					// Зелье Сверкающих Лезвий
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_cutting', 10, false, 360, 'эликсир');
					break;
				case 'pot_base_50_earthproof':
					// Эликсир Песков
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_earth', 50, false, 90, 'эликсир');
					break;
				case 'pot_base_50_waterproof':
					// Эликсир Морей
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_water', 50, false, 90, 'эликсир');
					break;
				case 'pot_base_50_airproof':
					// Эликсир Ветра
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_air', 50, false, 90, 'эликсир');
					break;
				case 'pot_base_50_fireproof':
					// Эликсир Пламени
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_fire', 50, false, 90, 'эликсир');
					break;
				case 'pot_base_50_damageproof':
					// Эликсир Неуязвимости
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_damage', 10, false, 360, 'эликсир');
					break;
				case 'pot_base_50_magicproof':
					// Эликсир Стихий
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_magic', 25, false, 120, 'эликсир');
					break;
				case 'pot_base_100_allmag1':
					// Малое зелье Отрицания
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_air', 50, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_earth', 50, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_fire', 50, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_water', 50, false, 180, 'эликсир');
					break;
				case 'pot_base_150_earthproof':
					// Эликсир Недр
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_earth', 75, false, 150, 'эликсир');
					break;
				case 'pot_base_150_waterproof':
					// Эликсир Океанов
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_water', 75, false, 150, 'эликсир');
					break;
				case 'pot_base_150_airproof':
					// Эликсир Урагана
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_air', 75, false, 150, 'эликсир');
					break;
				case 'pot_base_150_fireproof':
					// Эликсир Зарева
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_fire', 75, false, 150, 'эликсир');
					break;
				case 'pot_base_200_alldmg2':
					// Великое зелье Стойкости
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_damage', 10, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_piercing', 15, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_chopping', 15, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_crushing', 15, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_cutting', 15, false, 180, 'эликсир');
					break;
				case 'pot_base_200_allmag2':
					// Великое зелье Отрицания
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'protect_magic', 25, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_air', 75, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_earth', 75, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_fire', 75, false, 180, 'эликсир');
					$user->status_add($userdata, 'protect_water', 75, false, 180, 'эликсир');
					break;
				case 'pot_base_200_bot3':
					// Снадобье Великана
					$user->elixir_damage($userdata, $row);
					$user->status_add($userdata, 'strength', 15, false, 1440, 'эликсир');
					break;
			}
		}
	}
}

if( $delk )
{
	// ---------------
	// Удаление комплекта
	//
	if( !preg_match('#^[0-9]+$#', $delk) )
	{
		$message = 'Номер комплекта введен неправильно';
	}
	elseif( $userdata['user_level'] < 2 )
	{
		$message = 'Удалять комплекты можно только со второго уровня';
	}
	else
	{
		//
		// Получаем данные удаляемого комплекта
		//
		$sql = "SELECT kmp_id, kmp_user_id, kmp_name FROM " . KMP_TABLE . " WHERE `kmp_id` = " . $delk;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные комплекта...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		//
		// Проверки
		//
		if( !$row )
		{
			$message = 'Комплект не найден';
		}
		elseif( $row['kmp_user_id'] != $userdata['user_id'] )
		{
			$message = 'Комплект не найден';
		}
		else
		{
			//
			// Удаляем комплект
			//
			$sql = "DELETE FROM " . KMP_TABLE . " WHERE `kmp_id` = " . $row['kmp_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу удалить комплект...', '', __LINE__, __FILE__, $sql);
			}

			$message = 'Удалили комплект "' . $row['kmp_name'] . '"';
			// ----------
		}
		// ----------
	}
	//
	// ---------------
}
elseif( $drop )
{
	// ---------------
	// Выбрасывание предмета
	//
	if( $dropall )
	{
		// Выбрасывание предметов одного типа
		$message = $user->item_drop($_POST['id'], $userdata, $dropall);

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'drop', '"' . $userdata['user_login'] . '" выбросил все предметы вида "' . substr($message, 10, -11) . '"');
	}
	else
	{
		// Выбрасывание предмета
		$message = $user->item_drop($_POST['id'], $userdata);

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'drop', '"' . $userdata['user_login'] . '" выбросил предмет "' . substr($message, 9, -10) . '"');
	}
	//
	// ---------------
}
elseif( $set )
{
	// ---------------
	// Одевание вещи
	//
	if( !preg_match('#^[0-9]+$#', $set) )
	{
		$message = 'Неверно введены данные';
	}
	else
	{
		// Одеваемся ;)
		$user->item_set($set, $userdata);
	}
	//
	// ---------------
}
elseif( $setdown == 'all' )
{
	// ---------------
	// Снятие всех вещей
	//
	for( $i = 1; $i < 16; $i++ )
	{
		if( $userdata['user_w' . $i] > 0 )
		{
			$user->item_setdown($i, $userdata);
		}
	}

	for( $i = 100; $i < 110; $i++ )
	{
		if( $userdata['user_w' . $i] > 0 )
		{
			$user->item_setdown($i, $userdata);
		}
	}

	if( $userdata['user_w400'] > 0 )
	{
		$user->item_setdown(400, $userdata);
	}
	//
	// ---------------
}
elseif( $setdown )
{
	// ---------------
	// Снятие вещи
	//
	if( !preg_match('#^[0-9]+$#', $setdown) )
	{
		$message = 'Неверно введены данные';
	}
	else
	{
		// Раздеваемся ;)
		$user->item_setdown($setdown, $userdata);
	}
	//
	// ---------------
}
elseif( $savekmp )
{
	// ---------------
	// Запоминание комплекта
	//
	if( $userdata['user_level'] < 2 )
	{
		$message = 'Запоминать комплекты можно только со второго уровня';
	}
	else
	{
		//
		// Сохраняем комплект в БД
		//
		$sql = "INSERT INTO " . KMP_TABLE . " " . $db->sql_build_array('INSERT', array(
			'kmp_user_id'		=> $userdata['user_id'],
			'kmp_item_1'		=> $userdata['user_w1'],
			'kmp_item_2'		=> $userdata['user_w2'],
			'kmp_item_3'		=> $userdata['user_w3'],
			'kmp_item_4'		=> $userdata['user_w4'],
			'kmp_item_5'		=> $userdata['user_w5'],
			'kmp_item_6'		=> $userdata['user_w6'],
			'kmp_item_7'		=> $userdata['user_w7'],
			'kmp_item_8'		=> $userdata['user_w8'],
			'kmp_item_9'		=> $userdata['user_w9'],
			'kmp_item_10'		=> $userdata['user_w10'],
			'kmp_item_11'		=> $userdata['user_w11'],
			'kmp_item_12'		=> $userdata['user_w12'],
			'kmp_item_13'		=> $userdata['user_w13'],
			'kmp_item_14'		=> $userdata['user_w14'],
			'kmp_item_15'		=> $userdata['user_w15'],
			'kmp_item_16'		=> $userdata['user_w16'],
			'kmp_item_400'		=> $userdata['user_w400'],
			'kmp_name'			=> $savekmp));
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу запомнить комплект...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		$message = 'Запомнили комплект "' . $savekmp . '"';
	}
	//
	// ---------------
}
elseif( $skmp )
{
	// ---------------
	// Одевание комплекта
	//
	$sql = "SELECT * FROM " . KMP_TABLE . " WHERE `kmp_id` = " . $skmp;
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные комплекта...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	//
	// Проверки
	//
	if( !$row )
	{
		$message = 'Комплект не найден';
	}
	elseif( $row['kmp_user_id'] != $userdata['user_id'] )
	{
		$message = 'Комплект не найден';
	}
	else
	{
		// Снимаем кольца (на всякий)
		if( $userdata['user_w6'] > 0 && $userdata['user_w6'] != $row['kmp_item_6'] )
		{
			$user->item_setdown('6', $userdata);
		}

		if( $userdata['user_w7'] > 0 && $userdata['user_w7'] != $row['kmp_item_7'] )
		{
			$user->item_setdown('7', $userdata);
		}

		if( $userdata['user_w8'] > 0 && $userdata['user_w8'] != $row['kmp_item_8'] )
		{
			$user->item_setdown('8', $userdata);
		}

		//
		// Одеваем вещи
		//
		$items_indexes = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '400');

		foreach( $items_indexes as $index => $slot )
		{
			if( $userdata['user_w' . $slot] != $row['kmp_item_' . $slot] )
			{
				$user->item_set($row['kmp_item_' . $slot], $userdata, true);
			}
		}
		// ----------
	}
	//
	// ---------------
}

// Вещи
$items = array();
$user->obtain_items($items, $userdata, 'inventory', $edit);

//
// Комплекты
//
$kmps = '';
$user->obtain_kmps($kmps, $userdata);
// ----------

site_header();

$template->set_filenames(array(
	'body' => 'main_edit.html')
);

//
// Вывод маны (если есть)
//
if( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 )
{
	$template->assign_block_vars('mana', array());
}
// ----------

//
// Вывод свитков (если есть)
//
if( $userdata['user_w100'] || $userdata['user_w101'] || $userdata['user_w102'] || $userdata['user_w103'] || $userdata['user_w104'] || $userdata['user_w105'] || $userdata['user_w106'] || $userdata['user_w107'] || $userdata['user_w108'] || $userdata['user_w109'] )
{
	$template->assign_block_vars('scrolls', array());
}
// ----------

$template->assign_vars(array(
	'CURRENT_HP'			=> $userdata['user_current_hp'],
	'DRWFL'					=> $user->drwfl($userdata),
	'HPSPEED'				=> $userdata['user_hpspeed'],
	'LEVEL'					=> $userdata['user_level'],
	'LOGIN'					=> $userdata['user_login'],
	'KLAN'					=> ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? '<b>Орден Света</b>' : ( ( $userdata['user_align'] >= 3 && $userdata['user_align'] < 4 ) ? '<b>Армада</b>' : ( ( $userdata['user_klan'] != '' ) ? 'Клан - ' . $userdata['user_klan'] : '')),
	'MANA'					=> ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? 'top.setMana(' . $userdata['user_current_mana'] . ',' . $userdata['user_max_mana'] . ',' . $userdata['user_manaspeed'] . ')' : '',
	'MAX_HP'				=> $userdata['user_max_hp'],
	'OBRAZ'					=> $userdata['user_obraz'],
	'OBRAZ_BUTTON'			=> ( $userdata['user_allow_change_obraz'] == 1 || $userdata['user_access_level'] == ADMIN ) ? '<input type="submit" name="setimage" value="Образ" title="Выбрать образ персонажа">' : '',
	'SAVE_KMP'				=> ( $userdata['user_level'] >= 2 ) ? '<br><a href="javascript:kmp()">Запомнить комплект</a><br>' : '',

	'STRENGTH'				=> $userdata['user_strength'],
	'AGILITY'				=> $userdata['user_agility'],
	'PERCEPTION'			=> $userdata['user_perception'],
	'VITALITY'				=> $userdata['user_vitality'],
	'INTELLECT'				=> ( $userdata['user_level'] >= 4 ) ? 'Интеллект: ' . $userdata['user_intellect'] . '<br>' : '',
	'WISDOM'				=> ( $userdata['user_level'] >= 7 ) ? 'Мудрость: ' . $userdata['user_wisdom'] . '<br>' : '',
	'SPIRITUALITY'			=> ( $userdata['user_level'] >= 10 ) ? 'Духовность: ' . $userdata['user_spirituality'] . '<br>' : '',
	'FREEDOM'				=> ( $userdata['user_level'] >= 13 ) ? 'Воля: ' . $userdata['user_freedom'] . '<br>' : '',
	'FREEDOM_OF_SPIRIT'		=> ( $userdata['user_level'] >= 16 ) ? 'Свобода духа: ' . $userdata['user_freedom_of_spirit'] . '<br>' : '',
	'HOLINESS'				=> ( $userdata['user_level'] >= 19 ) ? 'Божественность: ' . $userdata['user_holiness'] . '<br>' : '',
	'FREE_UPR'				=> ( $userdata['user_free_upr'] > 0 ) ? '<a href="main.php?skills=1">+ Способности</a>' : '',
	'FREE_SKILLS'			=> ( $userdata['user_free_skills'] > 0 ) ? '&bull;&nbsp;<a href="main.php?skills=1">Обучение</a>' : '',

	'EXP'					=> $userdata['user_exp'],
	'NEXTEXP'				=> $nextexp,
	'WINS'					=> $userdata['user_wins'],
	'LOSSES'				=> $userdata['user_losses'],
	'DRAWS'					=> $userdata['user_draws'],
	'MONEY'					=> $user->int_money($userdata['user_money']),

	'OVERALL_MASS'			=> $userdata['user_items_mass'],
	'MAX_MASS'				=> $userdata['user_items_max_mass'] + ( $userdata['user_strength'] * 4 ),
	'ITEMS'					=> $userdata['user_items'],

	'I_SCROLL1'				=> $user->show_item($userdata, $items, 'w100', 'setdown'),
	'I_SCROLL2'				=> $user->show_item($userdata, $items, 'w101', 'setdown'),
	'I_SCROLL3'				=> $user->show_item($userdata, $items, 'w102', 'setdown'),
	'I_SCROLL4'				=> $user->show_item($userdata, $items, 'w103', 'setdown'),
	'I_SCROLL5'				=> $user->show_item($userdata, $items, 'w104', 'setdown'),
	'I_SCROLL6'				=> $user->show_item($userdata, $items, 'w105', 'setdown'),
	'I_SCROLL7'				=> $user->show_item($userdata, $items, 'w106', 'setdown'),
	'I_SCROLL8'				=> $user->show_item($userdata, $items, 'w107', 'setdown'),
	'I_SCROLL9'				=> $user->show_item($userdata, $items, 'w108', 'setdown'),
	'I_SCROLL10'			=> $user->show_item($userdata, $items, 'w109', 'setdown'),

	'I_HELMET'				=> $user->show_item($userdata, $items, 'w9', 'setdown'),
	'I_WEAPON'				=> $user->show_item($userdata, $items, 'w3', 'setdown'),
	'I_ARMOR'				=> ( $userdata['user_w4'] == 0 && $userdata['user_w400'] > 0 ) ? $user->show_item($userdata, $items, 'w400', 'setdown') : $user->show_item($userdata, $items, 'w4', 'setdown'),
	'I_BELT'				=> $user->show_item($userdata, $items, 'w5', 'setdown'),
	'I_R_KARMAN'			=> $user->show_item($userdata, $items, 'w14', 'setdown'),
	'I_L_KARMAN'			=> $user->show_item($userdata, $items, 'w15', 'setdown'),
	'I_CLIP'				=> $user->show_item($userdata, $items, 'w1', 'setdown'),
	'I_AMULET'				=> $user->show_item($userdata, $items, 'w2', 'setdown'),
	'I_BRASLET'				=> $user->show_item($userdata, $items, 'w13', 'setdown'),
	'I_GLOVES'				=> $user->show_item($userdata, $items, 'w11', 'setdown'),
	'I_RING1'				=> $user->show_item($userdata, $items, 'w6', 'setdown'),
	'I_RING2'				=> $user->show_item($userdata, $items, 'w7', 'setdown'),
	'I_RING3'				=> $user->show_item($userdata, $items, 'w8', 'setdown'),
	'I_SHIELD'				=> $user->show_item($userdata, $items, 'w10', 'setdown'),
	'I_BOOTS'				=> $user->show_item($userdata, $items, 'w12', 'setdown'),

	'KMPS'					=> $kmps,
	'MESSAGE'				=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '')
	);

$template->pparse('body');

site_bottom();

?>