<?php

class user
{
	// ---------------
	// Добавление записи в личное дело
	//
	function add_admin_log_message($user_id, $access, $type, $message)
	{
		global $db;

		//
		// Добавляем запись
		//
		$sql = "INSERT INTO " . LOG_TABLE . " " . $db->sql_build_array('INSERT', array(
			'log_user_id'		=> $user_id,
			'log_access'		=> $access,
			'log_time'			=> time(),
			'log_type'			=> $type,
			'log_text'			=> $message));
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу добавить запись...', '', __LINE__, __FILE__, $sql);
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Добавление сообщения в чат
	//
	function add_chat_message($userdata, $message, $recipients = false, $private = false, $sys = false)
	{
		global $db;

		//
		// Выбираем получателей
		//
		preg_match_all("/to \[(.*?)\]/i", $message, $matches);

		$msg_to = '';
		$private_message = false;

		if( count($matches[1]) == 0 )
		{
			preg_match_all("/private \[(.*?)\]/i", $message, $matches);

			if( count($matches[1]) > 0 )
			{
				$private_message = true;
			}
		}

		for( $i = 0; $i < count($matches[1]); $i++ )
		{
			if( $matches[1][$i] != '' )
			{
				$msg_to .= $matches[1][$i] . ', ';
			}
		}

		$msg_to = ( isset($msg_to) ) ? substr($msg_to, 0, -2) : NULL;
		$recipients = ( $sys && $recipients ) ? $recipients : $msg_to;
		$recipients = ( is_int($recipients) || $recipients == '' ) ? NULL : $recipients;
		// ----------

		//
		// Проверка для 0-ых уровней
		//
		if( $private_message == true && $userdata['user_level'] == 0 )
		{
			$cannot_insert = true;
		}
		else
		{
			$cannot_insert = false;
		}
		// ----------

		//
		// Добавляем запись
		//
		if( !$cannot_insert )
		{
			$sql = "INSERT INTO " . CHAT_TABLE . " " . $db->sql_build_array('INSERT', array(
				'msg_room'		=> $userdata['user_room'],
				'msg_time'		=> time(),
				'msg_to'		=> $recipients,
				'msg_private'	=> ( $sys ) ? $private : $private_message,
				'msg_sys'		=> ( $sys ) ? 1 : 0,
				'msg_author'	=> ( $sys ) ? NULL : $userdata['user_login'],
				'msg_text'		=> $message));
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу вставить запись в чат...', '', __LINE__, __FILE__, $sql);
			}
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Добавление записи в лог боя
	//
	function add_log_message($id, $message)
	{
		global $db;

		$sql = "INSERT INTO " . LOGS_TEXT_TABLE . " " . $db->sql_build_array('INSERT', array(
			'log_id'		=> $id,
			'log_text'		=> $message));
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу добавить запись в лог боя...', '', __LINE__, __FILE__, $sql);
		}
	}
	//
	// ---------------

	// ---------------
	// Проверка комнаты на пригодность для боя :)
	//
	function check_battle_room()
	{
		global $userdata;

		if( $userdata['user_room'] == '1.100.1.1' || $userdata['user_room'] == '1.100.1.2' || $userdata['user_room'] == '1.100.1.3' || $userdata['user_room'] == '1.100.1.4' || $userdata['user_room'] == '1.100.1.6.1' || $userdata['user_room'] == '1.100.1.6.2' || $userdata['user_room'] == '1.100.1.6.3' || $userdata['user_room'] == '1.100.1.7.1' || $userdata['user_room'] == '1.100.1.7.2' || $userdata['user_room'] == '1.100.1.7.3' || $userdata['user_room'] == '1.100.1.7.4' || $userdata['user_room'] == '1.100.1.8.1' || $userdata['user_room'] == '1.100.1.8.2' || $userdata['user_room'] == '1.100.1.8.3' || $userdata['user_room'] == '1.100.1.8.4' || $userdata['user_room'] == '1.100.1.8.5' || $userdata['user_room'] == '1.100.1.10' || $userdata['user_room'] == '1.100.1.11' || $userdata['user_room'] == '1.100.1.12' || $userdata['user_room'] == '1.100.1.13' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	//
	// ---------------

	// ---------------
	// Проверка требований вещей
	//
	function check_items_requires(&$userdata, &$items)
	{
		global $user;

		$userdata['check_items_requires'] = false;

		//
		// Основные вещи
		//
		for( $i = 1; $i < 16; $i++ )
		{
			if( $i == 10 && $userdata['user_w3'] == $userdata['user_w10'] )
			{

			}
			elseif( $userdata['user_w' . $i] > 0 )
			{
				if( $items['item_req_strength'][$i] > $userdata['user_strength'] || $items['item_req_agility'][$i] > $userdata['user_agility'] || $items['item_req_perception'][$i] > $userdata['user_perception'] || $items['item_req_vitality'][$i] > $userdata['user_vitality'] || $items['item_req_intellect'][$i] > $userdata['user_intellect'] || $items['item_req_wisdom'][$i] > $userdata['user_wisdom'] || $items['item_req_spirituality'][$i] > $userdata['user_spirituality'] || $items['item_req_freedom'][$i] > $userdata['user_freedom'] || $items['item_req_freedom_of_spirit'][$i] > $userdata['user_freedom_of_spirit'] || $items['item_req_holiness'][$i] > $userdata['user_holiness'] || $items['item_req_level'][$i] > $userdata['user_level'] || $items['item_req_knifes'][$i] > $userdata['user_knifes'] || $items['item_req_axes'][$i] > $userdata['user_axes'] || $items['item_req_clubs'][$i] > $userdata['user_clubs'] || $items['item_req_swords'][$i] > $userdata['user_swords'] || $items['item_req_staffs'][$i] > $userdata['user_staffs'] || $items['item_req_magic_air'][$i] > $userdata['user_magic_air'] || $items['item_req_magic_earth'][$i] > $userdata['user_magic_earth'] || $items['item_req_magic_fire'][$i] > $userdata['user_magic_fire'] || $items['item_req_magic_water'][$i] > $userdata['user_magic_water'] || $items['item_req_magic_light'][$i] > $userdata['user_magic_light'] || $items['item_req_magic_grey'][$i] > $userdata['user_magic_grey'] || $items['item_req_magic_dark'][$i] > $userdata['user_magic_dark'] )
				{
					// Снимаем вещь
					$user->item_setdown($i, $userdata);
					$userdata['check_items_requires'] = true;
				}
			}

			//
			// Рубашка
			//
			if( $i == 15 )
			{
				if( $userdata['user_w400'] > 0 )
				{
					if( $items['item_req_strength'][400] > $userdata['user_strength'] || $items['item_req_agility'][400] > $userdata['user_agility'] || $items['item_req_perception'][400] > $userdata['user_perception'] || $items['item_req_vitality'][400] > $userdata['user_vitality'] || $items['item_req_intellect'][400] > $userdata['user_intellect'] || $items['item_req_wisdom'][400] > $userdata['user_wisdom'] || $items['item_req_level'][400] > $userdata['user_level'] )
					{
						// Снимаем вещь
						$user->item_setdown(400, $userdata);
						$userdata['check_items_requires'] = true;
					}
				}
			}
			// ----------
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Проверка слотов
	//
	function check_items_slots(&$userdata, &$row)
	{
		global $db, $user;

		if( $row['item_slot'] == 3 && $row['item_secondhand'] == 1 )
		{
			//
			// Второе оружие
			//
			if( $userdata['user_w3'] > 0 && $userdata['user_w10'] > 0 )
			{
				$user->item_setdown('10', $userdata);
				$row['item_slot'] = 10;
			}
			elseif( $userdata['user_w3'] > 0 && $userdata['user_w10'] == 0 )
			{
				$row['item_slot'] = 10;
			}
			elseif( $userdata['user_w3'] == 0 && $userdata['user_w10'] > 0 )
			{
				$row['item_slot'] = 3;
			}

			$sql = "UPDATE " . ITEMS_TABLE . " SET item_slot = " . $row['item_slot'] . " WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}
		elseif( $row['item_slot'] == 3 && $row['item_twohand'] == 1 )
		{
			//
			// Двуручное оружие
			//
			if( $userdata['user_w3'] > 0 )
			{
				$user->item_setdown('3', $userdata);
			}

			if( $userdata['user_w10'] > 0 )
			{
				$user->item_setdown('10', $userdata);
			}
			// ----------
		}
		elseif( $row['item_slot'] >= 6 && $row['item_slot'] <= 8 )
		{
			//
			// Кольца
			//
			if( $userdata['user_w6'] > 0 && $userdata['user_w7'] > 0 && $userdata['user_w8'] > 0 )
			{
				$row['item_slot'] = 6;
			}
			elseif( $userdata['user_w6'] == 0 )
			{
				$row['item_slot'] = 6;
			}
			elseif( $userdata['user_w7'] == 0 )
			{
				$row['item_slot'] = 7;
			}
			elseif( $userdata['user_w8'] == 0 )
			{
				$row['item_slot'] = 8;
			}

			$sql = "UPDATE " . ITEMS_TABLE . " SET item_slot = " . $row['item_slot'] . " WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}
		elseif( $row['item_slot'] >= 100 && $row['item_slot'] <= 111 )
		{
			//
			// Свитки
			//
			if( $userdata['user_w100'] > 0 && $userdata['user_w101'] > 0 && $userdata['user_w102'] > 0 && $userdata['user_w103'] > 0 && $userdata['user_w104'] > 0 && $userdata['user_w105'] > 0 && $userdata['user_w106'] > 0 && $userdata['user_w107'] > 0 && $userdata['user_w108'] > 0 && $userdata['user_w109'] > 0 )
			{
				$row['item_slot'] = 100;
			}
			elseif( $userdata['user_w100'] == 0 )
			{
				$row['item_slot'] = 100;
			}
			elseif( $userdata['user_w101'] == 0 )
			{
				$row['item_slot'] = 101;
			}
			elseif( $userdata['user_w102'] == 0 )
			{
				$row['item_slot'] = 102;
			}
			elseif( $userdata['user_w103'] == 0 )
			{
				$row['item_slot'] = 103;
			}
			elseif( $userdata['user_w104'] == 0 )
			{
				$row['item_slot'] = 104;
			}
			elseif( $userdata['user_w105'] == 0 )
			{
				$row['item_slot'] = 105;
			}
			elseif( $userdata['user_w106'] == 0 )
			{
				$row['item_slot'] = 106;
			}
			elseif( $userdata['user_w107'] == 0 )
			{
				$row['item_slot'] = 107;
			}
			elseif( $userdata['user_w108'] == 0 )
			{
				$row['item_slot'] = 108;
			}
			elseif( $userdata['user_w109'] == 0 )
			{
				$row['item_slot'] = 109;
			}

			$sql = "UPDATE " . ITEMS_TABLE . " SET item_slot = " . $row['item_slot'] . " WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}
	}
	//
	// ---------------

	// ---------------
	// Название города
	//
	function city_name($town, $type = 'text')
	{
		if( $type == 'text' )
		{
			switch( $town )
			{
				case 'capital':			$town_name = 'Capital city'; break;
				case 'angels':			$town_name = 'Angels city'; break;
				case 'demons':			$town_name = 'Demons city'; break;
				case 'devils':			$town_name = 'Devils city'; break;
				case 'sun':				$town_name = 'Suncity'; break;
				case 'emeralds':		$town_name = 'Emeralds city'; break;
				case 'sand':			$town_name = 'Sandcity'; break;
				case 'moon':			$town_name = 'Mooncity'; break;
				case 'newcapital':		$town_name = 'New Capital city'; break;
				default:				$town_name = $town; break;
			}
		}
		elseif( $type == 'int' )
		{
			switch( $town )
			{
				case 'capital':			$town_name = 1; break;
				case 'angels':			$town_name = 2; break;
				case 'demons':			$town_name = 3; break;
				case 'devils':			$town_name = 4; break;
				case 'sun':				$town_name = 5; break;
				case 'emeralds':		$town_name = 6; break;
				case 'sand':			$town_name = 7; break;
				case 'moon':			$town_name = 8; break;
				case 'newcapital':		$town_name = 10; break;
				default:				$town_name = 1; break;
			}
		}

		return $town_name;
	}
	//
	// ---------------

	// ---------------
	// Характеристика и её составляющие
	//
	function characteristic_full($name, $items, $row)
	{
		global $user;

		// Массивы с цветами
		$green_colours = array('#004000', '#006100', '#006700', '#007500', '#007B00', '#007E00', '#008200', '#008800', '#008C00', '#009A00', '#00A000');
		$red_colours = array('#A00000');

		// Требуемый уровень
		$requirement_level = array(
			'strength'			=> 0,
			'agility'			=> 0,
			'perception'		=> 0,
			'vitality'			=> 0,
			'intellect'			=> 4,
			'wisdom'			=> 7,
			'spirituality'		=> 10,
			'freedom'			=> 13,
			'freedom_of_spirit'	=> 16,
			'holiness'			=> 19
		);

		$string = ( $row['user_level'] >= $requirement_level[$name] ) ? $user->characteristic_name($name) . ': ' : '';

		$string .= ( isset($items[$name]) > 0 && $row['user_level'] >= $requirement_level[$name] ) ? '<font color="' . $green_colours[mt_rand(0, count($green_colours) - 1)] . '"><b>' . $row['user_' . $name] . '</b></font> <small>(' . ( $row['user_' . $name] - $items[$name] ) . ' + ' . $items[$name] . ')</small><br />' : ( ( isset($items[$name]) < 0 && $row['user_level'] >= $requirement_level[$name] ) ? '<font color="' . $red_colours[mt_rand(0, count($red_colours) - 1)] . '"><b>' . $row['user_' . $name] . '</b></font> <small>(' . ( $row['user_' . $name] - $items[$name] ) . ' - ' . ( $items[$name] * -1 ) . ')</small><br />' : ( ( $row['user_level'] >= $requirement_level[$name] ) ? '<b>' . $row['user_' . $name] . '</b><br />' : ''));

		return $string;
	}
	//
	// ---------------

	// ---------------
	// Название параметра/модификатора
	//
	function characteristic_name($name)
	{
		switch( $name )
		{
			case 'max_hp':					$name = 'Уровень жизни (HP)'; break;
			case 'hpspeed':					$name = 'Восстановление HP (%)'; break;
			case 'max_mana':				$name = 'Уровень маны'; break;
			case 'manaspeed':				$name = 'Восстановление маны (%)'; break;
			case 'strength':				$name = 'Сила'; break;
			case 'agility':					$name = 'Ловкость'; break;
			case 'perception':				$name = 'Интуиция'; break;
			case 'vitality':				$name = 'Выносливость'; break;
			case 'intellect':				$name = 'Интеллект'; break;
			case 'wisdom':					$name = 'Мудрость'; break;
			case 'spirituality':			$name = 'Духовность'; break;
			case 'freedom':					$name = 'Воля'; break;
			case 'freedom_of_spirit':		$name = 'Свобода духа'; break;
			case 'holiness':				$name = 'Божественность'; break;
			case 'protect_damage':			$name = 'Защита от урона'; break;
			case 'protect_piercing':		$name = 'Защита от колющего урона'; break;
			case 'protect_chopping':		$name = 'Защита от рубящего урона'; break;
			case 'protect_crushing':		$name = 'Защита от дробящего урона'; break;
			case 'protect_cutting':			$name = 'Защита от режущего урона'; break;
			case 'protect_magic':			$name = 'Защита от магии'; break;
			case 'protect_air':				$name = 'Защита от магии воздуха'; break;
			case 'protect_earth':			$name = 'Защита от магии земли'; break;
			case 'protect_fire':			$name = 'Защита от магии огня'; break;
			case 'protect_water':			$name = 'Защита от магии воды'; break;
			case 'mf_power_damage':			$name = 'Мф. мощности урона (%)'; break;
			case 'mf_power_piercing':		$name = 'Мф. мощности колющего урона (%)'; break;
			case 'mf_power_chopping':		$name = 'Мф. мощности рубящего урона (%)'; break;
			case 'mf_power_crushing':		$name = 'Мф. мощности дробящего урона (%)'; break;
			case 'mf_power_cutting':		$name = 'Мф. мощности режущего урона (%)'; break;
			case 'mf_power_magic':			$name = 'Мф. мощности магии стихий (%)'; break;
			case 'mf_power_air':			$name = 'Мф. мощности магии воздуха (%)'; break;
			case 'mf_power_earth':			$name = 'Мф. мощности магии земли (%)'; break;
			case 'mf_power_fire':			$name = 'Мф. мощности магии огня (%)'; break;
			case 'mf_power_water':			$name = 'Мф. мощности магии воды (%)'; break;
			case 'mf_power_critical_hit':	$name = 'Мф. мощности критического удара (%)'; break;
			case 'mf_critical_hit':			$name = 'Мф. критического удара (%)'; break;
			case 'mf_anticritical_hit':		$name = 'Мф. против критического удара (%)'; break;
			case 'mf_dodging':				$name = 'Мф. увертывания (%)'; break;
			case 'mf_antidodging':			$name = 'Мф. против увертывания (%)'; break;
			case 'mf_counterblow':			$name = 'Мф. контрудара (%)'; break;
			case 'mf_shield_block':			$name = 'Мф. блока щитом (%)'; break;
			case 'mf_parry':				$name = 'Мф. парирования (%)'; break;
			case 'mf_hit_through_armour':	$name = 'Мф. удара сквозь броню (%)'; break;
			default:						$name = ''; break;
		}

		return $name;
	}
	//
	// ---------------

	// ---------------
	// Преобразуем время
	//
	function create_time($time)
	{
		// Дни
		$days = ( $time >= 86400 ) ? intval($time / 86400) : 0;
		$days = ( $days > 0 ) ? $days . ' дн. ' : '';
		$time -= ( $time >= 86400 ) ? 86400 * $days : 0;

		// Часы
		$hours = ( $time >= 3600 ) ? intval($time / 3600) : 0;
		$hours = ( $hours > 0 ) ? $hours . ' ч. ' : '';
		$time -= ( $time >= 3600 ) ? 3600 * $hours : 0;

		// Минуты
		$minutes = ( $time >= 60 ) ? intval($time / 60) : 0;
		$minutes = ( $minutes > 0 ) ? $minutes . ' мин.' : '';
		$time -= ( $time >= 60 ) ? 60 * $minutes : 0;

		$seconds = ( $time >= 1 && $days == 0 && $hours == 0 && $minutes == 0 ) ? $time : 0;
		$seconds = ( $seconds > 0 ) ? $seconds . ' сек.' : '';

		return $days . $hours . $minutes . $seconds;
	}
	//
	// ---------------

	// ---------------
	// Данные для скрипта
	//
	function drwfl($userdata)
	{
		$drwfl = '<script>drwfl("' . $userdata['user_login'] . '",' . $userdata['user_id'] . ',"' . $userdata['user_level'] . '",' . $userdata['user_align'] . ',"' . $userdata['user_klan'] . '");</script>';
//		$drwfl = '<script>drwfl("<i>невидимка</i>",-1,-1,0,"")</script>';

		return $drwfl;
	}
	//
	// ---------------

	// ---------------
	// Использование эликсира
	//
	function elixir_damage(&$userdata, $row)
	{
		global $db;

		if( ( $row['item_current_durability'] + 1 ) < $row['item_max_durability'] )
		{
			$sql = "UPDATE " . ITEMS_TABLE . " SET item_current_durability = (item_current_durability + 1) WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
		}
		elseif( ( $row['item_current_durability'] + 1 ) >= $row['item_max_durability'] )
		{
			$sql = "UPDATE " . ITEMS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
				'item_inventory_otdel'			=> 4,
				'item_artefact'					=> 0,
				'item_fit'						=> '',
				'item_fit_login'				=> '',
				'item_name'						=> 'Пустая Бутыль',
				'item_img'						=> 'elixir_empty',
				'item_align'					=> 0,
				'item_price'					=> 0.1,
				'item_weight'					=> 1,
				'item_current_durability'		=> 0,
				'item_max_durability'			=> 1,
				'item_application_time'			=> 0,
				'item_magic_time'				=> 0,
				'item_start_lifetime'			=> time(),
				'item_req_strength'				=> 0,
				'item_req_agility'				=> 0,
				'item_req_perception'			=> 0,
				'item_req_vitality'				=> 0,
				'item_req_intellect'			=> 0,
				'item_req_wisdom'				=> 0,
				'item_req_level'				=> 0,
				'item_req_knifes'				=> 0,
				'item_req_axes'					=> 0,
				'item_req_clubs'				=> 0,
				'item_req_swords'				=> 0,
				'item_req_staffs'				=> 0,
				'item_strength'					=> 0,
				'item_agility'					=> 0,
				'item_perception'				=> 0,
				'item_intellect'				=> 0,
				'item_hpspeed'					=> 0,
				'item_manaspeed'				=> 0,
				'item_decrease_usage_mana'		=> 0,
				'item_protect_damage'			=> 0,
				'item_protect_piercing'			=> 0,
				'item_protect_chopping'			=> 0,
				'item_protect_crushing'			=> 0,
				'item_protect_cutting'			=> 0,
				'item_protect_magic'			=> 0,
				'item_protect_air'				=> 0,
				'item_protect_earth'			=> 0,
				'item_protect_fire'				=> 0,
				'item_protect_water'			=> 0,
				'item_reduce_protect_magic'		=> 0,
				'item_reduce_protect_air'		=> 0,
				'item_reduce_protect_earth'		=> 0,
				'item_reduce_protect_fire'		=> 0,
				'item_reduce_protect_water'		=> 0,
				'item_mf_power_damage'			=> 0,
				'item_mf_power_piercing'		=> 0,
				'item_mf_power_chopping'		=> 0,
				'item_mf_power_crushing'		=> 0,
				'item_mf_power_cutting'			=> 0,
				'item_mf_power_magic'			=> 0,
				'item_mf_power_air'				=> 0,
				'item_mf_power_earth'			=> 0,
				'item_mf_power_fire'			=> 0,
				'item_mf_power_water'			=> 0,
				'item_mf_power_critical_hit'	=> 0,
				'item_mf_critical_hit'			=> 0,
				'item_mf_anticritical_hit'		=> 0,
				'item_mf_dodging'				=> 0,
				'item_mf_antidodging'			=> 0,
				'item_mf_counterblow'			=> 0,
				'item_mf_shield_block'			=> 0,
				'item_mf_parry'					=> 0,
				'item_mf_hit_through_armour'	=> 0,
				'item_can_repair'				=> 0,
				'item_min_hit'					=> 0,
				'item_max_hit'					=> 0,
				'item_comment'					=> '')) . " WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
		}
	}
	//
	// ---------------

	// ---------------
	// Одетые вещи
	//
	function get_equip_items($userdata, $gifts = false, $type = false)
	{
		global $db;

		//
		// Получаем данные одетых вещей
		//
		$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id`= " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещей...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Определяем нужные переменные
		//
		if( $type == 'inf' )
		{
			$items['strength'] = 0;
			$items['agility'] = 0;
			$items['perception'] = 0;
			$items['intellect'] = 0;
		}

		$items['flowers_count'] = 0;
		$items['plus_weight'] = 0;
		$items['weight'] = 0;
		$n = 0;
		// ----------

		while( $row = $db->sql_fetchrow($result) )
		{
			$items['plus_weight'] += $row['item_plus_weight'];

			if( $row['item_is_equip'] == 0 )
			{
				//
				// Подарки
				//
				if( $row['item_gift_from_real'] != '' )
				{
					if( $row['item_type'] == 'flowers' )
					{
						$items['item_gift_desc']['flowers'][$n] = $row['item_gift_desc'];
						$items['item_gift_from']['flowers'][$n] = $row['item_gift_from'];
						$items['item_gift_from_real']['flowers'][$n] = $row['item_gift_from_real'];
						$items['item_img']['flowers'][$n] = $row['item_img'];
						$items['flowers_count'] += 1;

						$n++;
					}
				}
				// ----------

				$items['weight'] += $row['item_weight'];
			}
			else
			{
				//
				// Получаем данные
				//
				if( $type == 'inf' )
				{
					$items['strength'] += $row['item_strength'];
					$items['agility'] += $row['item_agility'];
					$items['perception'] += $row['item_perception'];
					$items['intellect'] += $row['item_intellect'];
				}

				// Требования
				$items['item_req_strength'][$row['item_slot']] = $row['item_req_strength'];
				$items['item_req_agility'][$row['item_slot']] = $row['item_req_agility'];
				$items['item_req_perception'][$row['item_slot']] = $row['item_req_perception'];
				$items['item_req_vitality'][$row['item_slot']] = $row['item_req_vitality'];
				$items['item_req_intellect'][$row['item_slot']] = $row['item_req_intellect'];
				$items['item_req_wisdom'][$row['item_slot']] = $row['item_req_wisdom'];
				$items['item_req_level'][$row['item_slot']] = $row['item_req_level'];
				$items['item_req_inbuild_spell'][$row['item_slot']] = ( $type == 'battle' ) ? $row['item_req_inbuild_spell'] : '';

				// Броня
				$items['item_armour_head'][$row['item_slot']] = $row['item_armour_head'];
				$items['item_armour_body'][$row['item_slot']] = $row['item_armour_body'];
				$items['item_armour_waist'][$row['item_slot']] = $row['item_armour_waist'];
				$items['item_armour_leg'][$row['item_slot']] = $row['item_armour_leg'];
				$items['item_mf_armour_head'][$row['item_slot']] = $row['item_mf_armour_head'];
				$items['item_mf_armour_body'][$row['item_slot']] = $row['item_mf_armour_body'];
				$items['item_mf_armour_waist'][$row['item_slot']] = $row['item_mf_armour_waist'];
				$items['item_mf_armour_leg'][$row['item_slot']] = $row['item_mf_armour_leg'];

				// Встроенная магия
				$items['item_inbuild_magic'][$row['item_slot']] = $row['item_inbuild_magic'];
				$items['item_inbuild_magic_desc'][$row['item_slot']] = $row['item_inbuild_magic_desc'];
				$items['item_inbuild_magic_num'][$row['item_slot']] = $row['item_inbuild_magic_num'];
				$items['item_inbuild_magic_time'][$row['item_slot']] = $row['item_inbuild_magic_time'];

				// Характеристики
				$items['item_artefact'][$row['item_slot']] = $row['item_artefact'];
				$items['item_current_durability'][$row['item_slot']] = $row['item_current_durability'];
				$items['item_etching'][$row['item_slot']] = $row['item_etching'];
				$items['item_hp'][$row['item_slot']] = $row['item_hp'];
				$items['item_id'][$row['item_slot']] = $row['item_id'];
				$items['item_img'][$row['item_slot']] = $row['item_img'];
				$items['item_max_durability'][$row['item_slot']] = $row['item_max_durability'];
				$items['item_max_hit'][$row['item_slot']] = $row['item_max_hit'];
				$items['item_min_hit'][$row['item_slot']] = $row['item_min_hit'];
				$items['item_name'][$row['item_slot']] = $row['item_name'];
				$items['item_price'][$row['item_slot']] = $row['item_price'];
				$items['item_type'][$row['item_slot']] = $row['item_type'];

				if( $type == 'battle' )
				{
					// Тип атаки
					$items['item_ice_attacks'][$row['item_slot']] = $row['item_ice_attacks'];
					$items['item_fire_attacks'][$row['item_slot']] = $row['item_fire_attacks'];
					$items['item_electric_attacks'][$row['item_slot']] = $row['item_electric_attacks'];
					$items['item_light_attacks'][$row['item_slot']] = $row['item_light_attacks'];
					$items['item_dark_attacks'][$row['item_slot']] = $row['item_dark_attacks'];
					$items['item_piercing_attacks'][$row['item_slot']] = $row['item_piercing_attacks'];
					$items['item_chopping_attacks'][$row['item_slot']] = $row['item_chopping_attacks'];
					$items['item_crushing_attacks'][$row['item_slot']] = $row['item_crushing_attacks'];
					$items['item_cutting_attacks'][$row['item_slot']] = $row['item_cutting_attacks'];
					// ----------
				}
			}
		}

		return $items;
	}
	//
	// ---------------

	// ---------------
	// Определение времени перехода
	//
	function get_going_time()
	{
		global $userdata;

		return ( $userdata['user_room_time'] - time() <= 0 || $userdata['user_access_level'] == ADMIN || $userdata['user_bot'] ) ? 0 : $userdata['user_room_time'] - time();
	}
	//
	// ---------------

	// ---------------
	// Получение возможных ссылок на переходы
	//
	function get_room_links($room)
	{
		global $root_path;

		$links = array();

		switch( $room )
		{
			case '1.100.1.6.5':
				// ---------------
				// Этаж 2
				//
				$links['desc'][] = 'Время перехода: 10 сек.'; // Бойцовский Клуб
				$links['path'][] = '1.100.1.9';

				$links['desc'][] = 'Время перехода: 10 сек.'; // Этаж 3
				$links['path'][] = '1.100.1.7.5';
				break;
				//
				// ---------------
			case '1.100.1.7.5':
				// ---------------
				// Этаж 3
				//
				$links['desc'][] = 'Время перехода: 10 сек.'; // Этаж 2
				$links['path'][] = '1.100.1.6.5';
				break;
				//
				// ---------------
			case '1.100.1.8.6':
				// ---------------
				// Залы
				//
				$links['desc'][] = 'Время перехода: 10 сек.'; // Бойцовский Клуб
				$links['path'][] = '1.100.1.9';
				break;
				//
				// ---------------
			case '1.100.1.9':
				// ---------------
				// Бойцовский Клуб
				//
				$links['desc'][] = 'Время перехода: 15 сек.'; // Центральная площадь
				$links['path'][] = '1.100';

				$links['desc'][] = 'Время перехода: 10 сек.'; // Залы
				$links['path'][] = '1.100.1.8.6';

				$links['desc'][] = 'Время перехода: 10 сек.'; // Этаж 2
				$links['path'][] = '1.100.1.6.5';
				break;
				//
				// ---------------
			case '1.100.1.101':
				// ---------------
				//
				//
				$links['desc'][] = 'Время перехода: 15 сек.'; // Центральная площадь
				$links['path'][] = '1.100';
				break;
				//
				// ---------------
			case '1.100.1.102':
				// ---------------
				// Магазин
				//
				$links['desc'][] = 'Время перехода: 15 сек.'; // Центральная площадь
				$links['path'][] = '1.100';
//				$links['desc'][] = 'Нажми на меня';
//				$links['path'][] = '1.101';
				break;
				//
				// ---------------
			case '1.100.1.110':
				//
				// Банк
				//
				$links['desc'][] = 'Время перехода: 15 сек.'; // Страшилкина улица
				$links['path'][] = '1.107';
				break;
				//
				//
			default:
				$links['desc'][] = '';
				$links['path'][] = '';
		}

		return $links;
	}
	//
	// ---------------

	// ---------------
	// Получение названия комнаты
	//
	function get_room_name($room)
	{
		switch( $room )
		{
			case '1':				$name = 'Город'; break;
			case '1.100':			$name = 'Центральная Площадь'; break;
			case '1.107':			$name = 'Страшилкина улица'; break;
			case '1.111':			$name = 'Парк развлечений'; break;
			case '1.120':			$name = 'Большая торговая ул.'; break;
			case '1.100.1.1':		$name = 'Комната для новичков'; break;
			case '1.100.1.2':		$name = 'Комната для новичков 2'; break;
			case '1.100.1.3':		$name = 'Комната для новичков 3'; break;
			case '1.100.1.4':		$name = 'Комната для новичков 4'; break;
			case '1.100.1.5':		$name = 'Комната Перехода'; break;
			case '1.100.1.6.1':		$name = 'Рыцарский зал'; break;
			case '1.100.1.6.2':		$name = 'Торговый Зал'; break;
			case '1.100.1.6.3':		$name = 'Башня рыцарей-магов'; break;
			case '1.100.1.6.4':		$name = 'Комната Знахаря'; break;
			case '1.100.1.6.5':		$name = 'Этаж 2'; break;
			case '1.100.1.7.1':		$name = 'Колдовской мир'; break;
			case '1.100.1.7.2':		$name = 'Этажи духов'; break;
			case '1.100.1.7.3':		$name = 'Астральные миры'; break;
			case '1.100.1.7.4':		$name = 'Огненный мир'; break;
			case '1.100.1.7.5':		$name = 'Этаж 3'; break;
			case '1.100.1.8.1':		$name = 'Зал Паладинов'; break;
			case '1.100.1.8.2':		$name = 'Совет Белого Братства'; break;
			case '1.100.1.8.3':		$name = 'Зал Тьмы'; break;
			case '1.100.1.8.4':		$name = 'Царство Тьмы'; break;
			case '1.100.1.8.5':		$name = 'Зал Стихий'; break;
			case '1.100.1.8.6':		$name = 'Залы'; break;
			case '1.100.1.9':		$name = 'Бойцовский Клуб'; break;
			case '1.100.1.10':		$name = 'Зал воинов'; break;
			case '1.100.1.11':		$name = 'Зал воинов 2'; break;
			case '1.100.1.12':		$name = 'Зал воинов 3'; break;
			case '1.100.1.13':		$name = 'Будуар'; break;
			case '1.100.1.50':		$name = 'Секретная комната'; break;
			case '1.100.1.101':		$name = 'Ремонтная мастерская'; break;
			case '1.100.1.102':		$name = 'Магазин'; break;
			case '1.100.1.103':		$name = 'Вокзал'; break;
			case '1.100.1.105':		$name = 'Комиссионка'; break;
			case '1.100.1.106':		$name = 'Почтовое отделение'; break;
			case '1.100.1.108':		$name = 'Регистратура кланов'; break;
			case '1.100.1.109':		$name = 'Башня смерти'; break;
			case '1.100.1.110':		$name = 'Банк'; break;
			case '1.100.1.112':		$name = 'Маленькая беседка'; break;
			case '1.100.1.113':		$name = 'Средняя беседка'; break;
			case '1.100.1.114':		$name = 'Большая беседка'; break;
			case '1.100.1.115':		$name = 'Магазин Березка'; break;
			case '1.100.1.116':		$name = 'Оптовый магазин'; break;
			case '1.100.1.119':		$name = 'Цветочный магазин'; break;
			default:				$name = 'Комната не найдена'; break;
		}

		return $name;
	}
	//
	// ---------------

	// ---------------
	// Испортившийся предмет
	//
	function item_bad($row)
	{
		global $db, $user, $userdata;

		switch( $row['item_type'] )
		{
			case 'flowers':	$item_name = 'Увядший букет (' . $row['item_name'] . ')'; $item_img = 'just_junk'; break;
			case 'potion':	$item_name = 'Испортившийся эликсир (' . $row['item_name'] . ')'; $item_img = 'elixir_bad'; break;
			default:		$item_name = 'Какой-то мусор (' . $row['item_name'] . ')'; $item_img = 'just_junk'; break;
		}

		$sql = "UPDATE " . ITEMS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'item_inventory_otdel'			=> 4,
			'item_artefact'					=> 0,
			'item_fit'						=> '',
			'item_fit_login'				=> '',
			'item_name'						=> $item_name,
			'item_img'						=> $item_img,
			'item_align'					=> 0,
			'item_price'					=> 1,
			'item_weight'					=> 1,
			'item_current_durability'		=> 0,
			'item_max_durability'			=> 1,
			'item_application_time'			=> 15,
			'item_magic_time'				=> 0,
			'item_start_lifetime'			=> time(),
			'item_req_strength'				=> 0,
			'item_req_agility'				=> 0,
			'item_req_perception'			=> 0,
			'item_req_vitality'				=> 0,
			'item_req_intellect'			=> 0,
			'item_req_wisdom'				=> 0,
			'item_req_level'				=> 0,
			'item_req_knifes'				=> 0,
			'item_req_axes'					=> 0,
			'item_req_clubs'				=> 0,
			'item_req_swords'				=> 0,
			'item_req_staffs'				=> 0,
			'item_strength'					=> 0,
			'item_agility'					=> 0,
			'item_perception'				=> 0,
			'item_intellect'				=> 0,
			'item_hpspeed'					=> 0,
			'item_manaspeed'				=> 0,
			'item_decrease_usage_mana'		=> 0,
			'item_protect_damage'			=> 0,
			'item_protect_piercing'			=> 0,
			'item_protect_chopping'			=> 0,
			'item_protect_crushing'			=> 0,
			'item_protect_cutting'			=> 0,
			'item_protect_magic'			=> 0,
			'item_protect_air'				=> 0,
			'item_protect_earth'			=> 0,
			'item_protect_fire'				=> 0,
			'item_protect_water'			=> 0,
			'item_reduce_protect_magic'		=> 0,
			'item_reduce_protect_air'		=> 0,
			'item_reduce_protect_earth'		=> 0,
			'item_reduce_protect_fire'		=> 0,
			'item_reduce_protect_water'		=> 0,
			'item_mf_power_damage'			=> 0,
			'item_mf_power_piercing'		=> 0,
			'item_mf_power_chopping'		=> 0,
			'item_mf_power_crushing'		=> 0,
			'item_mf_power_cutting'			=> 0,
			'item_mf_power_magic'			=> 0,
			'item_mf_power_air'				=> 0,
			'item_mf_power_earth'			=> 0,
			'item_mf_power_fire'			=> 0,
			'item_mf_power_water'			=> 0,
			'item_mf_power_critical_hit'	=> 0,
			'item_mf_critical_hit'			=> 0,
			'item_mf_anticritical_hit'		=> 0,
			'item_mf_dodging'				=> 0,
			'item_mf_antidodging'			=> 0,
			'item_mf_counterblow'			=> 0,
			'item_mf_shield_block'			=> 0,
			'item_mf_parry'					=> 0,
			'item_mf_hit_through_armour'	=> 0,
			'item_can_repair'				=> 0,
			'item_min_hit'					=> 0,
			'item_max_hit'					=> 0,
			'item_comment'					=> '')) . " WHERE `item_id` = " . $row['item_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
		}

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'item_bad', 'У "' . $userdata['user_login'] . '" испортился предмет "' . $row['item_name'] . '"');
	}
	//
	// ---------------

	// ---------------
	// Выбрасывание вещи
	//
	function item_drop($id, $userdata, $type = false)
	{
		global $db;

		//
		// Получаем данные удаляемой вещи
		//
		$sql = "SELECT item_user_id, item_is_equip, item_name FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещи...', __LINE__, __FILE__, $sql);
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
		elseif( $row['item_is_equip'] )
		{
			$message = 'Для начала надо снять вещь ;)';
		}
		else
		{
			if( $type )
			{
				//
				// Выбрасывание вещей одного типа
				//
				$sql = "DELETE FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_name` = '" . $type . "'";
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу выбросить вещи одного типа...', '', __LINE__, __FILE__, $sql);
				}

				$message = 'Предметы "' . $row['item_name'] . '" выброшены';
				// ----------
			}
			else
			{
				//
				// Выбрасывание вещи
				//
				$sql = "DELETE FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $id;
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу выбросить вещь...', '', __LINE__, __FILE__, $sql);
				}

				$message = 'Предмет "' . $row['item_name'] . '" выброшен';
				// ----------
			}
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Починка вещей
	//
	function item_repair($user_id, $item_id, $repair_durability, $bot = false)
	{
		global $config, $db;

		// Определяем шанс и степерь поломки
		$chance = ( $repair_durability == 1 ) ? mt_rand(1, 105) : ( ( $repair_durability == 10 ) ? mt_rand(1, 130) : mt_rand(1, 200) );
		$damage = ( $bot == false && $chance > 100 ) ? mt_rand(1, 3) : 0;

		//
		// Обновляем данные вещи
		//
		$sql = "UPDATE " . ITEMS_TABLE . " SET item_current_durability = (item_current_durability - " . $repair_durability . "), item_max_durability = (item_max_durability - " . $damage . ") WHERE `item_id` = " . $item_id;
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем данные персонажа
		//
		if( $bot )
		{
			// Для бота
			$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money - " . ( 0.1 * $repair_durability ) . ") WHERE `bank_id` = " . $config['bots_bank_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные банковского счета...', '', __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			// Для обычного персонажа
			$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - " . ( 0.1 * $repair_durability ) . ") WHERE `user_id` = " . $user_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}
		}
		// ----------

		return $damage;
	}
	//
	// ---------------

	// ---------------
	// Надевание вещи
	//
	function item_set($id, &$userdata, $skip_redirect = false)
	{
		global $config, $db, $user;

		//
		// Получаем параметры вещи
		//
		$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		if( $row['item_id'] <= 0 )
		{
			site_message('Вещь не найдена в рюкзаке...');
		}

		// Определяем слот
		$user->check_items_slots($userdata, $row);

		//
		// Проверяем возможность одевания вещи...
		//
		if( $userdata['user_strength'] < $row['item_req_strength'] || $userdata['user_agility'] < $row['item_req_agility'] || $userdata['user_perception'] < $row['item_req_perception'] || $userdata['user_vitality'] < $row['item_req_vitality'] || $userdata['user_intellect'] < $row['item_req_intellect'] || $userdata['user_wisdom'] < $row['item_req_wisdom'] || $userdata['user_level'] < $row['item_req_level'] || $userdata['user_knifes'] < $row['item_req_knifes'] || $userdata['user_axes'] < $row['item_req_axes'] || $userdata['user_clubs'] < $row['item_req_clubs'] || $userdata['user_swords'] < $row['item_req_swords'] || $userdata['user_staffs'] < $row['item_req_staffs'] || $userdata['user_magic_air'] < $row['item_req_magic_air'] || $userdata['user_magic_earth'] < $row['item_req_magic_earth'] || $userdata['user_magic_fire'] < $row['item_req_magic_fire'] || $userdata['user_magic_water'] < $row['item_req_magic_water'] )
		{
			if( !$skip_redirect )
			{
				redirect('main.php?edit=');
			}
		}
		elseif( $row['item_align'] > 0 )
		{
			//
			// Проверка склонности
			//
			switch( $row['item_align'] )
			{
				case 1:
					// Вещи для паладинов
					if( $userdata['user_align'] < 1 && $userdata['user_align'] >= 2 )
					{
						redirect('main.php?edit=');
					}
					break;
				case '1.99':
					// Вещи для верховного паладина
					if( $userdata['user_align'] != '1.99' )
					{
						redirect('main.php?edit=');
					}
					break;
				case 3:
					// Вещи для темных
					if( $userdata['user_align'] < 3 && $userdata['user_align'] >= 4 )
					{
						redirect('main.php?edit=');
					}
					break;
				case '3.99':
					// Вещи для верховного тармана
					if( $userdata['user_align'] != '3.99' )
					{
						redirect('main.php?edit=');
					}
					break;
			}
			// ----------
		}
		// ----------

		// Обнуляем "Состояние"
		$user->obtain_status($userdata, '', 'set_items');

		//
		// Проверяем наличие вещи в этом слоте
		// Если вещь есть, то снимаем её
		//
		if( $userdata['user_w' . $row['item_slot']] > 0 )
		{
			$user->item_setdown($row['item_slot'], $userdata);
		}
		// ----------

		$userdata['user_hpspeed'] /= ( $userdata['user_bot'] && $config['fast_game'] ) ? 250 : ( ( $userdata['user_bot'] && !$config['fast_game'] ) ? 10 : 1);
		$userdata['user_hpspeed'] /= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : 1;
		$userdata['user_hpspeed'] /= ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1;
		$row['item_hpspeed'] = ( $row['item_hpspeed'] == 0 ) ? 0 : $row['item_hpspeed'];
		$twohand = ( $row['item_twohand'] == 1 ) ? 'user_w10 = ' . $id . ', ' : '';

		//
		// Прибавляем статы вещи персонажу
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $twohand . $db->sql_build_array('UPDATE', array(
			'user_attacks'								=> $userdata['user_attacks'] + $row['item_attacks'],
			'user_items_cost'						=> $userdata['user_items_cost'] + $row['item_price'],
			'user_current_hp'						=> $userdata['user_current_hp'],
			'user_max_hp'							=> $userdata['user_max_hp'] + $row['item_hp'],
			'user_hpspeed'							=> $userdata['user_hpspeed'] + $row['item_hpspeed'],
			'user_current_mana'					=> $userdata['user_current_mana'],
			'user_max_mana'						=> $userdata['user_max_mana'] + $row['item_mana'],
			'user_manaspeed'						=> $userdata['user_manaspeed'] + $row['item_manaspeed'],
			'user_start_regen'						=> time(),
			'user_start_regen_mana'				=> ( $userdata['user_max_mana'] + $row['item_mana'] > 0 ) ? time() : 0,
			'user_w' . $row['item_slot']			=> $id,
			'user_strength'							=> $userdata['user_strength'] + $row['item_strength'],
			'user_agility'								=> $userdata['user_agility'] + $row['item_agility'],
			'user_perception'						=> $userdata['user_perception'] + $row['item_perception'],
			'user_intellect'							=> $userdata['user_intellect'] + $row['item_intellect'],
			'user_mf_armour_head'				=> $userdata['user_mf_armour_head'] + $row['item_mf_armour_head'],
			'user_mf_armour_body'				=> $userdata['user_mf_armour_body'] + $row['item_mf_armour_body'],
			'user_mf_armour_waist'				=> $userdata['user_mf_armour_waist'] + $row['item_mf_armour_waist'],
			'user_mf_armour_leg'					=> $userdata['user_mf_armour_leg'] + $row['item_mf_armour_leg'],
			'user_armour_head'					=> $userdata['user_armour_head'] + $row['item_armour_head'],
			'user_armour_body'					=> $userdata['user_armour_body'] + $row['item_armour_body'],
			'user_armour_waist'					=> $userdata['user_armour_waist'] + $row['item_armour_waist'],
			'user_armour_leg'						=> $userdata['user_armour_leg'] + $row['item_armour_leg'],
			'user_protect_damage'				=> $userdata['user_protect_damage'] + $row['item_protect_damage'],
			'user_protect_piercing'				=> $userdata['user_protect_piercing'] + $row['item_protect_piercing'],
			'user_protect_chopping'				=> $userdata['user_protect_chopping'] + $row['item_protect_chopping'],
			'user_protect_crushing'				=> $userdata['user_protect_crushing'] + $row['item_protect_crushing'],
			'user_protect_cutting'					=> $userdata['user_protect_cutting'] + $row['item_protect_cutting'],
			'user_protect_magic'					=> $userdata['user_protect_magic'] + $row['item_protect_magic'],
			'user_protect_air'						=> $userdata['user_protect_air'] + $row['item_protect_air'],
			'user_protect_earth'					=> $userdata['user_protect_earth'] + $row['item_protect_earth'],
			'user_protect_fire'						=> $userdata['user_protect_fire'] + $row['item_protect_fire'],
			'user_protect_water'					=> $userdata['user_protect_water'] + $row['item_protect_water'],
			'user_reduce_protect_magic'		=> $userdata['user_reduce_protect_magic'] + $row['item_reduce_protect_magic'],
			'user_reduce_protect_air'			=> $userdata['user_reduce_protect_air'] + $row['item_reduce_protect_air'],
			'user_reduce_protect_earth'		=> $userdata['user_reduce_protect_earth'] + $row['item_reduce_protect_earth'],
			'user_reduce_protect_fire'			=> $userdata['user_reduce_protect_fire'] + $row['item_reduce_protect_fire'],
			'user_reduce_protect_water'		=> $userdata['user_reduce_protect_water'] + $row['item_reduce_protect_water'],
			'user_mf_power_damage'			=> $userdata['user_mf_power_damage'] + $row['item_mf_power_damage'],
			'user_mf_power_piercing'			=> $userdata['user_mf_power_piercing'] + $row['item_mf_power_piercing'],
			'user_mf_power_chopping'			=> $userdata['user_mf_power_chopping'] + $row['item_mf_power_chopping'],
			'user_mf_power_crushing'			=> $userdata['user_mf_power_crushing'] + $row['item_mf_power_crushing'],
			'user_mf_power_cutting'				=> $userdata['user_mf_power_cutting'] + $row['item_mf_power_cutting'],
			'user_mf_power_magic'				=> $userdata['user_mf_power_magic'] + $row['item_mf_power_magic'],
			'user_mf_power_air'					=> $userdata['user_mf_power_air'] + $row['item_mf_power_air'],
			'user_mf_power_earth'				=> $userdata['user_mf_power_earth'] + $row['item_mf_power_earth'],
			'user_mf_power_fire'					=> $userdata['user_mf_power_fire'] + $row['item_mf_power_fire'],
			'user_mf_power_water'				=> $userdata['user_mf_power_water'] + $row['item_mf_power_water'],
			'user_mf_power_critical_hit'		=> $userdata['user_mf_power_critical_hit'] + $row['item_mf_power_critical_hit'],
			'user_mf_critical_hit'					=> $userdata['user_mf_critical_hit'] + $row['item_mf_critical_hit'],
			'user_mf_anticritical_hit'				=> $userdata['user_mf_anticritical_hit'] + $row['item_mf_anticritical_hit'],
			'user_mf_dodging'						=> $userdata['user_mf_dodging'] + $row['item_mf_dodging'],
			'user_mf_antidodging'					=> $userdata['user_mf_antidodging'] + $row['item_mf_antidodging'],
			'user_mf_counterblow'				=> $userdata['user_mf_counterblow'] + $row['item_mf_counterblow'],
			'user_mf_shield_block'				=> $userdata['user_mf_shield_block'] + $row['item_mf_shield_block'],
			'user_mf_parry'							=> $userdata['user_mf_parry'] + $row['item_mf_parry'],
			'user_mf_hit_through_armour'		=> $userdata['user_mf_hit_through_armour'] + $row['item_mf_hit_through_armour'],
			'user_knifes'								=> $userdata['user_knifes'] + $row['item_knifes'],
			'user_axes'								=> $userdata['user_axes'] + $row['item_axes'],
			'user_clubs'								=> $userdata['user_clubs'] + $row['item_clubs'],
			'user_swords'								=> $userdata['user_swords'] + $row['item_swords'],
			'user_staffs'								=> $userdata['user_staffs'] + $row['item_staffs'],
			'user_magic_air'							=> $userdata['user_magic_air'] + $row['item_magic_air'],
			'user_magic_earth'						=> $userdata['user_magic_earth'] + $row['item_magic_earth'],
			'user_magic_fire'						=> $userdata['user_magic_fire'] + $row['item_magic_fire'],
			'user_magic_water'						=> $userdata['user_magic_water'] + $row['item_magic_water'],
			'user_magic_light'						=> $userdata['user_magic_light'] + $row['item_magic_light'],
			'user_magic_grey'						=> $userdata['user_magic_grey'] + $row['item_magic_grey'],
			'user_magic_dark'						=> $userdata['user_magic_dark'] + $row['item_magic_dark'])) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу одеть вещь...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Убираем вещь из рюкзака
		//
		$sql = "UPDATE " . ITEMS_TABLE . " SET item_is_equip = 1, item_sort_order = 0 WHERE `item_id` = " . $id;
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу одеть вещь...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем параметры персонажа
		//
		if( $row['item_twohand'] == 1 )
		{
			$userdata['user_w10'] = $row['item_id'];
		}

		$userdata['user_attacks'] += $row['item_attacks'];
		$userdata['user_items_cost'] += $row['item_price'];
		$userdata['user_max_hp'] += $row['item_hp'];
		$userdata['user_hpspeed'] += $row['item_hpspeed'];
		$userdata['user_hpspeed'] *= ( $userdata['user_bot'] && $config['fast_game'] ) ? 250 : ( ( $userdata['user_bot'] && !$config['fast_game'] ) ? 10 : 1);
		$userdata['user_hpspeed'] *= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : ( ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1);
		$userdata['user_max_mana'] += $row['item_mana'];
		$userdata['user_manaspeed'] += $row['item_manaspeed'];
		$userdata['user_w' . $row['item_slot']] = $row['item_id'];
		$userdata['user_strength'] += $row['item_strength'];
		$userdata['user_agility'] += $row['item_agility'];
		$userdata['user_perception'] += $row['item_perception'];
		$userdata['user_intellect'] += $row['item_intellect'];
		$userdata['user_mf_armour_head'] += $row['item_mf_armour_head'];
		$userdata['user_mf_armour_body'] += $row['item_mf_armour_body'];
		$userdata['user_mf_armour_waist'] += $row['item_mf_armour_waist'];
		$userdata['user_mf_armour_leg'] += $row['item_mf_armour_leg'];
		$userdata['user_armour_head'] += $row['item_armour_head'];
		$userdata['user_armour_body'] += $row['item_armour_body'];
		$userdata['user_armour_waist'] += $row['item_armour_waist'];
		$userdata['user_armour_leg'] += $row['item_armour_leg'];
		$userdata['user_protect_damage'] += $row['item_protect_damage'];
		$userdata['user_protect_piercing'] += $row['item_protect_piercing'];
		$userdata['user_protect_chopping'] += $row['item_protect_chopping'];
		$userdata['user_protect_crushing'] += $row['item_protect_crushing'];
		$userdata['user_protect_cutting'] += $row['item_protect_cutting'];
		$userdata['user_protect_magic'] += $row['item_protect_magic'];
		$userdata['user_protect_air'] += $row['item_protect_air'];
		$userdata['user_protect_earth'] += $row['item_protect_earth'];
		$userdata['user_protect_fire'] += $row['item_protect_fire'];
		$userdata['user_protect_water'] += $row['item_protect_water'];
		$userdata['user_reduce_protect_magic'] += $row['item_reduce_protect_magic'];
		$userdata['user_reduce_protect_air'] += $row['item_reduce_protect_air'];
		$userdata['user_reduce_protect_earth'] += $row['item_reduce_protect_earth'];
		$userdata['user_reduce_protect_fire'] += $row['item_reduce_protect_fire'];
		$userdata['user_reduce_protect_water'] += $row['item_reduce_protect_water'];
		$userdata['user_mf_power_damage'] += $row['item_mf_power_damage'];
		$userdata['user_mf_power_piercing'] += $row['item_mf_power_piercing'];
		$userdata['user_mf_power_chopping'] += $row['item_mf_power_chopping'];
		$userdata['user_mf_power_crushing'] += $row['item_mf_power_crushing'];
		$userdata['user_mf_power_cutting'] += $row['item_mf_power_cutting'];
		$userdata['user_mf_power_magic'] += $row['item_mf_power_magic'];
		$userdata['user_mf_power_air'] += $row['item_mf_power_air'];
		$userdata['user_mf_power_earth'] += $row['item_mf_power_earth'];
		$userdata['user_mf_power_fire'] += $row['item_mf_power_fire'];
		$userdata['user_mf_power_water'] += $row['item_mf_power_water'];
		$userdata['user_mf_power_critical_hit'] += $row['item_mf_power_critical_hit'];
		$userdata['user_mf_critical_hit'] += $row['item_mf_critical_hit'];
		$userdata['user_mf_anticritical_hit'] += $row['item_mf_anticritical_hit'];
		$userdata['user_mf_dodging'] += $row['item_mf_dodging'];
		$userdata['user_mf_antidodging'] += $row['item_mf_antidodging'];
		$userdata['user_mf_counterblow'] += $row['item_mf_counterblow'];
		$userdata['user_mf_shield_block'] += $row['item_mf_shield_block'];
		$userdata['user_mf_parry'] += $row['item_mf_parry'];
		$userdata['user_mf_hit_through_armour'] += $row['item_mf_hit_through_armour'];
		$userdata['user_knifes'] += $row['item_knifes'];
		$userdata['user_axes'] += $row['item_axes'];
		$userdata['user_clubs'] += $row['item_clubs'];
		$userdata['user_swords'] += $row['item_swords'];
		$userdata['user_staffs'] += $row['item_staffs'];
		$userdata['user_magic_air'] += $row['item_magic_air'];
		$userdata['user_magic_earth'] += $row['item_magic_earth'];
		$userdata['user_magic_fire'] += $row['item_magic_fire'];
		$userdata['user_magic_water'] += $row['item_magic_water'];
		$userdata['user_magic_light'] += $row['item_magic_light'];
		$userdata['user_magic_grey'] += $row['item_magic_grey'];
		$userdata['user_magic_dark'] += $row['item_magic_dark'];

		$user->obtain_status($userdata);
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Снятие вещи
	//
	function item_setdown($slot, &$userdata)
	{
		global $config, $db, $user;

		//
		// Проверяем наличие вещи
		//
		if( $userdata['user_w' . $slot] == 0 )
		{
			redirect('main.php?edit=');
		}
		// ----------

		//
		// Получаем данные вещи
		//
		$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 1 AND `item_slot` = " . $slot;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		//
		// Проверки
		//
		if( !$row )
		{
			site_message('Вещь не найдена...');
		}
		// ----------

		// Обнуляем «Состояние»
		$user->obtain_status($userdata, '', 'set_items');

		$userdata['user_hpspeed'] /= ( $userdata['user_bot'] && $config['fast_game'] ) ? 250 : ( ( $userdata['user_bot'] && !$config['fast_game'] ) ? 10 : 1);
		$userdata['user_hpspeed'] /= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : 1;
		$userdata['user_hpspeed'] /= ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1;
		$row['item_hpspeed'] = ( $row['item_hpspeed'] == 0 ) ? 0 : $row['item_hpspeed'];
		$twohand = ( $row['item_twohand'] == 1 ) ? 'user_w10 = 0, ' : '';

		//
		// Сбрасываем статы вещи с персонажа
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $twohand . $db->sql_build_array('UPDATE', array(
			'user_attacks'						=> $userdata['user_attacks'] - $row['item_attacks'],
			'user_items_cost'					=> $userdata['user_items_cost'] - $row['item_price'],
			'user_current_hp'					=> $userdata['user_current_hp'],
			'user_max_hp'						=> $userdata['user_max_hp'] - $row['item_hp'],
			'user_hpspeed'						=> $userdata['user_hpspeed'] - $row['item_hpspeed'],
			'user_current_mana'					=> $userdata['user_current_mana'],
			'user_max_mana'						=> $userdata['user_max_mana'] - $row['item_mana'],
			'user_manaspeed'					=> $userdata['user_manaspeed'] - $row['item_manaspeed'],
			'user_start_regen'					=> time(),
			'user_start_regen_mana'				=> ( $userdata['user_max_mana'] - $row['item_mana'] > 0 ) ? time() : 0,
			'user_w' . $slot					=> 0,
			'user_strength'						=> $userdata['user_strength'] - $row['item_strength'],
			'user_agility'						=> $userdata['user_agility'] - $row['item_agility'],
			'user_perception'					=> $userdata['user_perception'] - $row['item_perception'],
			'user_intellect'					=> $userdata['user_intellect'] - $row['item_intellect'],
			'user_mf_armour_head'				=> $userdata['user_mf_armour_head'] - $row['item_mf_armour_head'],
			'user_mf_armour_body'				=> $userdata['user_mf_armour_body'] - $row['item_mf_armour_body'],
			'user_mf_armour_waist'				=> $userdata['user_mf_armour_waist'] - $row['item_mf_armour_waist'],
			'user_mf_armour_leg'				=> $userdata['user_mf_armour_leg'] - $row['item_mf_armour_leg'],
			'user_armour_head'					=> $userdata['user_armour_head'] - $row['item_armour_head'],
			'user_armour_body'					=> $userdata['user_armour_body'] - $row['item_armour_body'],
			'user_armour_waist'					=> $userdata['user_armour_waist'] - $row['item_armour_waist'],
			'user_armour_leg'					=> $userdata['user_armour_leg'] - $row['item_armour_leg'],
			'user_protect_damage'				=> $userdata['user_protect_damage'] - $row['item_protect_damage'],
			'user_protect_piercing'				=> $userdata['user_protect_piercing'] - $row['item_protect_piercing'],
			'user_protect_chopping'				=> $userdata['user_protect_chopping'] - $row['item_protect_chopping'],
			'user_protect_crushing'				=> $userdata['user_protect_crushing'] - $row['item_protect_crushing'],
			'user_protect_cutting'				=> $userdata['user_protect_cutting'] - $row['item_protect_cutting'],
			'user_protect_magic'				=> $userdata['user_protect_magic'] - $row['item_protect_magic'],
			'user_protect_air'					=> $userdata['user_protect_air'] - $row['item_protect_air'],
			'user_protect_earth'				=> $userdata['user_protect_earth'] - $row['item_protect_earth'],
			'user_protect_fire'					=> $userdata['user_protect_fire'] - $row['item_protect_fire'],
			'user_protect_water'				=> $userdata['user_protect_water'] - $row['item_protect_water'],
			'user_reduce_protect_magic'			=> $userdata['user_reduce_protect_magic'] - $row['item_reduce_protect_magic'],
			'user_reduce_protect_air'			=> $userdata['user_reduce_protect_air'] - $row['item_reduce_protect_air'],
			'user_reduce_protect_earth'			=> $userdata['user_reduce_protect_earth'] - $row['item_reduce_protect_earth'],
			'user_reduce_protect_fire'			=> $userdata['user_reduce_protect_fire'] - $row['item_reduce_protect_fire'],
			'user_reduce_protect_water'			=> $userdata['user_reduce_protect_water'] - $row['item_reduce_protect_water'],
			'user_mf_power_damage'				=> $userdata['user_mf_power_damage'] - $row['item_mf_power_damage'],
			'user_mf_power_piercing'			=> $userdata['user_mf_power_piercing'] - $row['item_mf_power_piercing'],
			'user_mf_power_chopping'			=> $userdata['user_mf_power_chopping'] - $row['item_mf_power_chopping'],
			'user_mf_power_crushing'			=> $userdata['user_mf_power_crushing'] - $row['item_mf_power_crushing'],
			'user_mf_power_cutting'				=> $userdata['user_mf_power_cutting'] - $row['item_mf_power_cutting'],
			'user_mf_power_magic'				=> $userdata['user_mf_power_magic'] - $row['item_mf_power_magic'],
			'user_mf_power_air'					=> $userdata['user_mf_power_air'] - $row['item_mf_power_air'],
			'user_mf_power_earth'				=> $userdata['user_mf_power_earth'] - $row['item_mf_power_earth'],
			'user_mf_power_fire'				=> $userdata['user_mf_power_fire'] - $row['item_mf_power_fire'],
			'user_mf_power_water'				=> $userdata['user_mf_power_water'] - $row['item_mf_power_water'],
			'user_mf_power_critical_hit'		=> $userdata['user_mf_power_critical_hit'] - $row['item_mf_power_critical_hit'],
			'user_mf_critical_hit'				=> $userdata['user_mf_critical_hit'] - $row['item_mf_critical_hit'],
			'user_mf_anticritical_hit'			=> $userdata['user_mf_anticritical_hit'] - $row['item_mf_anticritical_hit'],
			'user_mf_dodging'					=> $userdata['user_mf_dodging'] - $row['item_mf_dodging'],
			'user_mf_antidodging'				=> $userdata['user_mf_antidodging'] - $row['item_mf_antidodging'],
			'user_mf_counterblow'				=> $userdata['user_mf_counterblow'] - $row['item_mf_counterblow'],
			'user_mf_shield_block'				=> $userdata['user_mf_shield_block'] - $row['item_mf_shield_block'],
			'user_mf_parry'						=> $userdata['user_mf_parry'] - $row['item_mf_parry'],
			'user_mf_hit_through_armour'		=> $userdata['user_mf_hit_through_armour'] - $row['item_mf_hit_through_armour'],
			'user_knifes'						=> $userdata['user_knifes'] - $row['item_knifes'],
			'user_axes'							=> $userdata['user_axes'] - $row['item_axes'],
			'user_clubs'						=> $userdata['user_clubs'] - $row['item_clubs'],
			'user_swords'						=> $userdata['user_swords'] - $row['item_swords'],
			'user_staffs'						=> $userdata['user_staffs'] - $row['item_staffs'],
			'user_magic_air'					=> $userdata['user_magic_air'] - $row['item_magic_air'],
			'user_magic_earth'					=> $userdata['user_magic_earth'] - $row['item_magic_earth'],
			'user_magic_fire'					=> $userdata['user_magic_fire'] - $row['item_magic_fire'],
			'user_magic_water'					=> $userdata['user_magic_water'] - $row['item_magic_water'],
			'user_magic_light'					=> $userdata['user_magic_light'] - $row['item_magic_light'],
			'user_magic_grey'					=> $userdata['user_magic_grey'] - $row['item_magic_grey'],
			'user_magic_dark'					=> $userdata['user_magic_dark'] - $row['item_magic_dark'])) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу снять вещь...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Сбрасываем вещь в рюкзак
		//
		$sql = "UPDATE " . ITEMS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'item_is_equip'			=> 0,
			'item_slot'				=> ( $row['item_slot'] == 10 && $row['item_type'] != 'shield' ) ? 3 : $row['item_slot'],
			'item_sort_order'		=> get_max_row('item_sort_order', 'last_item', ITEMS_TABLE, '`item_user_id` = ' . $userdata['user_id']) + 1)) . " WHERE `item_id` = " . $row['item_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу снять вещь...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем параметры персонажа
		//
		if( $row['item_twohand'] == 1 )
		{
			$userdata['user_w10'] = 0;
		}

		$userdata['user_attacks'] -= $row['item_attacks'];
		$userdata['user_items_cost'] -= $row['item_price'];
		$userdata['user_max_hp'] -= $row['item_hp'];
		$userdata['user_hpspeed'] -= $row['item_hpspeed'];
		$userdata['user_hpspeed'] *= ( $userdata['user_bot'] && $config['fast_game'] ) ? 250 : ( ( $userdata['user_bot'] && !$config['fast_game'] ) ? 10 : 1);
		$userdata['user_hpspeed'] *= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : ( ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1);
		$userdata['user_max_mana'] -= $row['item_mana'];
		$userdata['user_manaspeed'] -= $row['item_manaspeed'];
		$userdata['user_w' . $row['item_slot']] = 0;
		$userdata['user_strength'] -= $row['item_strength'];
		$userdata['user_agility'] -= $row['item_agility'];
		$userdata['user_perception'] -= $row['item_perception'];
		$userdata['user_intellect'] -= $row['item_intellect'];
		$userdata['user_mf_armour_head'] -= $row['item_mf_armour_head'];
		$userdata['user_mf_armour_body'] -= $row['item_mf_armour_body'];
		$userdata['user_mf_armour_waist'] -= $row['item_mf_armour_waist'];
		$userdata['user_mf_armour_leg'] -= $row['item_mf_armour_leg'];
		$userdata['user_armour_head'] -= $row['item_armour_head'];
		$userdata['user_armour_body'] -= $row['item_armour_body'];
		$userdata['user_armour_waist'] -= $row['item_armour_waist'];
		$userdata['user_armour_leg'] -= $row['item_armour_leg'];
		$userdata['user_protect_damage'] -= $row['item_protect_damage'];
		$userdata['user_protect_piercing'] -= $row['item_protect_piercing'];
		$userdata['user_protect_chopping'] -= $row['item_protect_chopping'];
		$userdata['user_protect_crushing'] -= $row['item_protect_crushing'];
		$userdata['user_protect_cutting'] -= $row['item_protect_cutting'];
		$userdata['user_protect_magic'] -= $row['item_protect_magic'];
		$userdata['user_protect_air'] -= $row['item_protect_air'];
		$userdata['user_protect_earth'] -= $row['item_protect_earth'];
		$userdata['user_protect_fire'] -= $row['item_protect_fire'];
		$userdata['user_protect_water'] -= $row['item_protect_water'];
		$userdata['user_reduce_protect_magic'] -= $row['item_reduce_protect_magic'];
		$userdata['user_reduce_protect_air'] -= $row['item_reduce_protect_air'];
		$userdata['user_reduce_protect_earth'] -= $row['item_reduce_protect_earth'];
		$userdata['user_reduce_protect_fire'] -= $row['item_reduce_protect_fire'];
		$userdata['user_reduce_protect_water'] -= $row['item_reduce_protect_water'];
		$userdata['user_mf_power_damage'] -= $row['item_mf_power_damage'];
		$userdata['user_mf_power_piercing'] -= $row['item_mf_power_piercing'];
		$userdata['user_mf_power_chopping'] -= $row['item_mf_power_chopping'];
		$userdata['user_mf_power_crushing'] -= $row['item_mf_power_crushing'];
		$userdata['user_mf_power_cutting'] -= $row['item_mf_power_cutting'];
		$userdata['user_mf_power_magic'] -= $row['item_mf_power_magic'];
		$userdata['user_mf_power_air'] -= $row['item_mf_power_air'];
		$userdata['user_mf_power_earth'] -= $row['item_mf_power_earth'];
		$userdata['user_mf_power_fire'] -= $row['item_mf_power_fire'];
		$userdata['user_mf_power_water'] -= $row['item_mf_power_water'];
		$userdata['user_mf_power_critical_hit'] -= $row['item_mf_power_critical_hit'];
		$userdata['user_mf_critical_hit'] -= $row['item_mf_critical_hit'];
		$userdata['user_mf_anticritical_hit'] -= $row['item_mf_anticritical_hit'];
		$userdata['user_mf_dodging'] -= $row['item_mf_dodging'];
		$userdata['user_mf_antidodging'] -= $row['item_mf_antidodging'];
		$userdata['user_mf_counterblow'] -= $row['item_mf_counterblow'];
		$userdata['user_mf_shield_block'] -= $row['item_mf_shield_block'];
		$userdata['user_mf_parry'] -= $row['item_mf_parry'];
		$userdata['user_mf_hit_through_armour'] -= $row['item_mf_hit_through_armour'];
		$userdata['user_knifes'] -= $row['item_knifes'];
		$userdata['user_axes'] -= $row['item_axes'];
		$userdata['user_clubs'] -= $row['item_clubs'];
		$userdata['user_swords'] -= $row['item_swords'];
		$userdata['user_staffs'] -= $row['item_staffs'];
		$userdata['user_magic_air'] -= $row['item_magic_air'];
		$userdata['user_magic_earth'] -= $row['item_magic_earth'];
		$userdata['user_magic_fire'] -= $row['item_magic_fire'];
		$userdata['user_magic_water'] -= $row['item_magic_water'];
		$userdata['user_magic_light'] -= $row['item_magic_light'];
		$userdata['user_magic_grey'] -= $row['item_magic_grey'];
		$userdata['user_magic_dark'] -= $row['item_magic_dark'];

		$user->obtain_status($userdata);
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Функция округления денег
	//
	function int_money($user_money)
	{
		list($money1, $money2) = explode(".", $user_money);

		switch( $money2 )
		{
			case 10: $money2 = 1; break;
			case 20: $money2 = 2; break;
			case 30: $money2 = 3; break;
			case 40: $money2 = 4; break;
			case 50: $money2 = 5; break;
			case 60: $money2 = 6; break;
			case 70: $money2 = 7; break;
			case 80: $money2 = 8; break;
			case 90: $money2 = 9; break;
		}

		if( $money2 == '00' )
		{
			$money = $money1;
		}
		else
		{
			$money = '' . $money1 . '.' . substr($money2, 0, 2);
		}

		return $money;
	}
	//
	// ---------------

	// ---------------
	// Выводим ссылки на комнаты
	//
	function links_display($links)
	{
		global $root_path, $template, $user, $userdata;

		if( $links['path'][0] != '' )
		{
			for( $i = 0; $i < count($links['path']); $i++ )
			{
				$template->assign_block_vars('links', array(
					'DESC'		=> $links['desc'][$i],
					'NAME'		=> $user->get_room_name($links['path'][$i]),
		
					'U_PATH'	=> append_sid($root_path . 'main.php?path=' . $links['path'][$i]))
				);
			}
		}
	}
	//
	// ---------------

	// ---------------
	// Получаем данные вещей
	//
	function obtain_items(&$items, &$userdata, $page = '', $inventory_otdel = 1)
	{
		global $boxsort, $db, $room, $root_path, $shop_otdel, $template, $to_id, $user;

		//
		// Получаем данные всех вещей
		//
		switch( $page )
		{
			// Инвентарь
			case 'inventory':
				switch( $boxsort )
				{
					case 'name':	$order_by = '`item_name` ASC'; break;
					case 'cost':	$order_by = '`item_price` ASC'; break;
					case 'type':	$order_by = '`item_slot` ASC'; break;
					default:		$order_by = '`item_sort_order` DESC'; break;
				}

				$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND ( `item_inventory_otdel` = " . $inventory_otdel . " OR `item_is_equip` = 1 ) ORDER BY " . $order_by; break;

			// Ремонтная мастерская
			case 'repair':
				if( !$room )
				{
					// Получаем данные всех вещей, которые можно починить
					$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 0 AND `item_can_repair` = 1 AND `item_current_durability` > 0 ORDER BY `item_sort_order` DESC";
				}
				elseif( $room == 1 )
				{
					// Получаем данные оружия, на которое можно нанести гравировку
					$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 0 AND `item_slot` = 3 ORDER BY `item_sort_order` DESC";
				}
				elseif( $room == 2 )
				{
					// Получаем данные вещей, со встроенной магией
					$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 0 AND `item_inbuild_magic_num` > 0 AND `item_inbuild_magic_time` <> 'battle' ORDER BY `item_sort_order` DESC";
				}
				elseif( $room == 3 )
				{
					// Получаем данные артефактов, которые можно усилить
					$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 0 AND `item_artefact` = 1 AND `item_req_level` <> 10 ORDER BY `item_sort_order` DESC";
				}
				elseif( $room == 4 )
				{
					// Получаем данные бронек, которые можно подогнать
					$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 0 AND `item_fit` IS NULL AND `item_req_level` > 0 AND `item_type` LIKE 'harmor' OR 'larmor' ORDER BY `item_sort_order` DESC";
				}
				break;

			// Магазин
			case 'shop':		$sql = ( $userdata['user_access_level'] == ADMIN ) ? "SELECT * FROM " . SHOP_TABLE . " WHERE `item_otdel` = '" . $shop_otdel . "' ORDER BY `item_req_level` ASC, `item_price` ASC" : "SELECT * FROM " . SHOP_TABLE . " WHERE `item_otdel` = '" . $shop_otdel . "' AND `item_count` > 0 ORDER BY `item_req_level` ASC, `item_price` ASC"; break;

			// Магазин (продажа)
			case 'shop_sale':	$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_artefact` = 0 AND `item_is_equip` = 0 AND `item_price` > 0 AND `item_user_id` = " . $userdata['user_id'] . " ORDER BY `item_sort_order` DESC"; break;

			// Передачи
			case 'transfer':	$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " ORDER BY `item_sort_order` DESC"; break;

			case 'main':		$sql = "SELECT item_id, item_slot, item_type, item_name, item_img, item_current_durability, item_max_durability, item_req_strength, item_req_agility, item_req_perception, item_req_vitality, item_req_intellect, item_req_wisdom, item_req_spirituality, item_req_freedom, item_req_freedom_of_spirit, item_req_holiness, item_req_level, item_req_knifes, item_req_axes, item_req_clubs, item_req_swords, item_req_staffs, item_req_magic_air, item_req_magic_earth, item_req_magic_fire, item_req_magic_water, item_req_magic_light, item_req_magic_grey, item_req_magic_dark, item_hp, item_mf_armour_head, item_mf_armour_body, item_mf_armour_waist, item_mf_armour_leg, item_armour_head, item_armour_body, item_armour_waist, item_armour_leg, item_min_hit, item_max_hit, item_inbuild_magic, item_inbuild_magic_desc, item_inbuild_magic_num, item_inbuild_magic_time, item_etching FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 1"; break;

			default:			$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_user_id` = " . $userdata['user_id'] . " AND `item_is_equip` = 1"; break;
		}

		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещей...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Определяем переменные
		//
		$n = 0;
		$need_redirect = false;
		// ----------

		// Характеристики
		$chars = array(
			'item_application_time'			=> ' дн.',
			'item_magic_time'				=> ' мин.',
			'item_spell_percent'			=> '%',
			'item_use_delay'				=> ' мин.',
			'item_lifetime'					=> ' дн.',
			'item_symbols_num'				=> ''
		);

		// Наложенные заклятия
		$inbuild_spell = array(
			'air'							=> 'стихия воздуха',
			'antimirror'					=> 'эфирное воздействие',
			'attack'						=> 'внезапность',
			'box_lock'						=> 'страж',
			'build_in'						=> 'встраивание магии',
			'chains'						=> 'частичная парализация',
			'chesnok'						=> 'против вампиров',
			'cure'							=> 'лечение',
			'cureHP'						=> 'исцеление',
			'curse'							=> 'проклятье',
			'dark'							=> 'магия тьмы',
			'earth'							=> 'стихия земли',
			'elemental'						=> 'астрал стихий',
			'fight_magic'					=> 'магия стихий',
			'fire'							=> 'стихия огня',
			'illusion'						=> 'иллюзия',
			'light'							=> 'Магия света',
			'mirror'						=> 'порождение клона',
			'powerup'						=> 'усиление',
			'scanner'						=> 'всевидящее око',
			'silence'						=> 'молчание',
			'submission'					=> 'подчинение',
			'teleport'						=> 'телепортация',
			'undef'							=> 'идентификация',
			'vampire'						=> 'вампиризм',
			'water'							=> 'стихия воды'
		);

		// Названия комплектов
		$set_names = array(
			'bloodmoon'						=> 'кровавой луны',
			'caress'						=> 'ласки',
			'darksteel'						=> 'темной стали',
			'dusk_storm'					=> 'сумеречных гроз',
			'forget_times'					=> 'забытых времен',
			'glare'							=> 'бликов',
			'granite_power'					=> 'гранитной власти',
			'fear'							=> 'ужаса',
			'fire'							=> 'огня',
			'fire_dawn'						=> 'огненной зари',
			'gold'							=> 'золота',
			'millstone'						=> 'жернова',
			'morning_sun'					=> 'утреннего солнца',
			'morning_waterfall'				=> 'утреннего водопада',
			'oblivion'						=> 'забвения',
			'obstinacy'						=> 'упорства',
			'siege'							=> 'осадный',
			'spider'						=> 'паука',
			'sunset'						=> 'заката',
			'timber'						=> 'лесной',
			'wanderer'						=> 'странника'
		);

		$chars_name = array(
			// Характеристики
			'item_application_time'			=> 'Срок годности',
			'item_spell_percent'			=> 'Вероятность срабатывания',
			'item_magic_time'				=> 'Продолжительность действия магии',
			'item_use_delay'				=> 'Задержка использования',
			'item_lifetime'					=> 'Срок жизни',
			'item_symbols_num'				=> 'Количество символов',

			// Требования
			'requirements'					=> array(
				'item_req_gender'				=> 'Пол',
				'item_req_strength'				=> 'Сила',
				'item_req_agility'				=> 'Ловкость',
				'item_req_perception'			=> 'Иинтуиция',
				'item_req_vitality'				=> 'Выносливость',
				'item_req_intellect'			=> 'Интеллект',
				'item_req_wisdom'				=> 'Мудрость',
				'item_req_spirituality'			=> 'Духовность',
				'item_req_freedom'				=> 'Воля',
				'item_req_freedom_of_spirit'	=> 'Свобода духа',
				'item_req_holiness'				=> 'Божественность',
				'item_req_level'				=> 'Уровень',
				'item_req_knifes'				=> 'Мастерство владения кастетами, ножами',
				'item_req_axes'					=> 'Мастерство владения топорами, секирами',
				'item_req_clubs'				=> 'Мастерство владения дубинами, булавами',
				'item_req_swords'				=> 'Мастерство владения мечами',
				'item_req_staffs'				=> 'Мастерство владения магическими посохами',
				'item_req_magic_air'			=> 'Мастерство владения стихией Воздуха',
				'item_req_magic_earth'			=> 'Мастерство владения стихией Земли',
				'item_req_magic_fire'			=> 'Мастерство владения стихией Огня',
				'item_req_magic_water'			=> 'Мастерство владения стихией Воды',
				'item_req_magic_light'			=> 'Мастерство владения магией Света',
				'item_req_magic_grey'			=> 'Мастерство владения серой магией',
				'item_req_magic_dark'			=> 'Мастерство владения магий Тьмы',
				'item_req_current_mana'			=> 'Мана',
			),

			// Действующие свойства
			'affects'						=> array(
				'item_strength'					=> 'Сила',
				'item_agility'					=> 'Ловкость',
				'item_perception'				=> 'Интуиция',
				'item_intellect'				=> 'Интеллект',
				'item_hpspeed'					=> 'Восстановление HP (%)',
				'item_manaspeed'				=> 'Восстановление маны (%)',
				'item_hp'						=> 'Уровень жизни (HP)',
				'item_mana'						=> 'Уровень маны',
				'item_decrease_usage_mana'		=> 'Уменьшение расхода маны (%)',
				'item_plus_weight'				=> 'Увеличение рюкзака',
				'item_armour_head'				=> 'Броня головы',
				'item_armour_body'				=> 'Броня корпуса',
				'item_armour_waist'				=> 'Броня пояса',
				'item_armour_leg'				=> 'Броня ног',
				'item_protect_damage'			=> 'Защита от урона',
				'item_protect_piercing'			=> 'Защита от колющего урона',
				'item_protect_chopping'			=> 'Защита от рубящего урона',
				'item_protect_crushing'			=> 'Защита от дробящего урона',
				'item_protect_cutting'			=> 'Защита от режущего урона',
				'item_protect_magic'			=> 'Защита от магии',
				'item_protect_air'				=> 'Защита от магии воздуха',
				'item_protect_earth'			=> 'Защита от магии земли',
				'item_protect_fire'				=> 'Защита от магии огня',
				'item_protect_water'			=> 'Защита от магии воды',
				'item_reduce_protect_magic'		=> 'Подавление защиты от магии',
				'item_reduce_protect_air'		=> 'Подавление защиты от магии воздуха',
				'item_reduce_protect_earth'		=> 'Подавление защиты от магии земли',
				'item_reduce_protect_fire'		=> 'Подавление защиты от магии огня',
				'item_reduce_protect_water'		=> 'Подавление защиты от магии воды',
				'item_mf_anticritical_hit'		=> 'Мф. против критического удара (%)',
				'item_mf_dodging'				=> 'Мф. увертывания (%)',
				'item_mf_counterblow'			=> 'Мф. контрудара (%)',
				'item_mf_shield_block'			=> 'Мф. блока щитом (%)',
				'item_mf_parry'					=> 'Мф. парирования (%)'
			),

			// Свойства предмета
			'properties'					=> array(
				'item_mf_power_damage'			=> 'Мф. мощности урона (%)',
				'item_mf_power_piercing'		=> 'Мф. мощности колющего урона (%)',
				'item_mf_power_chopping'		=> 'Мф. мощности рубящего урона (%)',
				'item_mf_power_crushing'		=> 'Мф. мощности дробящего урона (%)',
				'item_mf_power_cutting'			=> 'Мф. мощности режущего урона (%)',
				'item_mf_power_magic'			=> 'Мф. мощности магии стихий (%)',
				'item_mf_power_air'				=> 'Мф. мощности магии воздуха (%)',
				'item_mf_power_earth'			=> 'Мф. мощности магии земли (%)',
				'item_mf_power_fire'			=> 'Мф. мощности магии огня (%)',
				'item_mf_power_water'			=> 'Мф. мощности магии воды (%)',
				'item_mf_power_critical_hit'	=> 'Мф. мощности крит. удара (%)',
				'item_mf_critical_hit'			=> 'Мф. критического удара (%)',
				'item_mf_antidodging'			=> 'Мф. против увертывания (%)',
				'item_mf_hit_through_armour'	=> 'Мф. удара сквозь броню (%)',
				'item_knifes'					=> 'Мастерство владения кастетами, ножами',
				'item_axes'						=> 'Мастерство владения топорами, секирами',
				'item_clubs'					=> 'Мастерство владения дубинами, булавами',
				'item_swords'					=> 'Мастерство владения мечами',
				'item_staffs'					=> 'Мастерство владения магическими посохами',
				'item_magic_air'				=> 'Мастерство владения стихией Воздуха',
				'item_magic_earth'				=> 'Мастерство владения стихией Земли',
				'item_magic_fire'				=> 'Мастерство владения стихией Огня',
				'item_magic_water'				=> 'Мастерство владения стихией Воды',
				'item_magic_light'				=> 'Мастерство владения магией Света',
				'item_magic_grey'				=> 'Мастерство владения серой магией',
				'item_magic_dark'				=> 'Мастерство владения магией Тьмы'
			),

			// Особенности
			'specials'						=> array(
				'item_ice_attacks'				=> 'Ледяные атаки',
				'item_fire_attacks'				=> 'Огненные атаки',
				'item_electric_attacks'			=> 'Электрические атаки',
				'item_light_attacks'			=> 'Атаки светом',
				'item_dark_attacks'				=> 'Атаки тьмой',
				'item_piercing_attacks'			=> 'Колющие атаки',
				'item_chopping_attacks'			=> 'Рубящие атаки',
				'item_crushing_attacks'			=> 'Дробящие атаки',
				'item_cutting_attacks'			=> 'Режущие атаки',
				'item_piercing_armour'			=> 'Защита от колющего урона',
				'item_chopping_armour'			=> 'Защита от рубящего урона',
				'item_crushing_armour'			=> 'Защита от дробящего урона',
				'item_cutting_armour'			=> 'Защита от режущего урона'
			)
		);

		while( $row = $db->sql_fetchrow($result) )
		{
			if( ( $page == 'inventory' && $row['item_is_equip'] ) || $page == 'main' || $page == 'overall' )
			{
				if( $page != 'battle' )
				{
					if( $row['item_req_strength'] > $userdata['user_strength'] || $row['item_req_agility'] > $userdata['user_agility'] || $row['item_req_perception'] > $userdata['user_perception'] || $row['item_req_vitality'] > $userdata['user_vitality'] || $row['item_req_intellect'] > $userdata['user_intellect'] || $row['item_req_wisdom'] > $userdata['user_wisdom'] || $row['item_req_spirituality'] > $userdata['user_spirituality'] || $row['item_req_freedom'] > $userdata['user_freedom'] || $row['item_req_freedom_of_spirit'] > $userdata['user_freedom_of_spirit'] || $row['item_req_holiness'] > $userdata['user_holiness'] || $row['item_req_level'] > $userdata['user_level'] || $row['item_req_knifes'] > $userdata['user_knifes'] || $row['item_req_axes'] > $userdata['user_axes'] || $row['item_req_clubs'] > $userdata['user_clubs'] || $row['item_req_swords'] > $userdata['user_swords'] || $row['item_req_staffs'] > $userdata['user_staffs'] || $row['item_req_magic_air'] > $userdata['user_magic_air'] || $row['item_req_magic_earth'] > $userdata['user_magic_earth'] || $row['item_req_magic_fire'] > $userdata['user_magic_fire'] || $row['item_req_magic_water'] > $userdata['user_magic_water'] || $row['item_req_magic_light'] > $userdata['user_magic_light'] || $row['item_req_magic_grey'] > $userdata['user_magic_grey'] || $row['item_req_magic_dark'] > $userdata['user_magic_dark'] )
					{
						// Снимаем вещь
						$user->item_setdown($row['item_slot'], $userdata);
						$need_redirect = true;
					}
				}

				// Броня
				$items['item_armour_head'][$row['item_slot']] = $row['item_armour_head'];
				$items['item_armour_body'][$row['item_slot']] = $row['item_armour_body'];
				$items['item_armour_waist'][$row['item_slot']] = $row['item_armour_waist'];
				$items['item_armour_leg'][$row['item_slot']] = $row['item_armour_leg'];
				$items['item_mf_armour_head'][$row['item_slot']] = $row['item_mf_armour_head'];
				$items['item_mf_armour_body'][$row['item_slot']] = $row['item_mf_armour_body'];
				$items['item_mf_armour_waist'][$row['item_slot']] = $row['item_mf_armour_waist'];
				$items['item_mf_armour_leg'][$row['item_slot']] = $row['item_mf_armour_leg'];
				// ----------

				// Встройки
				$items['item_inbuild_magic'][$row['item_slot']] = $row['item_inbuild_magic'];
				$items['item_inbuild_magic_desc'][$row['item_slot']] = $row['item_inbuild_magic_desc'];
				$items['item_inbuild_magic_num'][$row['item_slot']] = $row['item_inbuild_magic_num'];
				$items['item_inbuild_magic_time'][$row['item_slot']] = $row['item_inbuild_magic_time'];
				// ----------

				// Остальное
				$items['item_current_durability'][$row['item_slot']] = $row['item_current_durability'];
				$items['item_etching'][$row['item_slot']] = $row['item_etching'];
				$items['item_hp'][$row['item_slot']] = $row['item_hp'];
				$items['item_id'][$row['item_slot']] = $row['item_id'];
				$items['item_img'][$row['item_slot']] = $row['item_img'];
				$items['item_max_durability'][$row['item_slot']] = $row['item_max_durability'];
				$items['item_max_hit'][$row['item_slot']] = $row['item_max_hit'];
				$items['item_min_hit'][$row['item_slot']] = $row['item_min_hit'];
				$items['item_name'][$row['item_slot']] = $row['item_name'];
				$items['item_type'][$row['item_slot']] = $row['item_type'];
				// ----------
			}
			elseif( !$page || $page == 'inventory' || $page == 'repair' || $page == 'shop' || $page == 'shop_sale' || $page == 'transfer' )
			{
				//
				// Порча вещей
				//
				if( $page == 'inventory' || $page == 'transfer' )
				{
					if( $row['item_application_time'] > 0 && ( time() > ( $row['item_start_lifetime'] + ( $row['item_application_time'] * 86400 ) ) ) )
					{
						if( $row['item_img'] == 'elixir_bad' || $row['item_img'] == 'just_junk' )
						{
							// Уничтожаем предмет
							$user->item_drop($row['item_id'], $userdata);

							// Запись в личное дело
							$user->add_admin_log_message($userdata['user_id'], '1.9', 'item_bad', 'У "' . $userdata['user_login'] . '" предмет "' . $row['item_name'] . '" рассыпался в пыль');
						}
						else
						{
							// Портим предмет
							$user->item_bad($row);
						}

						$need_redirect = true;
					}
				}
				// ----------

				if( ( $page == 'repair' || $page == 'shop' || $page == 'shop_sale' ) || ( $page == 'inventory' || $page == 'transfer' ) )
				{
					//
					// Определяем переменные
					//
					$item_align[$n]		= $row['item_align'];
					$item_artefact[$n]	= $row['item_artefact'];
					$item_id[$n]		= $row['item_id'];
					$item_img[$n]		= $row['item_img'];
					$item_name[$n]		= $row['item_name'];
					$item_weight[$n]	= $row['item_weight'];

					if( $page == 'inventory' || $page == 'repair' || $page == 'transfer' )
					{
						$item_fit_login[$n] = ( $row['item_fit'] ) ? $row['item_fit_login'] : '';
						$item_durability[$n] = ( $row['item_current_durability'] >= ( $row['item_max_durability'] - 2 ) && $row['item_current_durability'] > 0 ) ? '<font color="#880000">' . $row['item_current_durability'] . '/' . $row['item_max_durability'] . '</font>' : $row['item_current_durability'] . '/' . $row['item_max_durability'];
						$item_gift[$n] = ( $row['item_gift_from'] ) ? '<img src="http://static.ivacuum.ru/i/podarok.gif" width="18" height="16" alt="Этот предмет вам подарил ' . $row['item_gift_from'] . '. Вы не сможете передать этот предмет кому-либо еще.">' : '';
						$item_price[$n] = $row['item_price'];
					}
					elseif( $page == 'shop' || $page == 'shop_sale' )
					{
						$item_fit_login[$n] = '';
						$row['item_price_real'] = $row['item_price'];
						$row['item_price'] = ( $row['item_ekrprice'] > 0 && $row['item_artefact'] ) ? ( $row['item_ekrprice'] * 2 ) : ( ( $row['item_ekrprice'] > 0 && !$row['item_artefact'] ) ? ( $row['item_ekrprice'] * 10 ) : $row['item_price'] );
						$row['item_price'] = ( $row['item_price'] == intval($row['item_price']) ) ? intval($row['item_price']) : $row['item_price'];
						$item_price[$n] = ( $page == 'shop_sale' || $userdata['user_money'] >= $row['item_price'] ) ? $row['item_price'] : '<font color="red">' . $row['item_price'] . '</font>';

						if( $page == 'shop' )
						{
							if( !$row['item_count'] )
							{
								$item_count[$n] = '<font style="color: red; font-size: 11px; font-weight: bold">' . $row['item_count'] . '</font>';
							}
							else
							{
								$item_count[$n] = $row['item_count'];
							}

							$item_count_hidden[$n] = $row['item_count_hidden'];
						}
						else
						{
							$item_sale_price[$n] = ( $row['item_price_real'] / 2 ) - ( $row['item_current_durability'] * 0.12 );
							$item_sale[$n] = '<a href="shop.php?sl=' . $row['item_id'] . '">продать за ' . sprintf("%.2f кр.", $item_sale_price[$n]) . '</a>';
						}

						$item_durability[$n] = ( $page == 'shop_sale' && $row['item_current_durability'] >= ( $row['item_max_durability'] - 2 ) && $row['item_current_durability'] > 0 ) ? '<font color="#880000">' . $row['item_current_durability'] . '/' . $row['item_max_durability'] . '</font>' : ( ( $page == 'shop_sale' ) ? $row['item_current_durability'] . '/' . $row['item_max_durability'] : '0/' . $row['item_max_durability']);
					}
					// ----------

					//
					// Характеристики предмета
					//
					$chars['item_application_time'] .= ( $row['item_application_time'] && ( $page == 'inventory' || $page == 'transfer' ) ) ? ' (до ' . date('d.m.y', ( $row['item_start_lifetime'] + ( $row['item_application_time'] * 86400 ) ) ) . ')' : '';
					$chars['item_lifetime'] .= ( $row['item_lifetime'] && ( $page == 'inventory' || $page == 'transfer' ) ) ? ' (до ' . date('d.m.y', ( $row['item_start_lifetime'] + ( $row['item_lifetime'] * 86400 ) ) ) . ')' : '';
					$item_chars[$n] = '';

					foreach( $chars as $key => $value )
					{
						$item_chars[$n] .= ( $row[$key] ) ? $chars_name[$key] . ': ' . $row[$key] . $value . '<br />' : '';
					}

					$chars['item_application_time'] = ( $row['item_application_time'] && ( $page == 'inventory' || $page == 'transfer' ) ) ? ' дн.' : ' дн.';
					$chars['item_lifetime'] = ( $row['item_lifetime'] && ( $page == 'inventory' || $page == 'transfer' ) ) ? ' дн.' : ' дн.';
					// ----------

					//
					// Требования предмета
					//
					if( $page == 'inventory' )
					{
						$cannot_set[$n] = false;
						$cannot_use[$n] = false;

						if( !$row['item_slot'] )
						{
							// Не может одеть, если некуда
							$cannot_set[$n] = true;
						}
					}

					$item_requirements[$n] = '';

					foreach( $chars_name['requirements'] as $key => $value )
					{
						if( $row[$key] && $userdata['user_' . substr($key, 9)] >= $row[$key] )
						{
							$item_requirements[$n] .= (	$row[$key] ) ? '&bull; ' . $value . ': ' . $row[$key] . '<br />' : '';
						}
						elseif( $row[$key] && $userdata['user_' . substr($key, 9)] < $row[$key] )
						{
							$item_requirements[$n] .= ( $row[$key] ) ? '&bull; <font color="red">' . $value . ': ' . $row[$key] . '</font><br />' : '';

							if( $page == 'inventory' )
							{
								$cannot_set[$n] = true;
								$cannot_use[$n] = true;
							}
						}
					}

					$item_requirements[$n] .= ( $row['item_req_inbuild_spell'] ) ? '<font color="#8f0000">Наложены заклятия:</font> ' . $inbuild_spell[$row['item_req_inbuild_spell']] . '<br />' : '';
					$item_requirements[$n] = ( $item_requirements[$n] ) ? '<b>Требуется минимальное:</b><br />' . $item_requirements[$n] : '';
					// ----------

					//
					// Действующие эффекты
					//
					$item_affects[$n] = '';

					foreach( $chars_name['affects'] as $key => $value )
					{
						if( $row[$key] && ( $key == 'item_armour_head' || $key == 'item_armour_body' || $key == 'item_armour_waist' || $key == 'item_armour_leg' ) )
						{
							$item_affects[$n] .= ( $row['item_mf_' . substr($key, 5)] ) ? '&bull; ' . $value . ': ' . ( 1 + $row['item_mf_' . substr($key, 5)] ) . '-' . ( $row['item_mf_' . substr($key, 5)] + $row[$key] ) . ' (' . round($row['item_mf_' . substr($key, 5)]) . '+d' . round($row[$key]) . ')<br />' : '&bull; ' . $value . ': 1-' . round($row[$key]) . ' (d' . round($row[$key]) . ')<br />';
						}
						elseif( $row[$key] )
						{
							$item_affects[$n] .= '&bull; ' . $value . ': ' . $row[$key] . '<br />';
						}
					}

					$item_affects[$n] = ( $item_affects[$n] ) ? '<b>Действует на:</b><br />' . $item_affects[$n] : '';
					// ----------

					//
					// Свойства предмета
					//
					$item_properties[$n] = ( $row['item_min_hit'] || $row['item_max_hit'] ) ? '&bull; Урон: ' . round($row['item_min_hit']) . ' - ' . round($row['item_max_hit']) . '<br />' : '';

					foreach( $chars_name['properties'] as $key => $value )
					{
						$item_properties[$n] .= ( $row[$key] ) ? '&bull; ' . $value . ': ' . $row[$key] . '<br />' : '';
					}

					$item_properties[$n] .= ( $row['item_secondhand'] ) ? '&bull; Второе оружие<br />' : '';
					$item_properties[$n] .= ( $row['item_twohand'] ) ? '&bull; Двуручное оружие<br />' : '';
					$item_properties[$n] = ( $item_properties[$n] ) ? '<b>Свойства предмета:</b><br />' . $item_properties[$n] : '';
					// ----------

					//
					// Особенности предмета
					//
					$item_specials[$n] = '';

					foreach( $chars_name['specials'] as $key => $value )
					{
						$item_specials[$n] .= ( $row[$key] ) ? '&bull; ' . $value . ': ' . $row[$key] . '<br />' : '';
					}

					$item_specials[$n] = ( $item_specials[$n] ) ? '<b>Особенности:</b><br />' . $item_specials[$n] : '';
//					$item_specials[$n] .= ( $row['item_set'] && $row['item_set_num'] ) ? '&bull; Часть комплекта: <b>Комлект ' . $set_names[$row['item_set']] . '</a> [0/' . $row['item_set_num'] . ']</b><br />' : '';
					$item_specials[$n] .= ( $row['item_set'] && $row['item_set_num'] ) ? '&bull; Часть комплекта: <b>Комлект ' . $row['item_set'] . '</a> [0/' . $row['item_set_num'] . ']</b><br />' : '';
					// ----------

					//
					// Встройки
					//
					$item_inbuild_magic[$n] = ( $row['item_inbuild_magic'] && $row['item_inbuild_magic_desc'] && $row['item_inbuild_magic_num'] && $row['item_inbuild_magic_time'] ) ? 'Встроено заклятие <img src="http://static.ivacuum.ru/i/items/' . $row['item_inbuild_magic'] . '.gif" width="40" height="25" alt="' . $row['item_inbuild_magic_desc'] . '"></a> ' . $row['item_inbuild_magic_num'] . ' шт. ' . ( ( $row['item_inbuild_magic_time'] == 'day' ) ? 'в сутки' : ( $row['item_inbuild_magic_time'] == 'battle' ? 'на бой' : '' ) ) . '<br>' : '';

					$item_ismagic[$n] = ( $item_inbuild_magic[$n] ) ? ' class="ismagic"' : '';

					$item_comment[$n] = ( ( $page == 'inventory' || $page == 'repair' || $page == 'transfer' ) && $row['item_etching'] && $row['item_etching_town'] ) ? '<img src="http://static.ivacuum.ru/i/g' . $row['item_etching_town'] . '.gif"> На ручке выгравирована надпись:<br /><center><font color="#999999">' . $row['item_etching'] . '</font></center>' : '';
					$item_comment[$n] .= ( $row['item_comment'] ) ? '<small>Описание:<br />' . $row['item_comment'] . '</small><br />' : '';
					$item_comment[$n] .= ( ( ( $page == 'inventory' || $page == 'repair' || $page == 'transfer' ) && $row['item_type'] == 'shirt' ) || ( $page == 'shop' && $shop_otdel == 'shirts' ) || ( $page == 'shop_sale' && $row['item_type'] == 'shirt' ) ) ? '<small>Одевается под броню</small><br />' : '';
					$item_comment[$n] .= ( $page == 'inventory' || $page == 'repair' || $page == 'shop_sale' || $page == 'transfer' ) ? '<small>Сделано в ' . $user->city_name($row['item_town'], 'text') . '</small><br />' : '';
					$item_comment[$n] .= ( !$row['item_can_repair'] ) ? '<font color="#8f0000"><small>Предмет не подлежит ремонту</small></font><br />' : '';
					// ----------

					//
					// Возможность одеть и использовать вещь
					//
					if( $page == 'inventory' && ( !$row['item_slot'] || !$row['item_slot'] ) )
					{
						$cannot_set == true;
					}

					$item_action[$n] = '';

					if( $page == 'inventory' )
					{
						if( $cannot_use[$n] == false && ( ( $row['item_type'] == 'potion' && $row['item_img'] != 'elixir_bad' && $row['item_img'] != 'elixir_empty' ) || $item_inbuild_magic[$n] ) )
						{
							switch( $row['item_img'] )
							{
								default:			$item_action[$n] .= '&nbsp;<a href="JavaScript:UseMagick(\'' . $row['item_name'] . '\',\'main.php\', \'' . $row['item_img'] . '\', \'\', ' . $row['item_id'] . ', \'\', \'\')">исп-ть</a><br />'; break;
							}
						}

						$item_action[$n] .= ( !$cannot_set[$n] ) ? '&nbsp;<a href="main.php?set=' . $row['item_id'] . '">надеть</a>' : '';
						$item_action[$n] .= '&nbsp;<a href="javascript:drop(\'' . $row['item_img'] . '\', \'' . $row['item_id'] . '\', \'' . $row['item_name'] . '\')"><img src="http://static.ivacuum.ru/i/clear.gif" width="13" height="13" alt="Выбросить предмет"></a>';
					}
					elseif( $page == 'repair' )
					{
						if( !$room )
						{
							$item_action[$n] = '<a href="repair.php?rp=' . $row['item_id'] . '">Ремонт 1 ед. за 0.1 кр.</a><br>';
							$item_action[$n] .= ( $row['item_current_durability'] >= 10 ) ? '<a href="repair.php?rp=' . $row['item_id'] . '&medium=1">Ремонт 10 ед. за 1 кр.</a><br>' : '';
							$item_action[$n] .= '<a href="repair.php?rp=' . $row['item_id'] . '&full=1">Полный ремонт за ' . ( 0.1 * $row['item_current_durability'] ) . ' кр.</a>';
						}
						elseif( $room == 4 )
						{
							$item_action[$n] = '<a href="repair.php?fit=' . $row['item_id'] . '">Подогнать (+' . ( 6 + ( 6 * $row['item_req_level'] ) ) . 'HP) за ' . ( 10 + ( 5 * $row['item_req_level'] ) ) . 'кр.</a>';
						}
					}
					elseif( $page == 'transfer' )
					{
						$item_action[$n] .= ( $row['item_gift_from'] == '' ) ? '<a href="main.php?to_id=' . $to_id . '&setobject=' . $row['item_id'] . '" onclick="return confirm(\'Передать предмет ' . $row['item_name'] . '?\')">передать&nbsp;за&nbsp;1&nbsp;кр.</a><br><a href="main.php?to_id=' . $to_id . '&setobject=' . $row['item_id'] . '&podarok=1" onclick="return confirm(\'Подарить предмет ' . $row['item_name'] . '? Т.к. вы дарите, то этот предмет больше нельзя будет кому-либо передать!!!\')">подарить</a><br><a href="javascript:Sale(\'' . $to_id . '\', \'' . $row['item_id'] . '\', \'' . $row['item_name'] . '\', \'1\')">продать</a>' : 'подарок нельзя передавать';
					}
					// ----------

					$n++;
				}
			}
		}
		// ----------

		if( $need_redirect )
		{
			// Редирект (если нужно)
			redirect('main.php?edit=');
		}

		if( $page == 'inventory' || $page == 'transfer' )
		{
			$template->assign_block_vars('inventory_header', array(
				'EDIT_' . $inventory_otdel	=> 'bgcolor="A5A5A5" ')
			);
		}

		//
		// Если вещей в рюкзаке нет, то...
		//
		if( !$n )
		{
			$template->assign_block_vars('no_items', array());
		}
		else
		{
			for( $i = 0; $i < $n; $i++ )
			{
				$template->assign_block_vars( ( ( $page == 'shop_sale' ) ? 'sale_items' : 'items' ), array(
					'ACTION'			=> $item_action[$i],
					'ADDCOUNT'			=> ( $page == 'shop' && $userdata['user_access_level'] == ADMIN ) ? '<br /><a href="javascript:" onclick="item_add(\'' . $item_img[$i] . '\', \'' . $item_name[$i] . '\', \'' . sprintf("%.2f", $item_price[$i] * 0.85) . '\')">добавить</a>' : '',
					'AFFECTS'			=> $item_affects[$i],
					'ALIGN'				=> $item_align[$i],
					'ARTEFACT'			=> ( $item_artefact[$i] ) ? '<img alt="Артефакт" height="16" src="http://static.ivacuum.ru/i/artefact.gif" width="18">' : '',
					'CHAR'				=> $item_chars[$i],
					'COUNT'				=> ( $page == 'shop' && $item_count_hidden[$i] == true && $userdata['user_access_level'] != ADMIN ) ? '' : ( ( $page == 'shop' ) ? '&nbsp;<small>(количество: ' . $item_count[$i] . ')</small>' : ''),
					'COMMENT'			=> $item_comment[$i],
					'DURABILITY'		=> $item_durability[$i],
					'FEATURES'			=> $item_specials[$i],
					'FIT'				=> ( $item_fit_login[$i] && ( $page == 'inventory' || $page == 'repair' || $page == 'transfer' ) ) ? '&nbsp;<img src="http://static.ivacuum.ru/i/destiny.gif" width="16" height="18" alt="Этот предмет связан общей судьбой с ' . $item_fit_login[$i] . '. Никто другой не сможет его использовать.">' : '',
					'GIFT'				=> ( $page == 'inventory' || $page == 'repair' || $page == 'transfer' ) ? $item_gift[$i] : '',
					'IMG'				=> $item_img[$i],
					'INBUILD_MAGIC'		=> $item_inbuild_magic[$i],
					'ISMAGIC'			=> $item_ismagic[$i],
					'NAME'				=> $item_name[$i],
					'PRICE'				=> ( $page == 'shop' || $page == 'shop_sale' ) ? $item_price[$i] : $user->int_money($item_price[$i]),
					'PROPERTIES'		=> $item_properties[$i],
					'REQUIRES'			=> $item_requirements[$i],
					'ROW_CLASS'			=> ( !($i % 2) ) ? '#c7c7c7' : '#d5d5d5',
					'SALE'				=> ( $page == 'shop_sale' ) ? $item_sale[$i] : '',
					'WEIGHT'			=> $item_weight[$i])
				);
			}
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Получаем комплекты
	//
	function obtain_kmps(&$kmps, $userdata)
	{
		global $db;

		$sql = "SELECT * FROM " . KMP_TABLE . " WHERE `kmp_user_id` = " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные комплектов...', '', __LINE__, __FILE__, $sql);
		}

		while( $row = $db->sql_fetchrow($result) )
		{
			$kmps .= '<img src="http://static.ivacuum.ru/i/clear.gif" width="13" height="13" alt="Удалить комплект" onclick="if (confirm(\'Удалить комплект ' . $row['kmp_name'] . '?\')) {location=\'main.php?delk=' . $row['kmp_id'] . '\'}" style="cursor: hand"> <a href="main.php?skmp=' . $row['kmp_id'] . '">Надеть "' . $row['kmp_name'] . '"</a><br />';
		}
	}
	//
	// ---------------

	// ---------------
	// Определяем состояние персонажа (укуренность, опъянение и т.д.)
	//
	function obtain_status(&$userdata, $array = '', $page = '')
	{
		global $db, $template, $user;

		//
		// Получаем данные
		//
		$sql = "SELECT * FROM " . STATUS_TABLE . " WHERE `status_user_id` = " . $userdata['user_id'] . " AND ( `status_time` > " . time() . " OR `status_time` = '-1' )";
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу определить состояние персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		$n = 0;

		while( $row = $db->sql_fetchrow($result) )
		{
			if( $page == 'set_items' || $page == 'set_skills' )
			{
				// При одевании/снятии вещей вычитаем модификаторы...
				$userdata['user_' . $row['status_name']] -= $row['status_mf'];
			}
			else
			{
				// ...в остальных случаях прибавляем
				$userdata['user_' . $row['status_name']] += $row['status_mf'];
			}

			if( $page == 'inf' && ( $row['status_name'] == 'strength' || $row['status_name'] == 'agility' || $row['status_name'] == 'perception' || $row['status_name'] == 'vitality' || $row['status_name'] == 'intellect' || $row['status_name'] == 'wisdom' ) )
			{
				// Обновляем массив на странице инфы
				$array[$row['status_name']] += $row['status_mf'];
			}
			elseif( $page == 'skills' )
			{
				if( $n == 0 )
				{
					// На странице умений выводим таблицу усилений
					$template->assign_block_vars('status_table', array());
				}

				$template->assign_block_vars('status', array(
					'COMMENT'		=> ( $row['status_comment'] ) ? '<small>(' . $row['status_comment'] . ')</small>' : '',
					'MF'					=> ( $row['status_mf'] > 0 && $row['status_mf_hidden'] ) ? '<font color="green">+??</font>' : ( ( $row['status_mf'] > 0 ) ? '<font color="green">+' . $row['status_mf'] . '</font>' : ( ( $row['status_mf'] < 0 && $row['status_mf_hidden'] ) ? '<font color="red">-??</font>' : ( ( $row['status_mf'] < 0 ) ? '<font color="red">' . $row['status_mf'] . '</font>' : '') ) ),
					'NAME'				=> $user->characteristic_name($row['status_name']),
					'ROW_CLASS'	=> ( !($n % 2) ) ? '#d5d5d5' : '#c7c7c7',
					'TIME'				=> $user->create_time($row['status_time'] - time()))
				);

				$n++;
			}
		}

		return $array;
	}
	//
	// ---------------

	// ---------------
	// Переход по комнатам
	//
	function path($path)
	{
		global $items, $user, $userdata;

		// Время перехода
		$going_time = $user->get_going_time();

		//
		// Проверки
		//
		if( $going_time > 0 )
		{
			$message = 'Вы не можете так быстро перемещаться по комнатам';
		}
		elseif( $items['weight'] > ( ( $userdata['user_strength'] * 4 ) + $items['plus_weight'] ) )
		{
			$message = 'Вы перегружены и не можете перемещаться';
		}
		else
		{
			$message = '';
		}
		// ----------

		if( !$message )
		{
			switch( $userdata['user_room'] )
			{
				case '1.100':
					// ---------------
					// Центральная площадь
					//
					switch( $path )
					{
						case 'o0':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 10); break;
						case 'o1':
							// Ремонтная мастерская
							$user->path_update_room('1.100.1.101', 15, 'repair.php'); break;
						case 'o2':
							// Магазин
							$user->path_update_room('1.100.1.102', 15, 'shop.php'); break;
						case 'o12':
							// Страшилкина улица
							$user->path_update_room('1.107', 30); break;
					}
					break;
					//
					// ---------------
				case '1.107':
					// ---------------
					// Страшилкина улица
					//
					switch( $path )
					{
						case 'o4':
							// Банк
							$user->path_update_room('1.100.1.110', 15, 'bank.php'); break;
						case 'o8':
							// Большая торговая улица
							$user->path_update_room('1.120', 30); break;
						case 'o9':
							// Центральная площадь
							$user->path_update_room('1.100', 30); break;
					}
					break;
					//
					// ---------------
				case '1.120':
					// ---------------
					// Большая торговая ул.
					//
					switch( $path )
					{
						case 'o2':
							// Страшилкина улица
							$user->path_update_room('1.107', 30); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.1':
					// ---------------
					// Комната для новичков
					//
					if( $path == 'm6' )
					{
						if( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 )
						{
							// Зал паладинов
							$user->path_update_room('1.100.1.8.1', 5);
						}
					}
					elseif( $path == 'm7' )
					{
						if( $userdata['user_level'] >= 1 )
						{
							// Комната перехода
							$user->path_update_room('1.100.1.5', 5);
						}
					}
					break;
					//
					// ---------------
				case '1.100.1.5':
					// ---------------
					// Комната перехода
					//
					if( $path == 'm3' )
					{
						// Комната для новичков
						$user->path_update_room('1.100.1.1', 5);
					}
					elseif( $path == 'm7' )
					{
						if( $userdata['user_level'] >= 1 )
						{
							// Зал воинов 3
							$user->path_update_room('1.100.1.12', 5);
						}
					}
					break;
					//
					// ---------------
				case '1.100.1.6.1':
					// ---------------
					// Рыцарский зал
					//
					if( $path == 'm5' )
					{
						// Этаж 2
						$user->path_update_room('1.100.1.6.5', 5);
					}
					elseif( $path == 'm1' && $userdata['user_level'] >= 7 )
					{
						// Башня рыцарей-магов
						$user->path_update_room('1.100.1.6.3', 5);
					}
					else
					{
						$message = '<font color="red"><b>Вы не можете попасть в эту комнату... уровень маловат ;)</b></font>';
					}

					return $message;
					break;
					//
					// ---------------
				case '1.100.1.6.2':
					// ---------------
					// Торговый зал
					//
					if( $path == 'm7' )
					{
						$user->path_update_room('1.100.1.6.5', 5);
					}
					break;
					//
					// ---------------
				case '1.100.1.6.3':
					// ---------------
					// Башня рыцарей-магов
					//
					if( $path == 'm5' )
					{
						$user->path_update_room('1.100.1.6.1', 5);
					}
					break;
					//
					// ---------------
				case '1.100.1.6.4':
					// ---------------
					// Комната Знахаря
					//
					break;
					//
					// --------------
				case '1.100.1.6.5':
					// ---------------
					// Этаж 2
					//
					if( ( $path == '1.100.1.7.5' && $userdata['user_level'] >= 10 ) || $path == '1.100.1.9' )
					{
						// Этаж 3
						$user->path_update_room($path, 10);
					}
					elseif( ( $path == 'm1' || $path == 'm3' ) && $userdata['user_level'] >= 4 )
					{
						switch( $path )
						{
							case 'm1':
								// Рыцарский зал
								$user->path_update_room('1.100.1.6.1', 5); break;
							case 'm3':
								// Торговый зал
								$user->path_update_room('1.100.1.6.2', 5); break;
						}
					}
					else
					{
						$message = '<font color="red"><b>Вы не можете попасть в эту комнату... уровень маловат ;)</b></font>';
					}

					return $message;
					break;
					//
					// ---------------
				case '1.100.1.7.5':
					// ---------------
					// Этаж 3
					//
					if( $path == '1.100.1.6.5' )
					{
						$user->path_update_room($path, 10);
					}
					break;
					//
					// ---------------
				case '1.100.1.8.1':
					// ---------------
					// Зал паладинов
					//
					if( $path == 'm1' )
					{
						if( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 )
						{
							// Комната для новичков
							$user->path_update_room('1.100.1.1', 5);
						}
						else
						{
							$message = '<font color="red"><b>Вы не можете попасть в эту комнату, склонность не та...</b></font>';
						}
					}
					elseif( $path == 'm3' )
					{
						if( $userdata['user_align'] >= '1.7' && $userdata['user_align'] < 2 )
						{
							// Совет белого братства
							$user->path_update_room('1.100.1.8.2', 5);
						}
						else
						{
							$message = '<font color="red"><b>Вы не можете попасть в эту комнату, склонность не та...</b></font>';
						}
					}
					elseif( $path == 'm6' )
					{
						// Залы
						$user->path_update_room('1.100.1.8.6', 5);
					}

					return $message;
					break;
					//
					// ---------------
				case '1.100.1.8.2':
					// ---------------
					// Совет белого братства
					//
					if( $path == 'm7' )
					{
						// Зал паладинов
						$user->path_update_room('1.100.1.8.1', 5);
					}

					return $message;
					break;
					//
					// ---------------
				case '1.100.1.8.3':
					// ---------------
					// Зал тьмы
					//
					if( $path == 'm2' )
					{
						$user->path_update_room('1.100.1.8.6', 5);
					}
					break;
					//
					// ---------------
				case '1.100.1.8.5':
					// ---------------
					// Зал стихий
					//
					if( $path == 'm8' )
					{
						$user->path_update_room('1.100.1.8.6', 5);
					}
					break;
					//
					// ---------------
				case '1.100.1.8.6':
					// ---------------
					// Залы
					//
					switch( $path )
					{
						case 'o5':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 10); break;
					}

					if( $path == '1.100.1.9' )
					{
						$user->path_update_room($path, 10);
					}
					elseif( ( $path == 'o0' && $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) || ( $path == 'o1' && $userdata['user_align'] == 7 ) || ( $path == 'o2' && $userdata['user_align'] >= 3 && $userdata['user_align'] < 4 ) || $userdata['user_access_level'] == ADMIN )
					{
						switch( $path )
						{
							case 'o0':
								$user->path_update_room('1.100.1.8.1', 5);
								break;
							case 'o1':
								$user->path_update_room('1.100.1.8.5', 5);
								break;
							case 'o2':
								$user->path_update_room('1.100.1.8.3', 5);
								break;
						}
					}
					else
					{
						$message = '<font color="red"><b>Вы не можете попасть в эту комнату, склонность не та...</b></font>';
					}

					return $message;
					break;
					//
					// ---------------
				case '1.100.1.9':
					// ---------------
					// Бойцовский Клуб
					//
					switch( $path )
					{
						case 'o0':
							// Будуар
							if( $userdata['user_gender'] == 'Женский' || $userdata['user_access_level'] == ADMIN )
							{
								$user->path_update_room('1.100.1.13', 5); break;
							}
							else
							{
								$message = 'Вход разрешен только женщинам';
							}
							break;
						case 'o1':
							// Зал воинов
							$user->path_update_room('1.100.1.10', 5); break;
						case 'o2':
							// Зал воинов 2
							$user->path_update_room('1.100.1.11', 5); break;
						case 'o3':
							// Зал воинов 3
							$user->path_update_room('1.100.1.12', 5); break;
						case 'o4':
							// Этаж 2
							$user->path_update_room('1.100.1.6.5', 10); break;
						case 'o5':
							// Залы
							$user->path_update_room('1.100.1.8.6', 10); break;
						case 'o6':
							// Центральная Площадь
							$user->path_update_room('1.100', 15); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.10':
					// ---------------
					// Зал воинов
					//
					switch( $path )
					{
						case 'o7':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 5); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.11':
					// ---------------
					// Зал воинов 2
					//
					switch( $path )
					{
						case 'o7':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 5); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.12':
					// ---------------
					// Зал воинов 3
					//
					switch( $path )
					{
						case 'o7':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 5); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.13':
					// ---------------
					// Будуар
					//
					switch( $path )
					{
						case 'o7':
							// Бойцовский Клуб
							$user->path_update_room('1.100.1.9', 5); break;
					}
					break;
					//
					// ---------------
				case '1.100.1.101':
					// ---------------
					// Ремонтная мастерская
					//
					if( $path == '1.100' )
					{
						$user->path_update_room('1.100', 15);
					}
					break;
					//
					// ---------------
				case '1.100.1.102':
					// ---------------
					// Магазин
					//
					if( $path == '1.100' )
					{
						$user->path_update_room('1.100', 15);
					}
					break;
					//
					// ---------------
				case '1.100.1.110':
					// ---------------
					// Банк
					//
					if( $path == '1.107' )
					{
						$user->path_update_room('1.107', 15);
					}
					//
					// ---------------
			}
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Переход в другую комнату
	//
	function path_update_room($path, $going_time, $redirect = false)
	{
		global $db, $user, $userdata;

		switch( $path )
		{
			case '1.100':			$room = 'на центральную площадь'; break;
			case '1.107':			$room = 'на Страшилкину улицу'; break;
			case '1.111':			$room = 'в парк развлечений'; break;
			case '1.120':			$room = 'на Большую торговую улицу'; break;
			case '1.100.1.1':		$room = 'в комнату для новичков'; break;
			case '1.100.1.2':		$room = 'в комнату для новичков 2'; break;
			case '1.100.1.3':		$room = 'в комнату для новичков 3'; break;
			case '1.100.1.4':		$room = 'в комнату для новичков 4'; break;
			case '1.100.1.5':		$room = 'в комнату перехода'; break;
			case '1.100.1.6.1':		$room = 'в рыцарский зал'; break;
			case '1.100.1.6.2':		$room = 'в торговый зал'; break;
			case '1.100.1.6.3':		$room = 'в башню рыцарей-магов'; break;
			case '1.100.1.6.4':		$room = 'в комнату знахаря'; break;
			case '1.100.1.6.5':		$room = 'на второй этаж'; break;
			case '1.100.1.7.5':		$room = 'на третий этаж'; break;
			case '1.100.1.8.1':		$room = 'в зал паладинов'; break;
			case '1.100.1.8.2':		$room = 'на совет белого братства'; break;
			case '1.100.1.8.3':		$room = 'в зал тьмы'; break;
			case '1.100.1.8.4':		$room = 'в царство тьмы'; break;
			case '1.100.1.8.5':		$room = 'в зал стихий'; break;
			case '1.100.1.8.6':		$room = 'в залы'; break;
			case '1.100.1.9':		$room = 'в Бойцовский Клуб'; break;
			case '1.100.1.10':		$room = 'в Зал Воинов'; break;
			case '1.100.1.11':		$room = 'в Зал Воинов 2'; break;
			case '1.100.1.12':		$room = 'в Зал Воинов 3'; break;
			case '1.100.1.13':		$room = 'в Будуар'; break;
			case '1.100.1.101':		$room = 'в ремонтную мастерскую'; break;
			case '1.100.1.102':		$room = 'в магазин'; break;
			case '1.100.1.103':		$room = 'на вокзал'; break;
			case '1.100.1.105':		$room = 'в комиссионный магазин'; break;
			case '1.100.1.106':		$room = 'к почтовому отделению'; break;
			case '1.100.1.110':		$room = 'в банк'; break;
		}

		// Запись в чат
		$user->add_chat_message($userdata, '[<span>' . $userdata['user_login'] . '</span>] отправился ' . $room, false, false, true);

		//
		// Обновляем данные пользователя
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_previous_room'	=> $userdata['user_room'],
			'user_room'				=> $path,
			'user_room_time'		=> ( time() + $going_time ))) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу дойти до комнаты...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем данные
		//
		$userdata['user_previous_room'] = $userdata['user_room'];
		$userdata['user_room'] = $path;
		$userdata['user_room_time'] = time() + $going_time;
		// ----------

		// Запись в чат
		$user->add_chat_message($userdata, '[<span>' . $userdata['user_login'] . '</span>] приветствует Вас', false, false, true);

		//
		// Переадресовываем (если нужно)
		//
		if( $redirect )
		{
			redirect($redirect);
		}
		// ----------
	}
	// ---------------

	// ---------------
	// Устанавливаем cookie
	//
	function set_cookie($name, $cookiedata, $cookietime)
	{
		global $config;

		if( $config['cookie_domain'] == 'localhost' || $config['cookie_domain'] == '127.0.0.1' )
		{
			setcookie($name, $cookiedata, $cookietime, $config['cookie_path']);
		}
		else
		{
			setcookie($name, $cookiedata, $cookietime, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
		}
	}
	//
	// ---------------

	// ---------------
	// Отображаем персонажа
	//
	function show_character($userdata, $page = 'overall')
	{
		global $root_path, $template, $user;

		//
		// Одетые вещи
		//
		$items = array();

		if( $userdata['user_w1'] || $userdata['user_w2'] || $userdata['user_w3'] || $userdata['user_w4'] || $userdata['user_w5'] || $userdata['user_w6'] || $userdata['user_w7'] || $userdata['user_w8'] || $userdata['user_w9'] || $userdata['user_w10'] || $userdata['user_w11'] || $userdata['user_w12'] || $userdata['user_w13'] || $userdata['user_w14'] || $userdata['user_w15'] || $userdata['user_w16'] || $userdata['user_w100'] || $userdata['user_w101'] || $userdata['user_w102'] || $userdata['user_w103'] || $userdata['user_w104'] || $userdata['user_w105'] || $userdata['user_w106'] || $userdata['user_w107'] || $userdata['user_w108'] || $userdata['user_w109'] || $userdata['user_w400'] )
		{
			$user->obtain_items($items, $userdata, $page);
		}
		// ----------

		if( $page == 'inventory' || $page == 'main' || $page == 'overall' )
		{
			// Таблица опыта
			include($root_path . 'includes/main_exp.php');
		}

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
			$template->assign_block_vars('scrolls', array(
				'1'					=> $user->show_item($userdata, $items, 'w100'),
				'2'					=> $user->show_item($userdata, $items, 'w101'),
				'3'					=> $user->show_item($userdata, $items, 'w102'),
				'4'					=> $user->show_item($userdata, $items, 'w103'),
				'5'					=> $user->show_item($userdata, $items, 'w104'),
				'6'					=> $user->show_item($userdata, $items, 'w105'),
				'7'					=> $user->show_item($userdata, $items, 'w106'),
				'8'					=> $user->show_item($userdata, $items, 'w107'),
				'9'					=> $user->show_item($userdata, $items, 'w108'),
				'10'				=> $user->show_item($userdata, $items, 'w109'))
			);
		}
		// ----------

		$template->assign_vars(array(
			'CURRENT_HP'			=> $userdata['user_current_hp'],
			'DRWFL'					=> $user->drwfl($userdata),
			'HPSPEED'				=> $userdata['user_hpspeed'],
			'LEVEL'					=> $userdata['user_level'],
			'LOGIN'					=> $userdata['user_login'],
			'KLAN'					=> ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? '<b>Орден Света</b>' : ( ( $userdata['user_align'] > 3 && $userdata['user_align'] < 4 ) ? '<b>Армада</b>' : ( ( $userdata['user_klan'] ) ? 'Клан - ' . $userdata['user_klan'] : '')),
			'MANA'					=> ( $userdata['user_level'] >= 7 && $userdata['user_max_mana'] > 0 ) ? 'top.setMana(' . $userdata['user_current_mana'] . ',' . $userdata['user_max_mana'] . ',' . $userdata['user_manaspeed'] . ')' : '',
			'MAX_HP'				=> $userdata['user_max_hp'],
			'OBRAZ'					=> $userdata['user_obraz'],

			'STRENGTH'				=> $userdata['user_strength'],
			'AGILITY'				=> $userdata['user_agility'],
			'PERCEPTION'			=> $userdata['user_perception'],
			'VITALITY'				=> $userdata['user_vitality'],
			'INTELLECT'				=> ( $userdata['user_level'] >= 4 ) ? 'Интеллект: ' . $userdata['user_intellect'] . '<br />' : '',
			'WISDOM'				=> ( $userdata['user_level'] >= 7 ) ? 'Мудрость: ' . $userdata['user_wisdom'] . '<br />' : '',
			'SPIRITUALITY'			=> ( $userdata['user_level'] >= 10 ) ? 'Духовность: ' . $userdata['user_spirituality'] . '<br />' : '',
			'FREEDOM'				=> ( $userdata['user_level'] >= 13 ) ? 'Воля: ' . $userdata['user_freedom'] . '<br />' : '',
			'FREEDOM_OF_SPIRIT'		=> ( $userdata['user_level'] >= 16 ) ? 'Свобода духа: ' . $userdata['user_freedom_of_spirit'] . '<br />' : '',
			'HOLINESS'				=> ( $userdata['user_level'] >= 19 ) ? 'Божественность: ' . $userdata['user_holiness'] . '<br />' : '',
			'FREE_UPR'				=> ( $userdata['user_free_upr'] ) ? '<a href="main.php?skills=1">+ Способности</a>&nbsp;' : '',
			'FREE_SKILLS'			=> ( $userdata['user_free_skills'] ) ? '&bull;&nbsp;<a href="main.php?skills=1">Обучение</a>' : '',

			'EXP'					=> $userdata['user_exp'],
			'GENDER'				=> ( $userdata['user_gender'] == 'Мужской' ) ? 0 : 1,
			'NEXTEXP'				=> $nextexp,
			'WINS'					=> $userdata['user_wins'],
			'LOSSES'				=> $userdata['user_losses'],
			'DRAWS'					=> $userdata['user_draws'],
			'MONEY'					=> $user->int_money($userdata['user_money']),

			'I_HELMET'				=> $user->show_item($userdata, $items, 'w9'),
			'I_WEAPON'				=> $user->show_item($userdata, $items, 'w3'),
			'I_ARMOR'				=> ( $userdata['user_w400'] && !$userdata['user_w4'] ) ? $user->show_item($userdata, $items, 'w400') : $user->show_item($userdata, $items, 'w4'),
			'I_BELT'				=> $user->show_item($userdata, $items, 'w5'),
			'I_R_KARMAN'			=> $user->show_item($userdata, $items, 'w14'),
			'I_L_KARMAN'			=> $user->show_item($userdata, $items, 'w15'),
			'I_EARRINGS'			=> $user->show_item($userdata, $items, 'w1'),
			'I_KULON'				=> $user->show_item($userdata, $items, 'w2'),
			'I_NARUCHI'				=> $user->show_item($userdata, $items, 'w13'),
			'I_GLOVES'				=> $user->show_item($userdata, $items, 'w11'),
			'I_RING1'				=> $user->show_item($userdata, $items, 'w6'),
			'I_RING2'				=> $user->show_item($userdata, $items, 'w7'),
			'I_RING3'				=> $user->show_item($userdata, $items, 'w8'),
			'I_SHIELD'				=> $user->show_item($userdata, $items, 'w10'),
			'I_BOOTS'				=> $user->show_item($userdata, $items, 'w12'))
		);
	}
	//
	// ---------------

	// ---------------
	// Показываем вещь в слоте
	//
	function show_item($userdata, $items, $slot, $type = false)
	{
		global $db, $root_path;

		switch( $slot )
		{
			case 'w1':		$slotname = 'серьги'; $width = 60; $height = 20; break;
			case 'w2':		$slotname = 'ожерелье'; $width = 60; $height = 20; break;
			case 'w3':		$slotname = 'оружие'; $width = 60; $height = 60; break;
			case 'w4':
			case 'w400':	$slotname = 'броня'; $width = 60; $height = 80; break;
			case 'w5':		$slotname = 'пояс'; $width = 60; $height = 40; break;
			case 'w6':
			case 'w7':
			case 'w8':		$slotname = 'кольцо'; $width = 20; $height = 20; break;
			case 'w9':		$slotname = 'шлем'; $width = 60; $height = 60; break;
			case 'w10':		$slotname = 'щит'; $width = 60; $height = 60; break;
			case 'w11':		$slotname = 'перчатки'; $width = 60; $height = 40; break;
			case 'w12':		$slotname = 'обувь'; $width = 60; $height = 40; break;
			case 'w13':		$slotname = 'наручи'; $width = 60; $height = 40; break;
			case 'w14':		$slotname = 'правый карман'; $width = 40; $height = 20; break;
			case 'w15':		$slotname = 'левый карман'; $width = 40; $height = 20; break;

			case 'w100':
			case 'w101':
			case 'w102':
			case 'w103':
			case 'w104':
			case 'w105':
			case 'w106':
			case 'w107':
			case 'w108':
			case 'w109':
			case 'w110':
			case 'w111':	$slotname = 'заклинание'; $width = 40; $height = 25; break;
		}

		if( $userdata['user_' . $slot] )
		{
			// Номер слота
			$n = substr($slot, 1, strlen($slot));

			if( $n == 10 && $userdata['user_w3'] == $userdata['user_w10'] )
			{
				$n = 3;
				$items['item_img'][$n] = 'wb';
			}

			//
			// Вывод характеристик вещи
			//
			$armour_head = ( $items['item_armour_head'][$n] && $items['item_mf_armour_head'][$n] ) ? '<br />Броня головы: ' . ( 1 + $items['item_mf_armour_head'][$n] ) . '-' . ( $items['item_mf_armour_head'][$n] + $items['item_armour_head'][$n] ) . ' (' . round($items['item_mf_armour_head'][$n]) . '+d' . round($items['item_armour_head'][$n]) . ')' : ( ( $items['item_armour_head'][$n] ) ? "<br />Броня головы: 1-" . round($items['item_armour_head'][$n]) . " (d" . round($items['item_armour_head'][$n]) . ")" : '');
			$armour_body = ( $items['item_armour_body'][$n] && $items['item_mf_armour_body'][$n] ) ? "<br />Броня корпуса: " . ( 1 + $items['item_mf_armour_body'][$n] ) . "-" . ( $items['item_mf_armour_body'][$n] + $items['item_armour_body'][$n] ) . " (" . round($items['item_mf_armour_body'][$n]) . "+d" . round($items['item_armour_body'][$n]) . ")" : ( ( $items['item_armour_body'][$n] ) ? "<br />Броня корпуса: 1-" . round($items['item_armour_body'][$n]) . " (d" . round($items['item_armour_body'][$n]) . ")" : '');
			$armour_waist = ( $items['item_armour_waist'][$n] && $items['item_mf_armour_waist'][$n] ) ? "<br />Броня пояса: " . ( 1 + $items['item_mf_armour_waist'][$n] ) . "-" . ( $items['item_mf_armour_waist'][$n] + $items['item_armour_waist'][$n] ) . " (" . round($items['item_mf_armour_waist'][$n]) . "+d" . round($items['item_armour_waist'][$n]) . ")" : ( ( $items['item_armour_waist'][$n] ) ? "<br />Броня пояса: 1-" . intval($items['item_armour_waist'][$n]) . " (d" . intval($items['item_armour_waist'][$n]) . ")" : '');
			$armour_leg = ( $items['item_armour_leg'][$n] && $items['item_mf_armour_leg'][$n] ) ? "<br />Броня ног: " . ( 1 + $items['item_mf_armour_leg'][$n] ) . "-" . ( $items['item_mf_armour_leg'][$n] + $items['item_armour_leg'][$n] ) . " (" . round($items['item_mf_armour_leg'][$n]) . "+d" . round($items['item_armour_leg'][$n]) . ")" : ( ( $items['item_armour_leg'][$n] != 0 ) ? "<br />Броня ног: 1-" . round($items['item_armour_leg'][$n]) . " (d" . round($items['item_armour_leg'][$n]) . ")" : '');

			$hit = ( $items['item_min_hit'][$n] || $items['item_max_hit'][$n] ) ? "<br />Удар: " . round($items['item_min_hit'][$n]) . " - " . round($items['item_max_hit'][$n]) : '';
			$hp = ( $items['item_hp'][$n] ) ? "<br />Уровень жизни: " . $items['item_hp'][$n] : '';

			$etching = ( $items['item_etching'][$n] ) ? "<br />На ручке выгравирована надпись: " . $items['item_etching'][$n] : '';
			// ----------

			//
			// Встройка
			//
			$inbuild_magic = '';

			if( $items['item_inbuild_magic'][$n] && $items['item_inbuild_magic_desc'][$n] && $items['item_inbuild_magic_num'][$n] )
			{
				//$inbuild_magic = "<br />Встроена магия: " . $items['item_inbuild_magic_desc'][$n] . " / " . $items['item_inbuild_magic_num'][$n] . " шт.";
				$inbuild_magic = sprintf('<br />Встроена магия: %s / %d шт.', $items['item_inbuild_magic_desc'][$n], $items['item_inbuild_magic_num'][$n]);

				switch( $items['item_inbuild_magic_time'][$n] )
				{
					case 'battle':		$inbuild_magic .= ' на бой'; break;
					case 'day':			$inbuild_magic .= ' в сутки'; break;
					default:			$inbuild_magic .= ''; break;
				}

				$inbuild_magic_class = ( $type != 'inf' ) ? ' class="ismagic"' : '';
			}
			// ----------

			$use_magick = '';

			if( substr($items['item_type'][$n], 0, 6) == 'scroll' )
			{
				switch( substr($items['item_type'][$n], 7) )
				{
					//
					// Нападалки
					//
					case 'attack':
					case 'attackb':
						if( !$userdata['user_battle_id'] )
						{
							$use_magick = 'magicklogin(\'' . $items['item_name'][$n] . '\', \'main.pl\', \'' . $items['item_img'][$n] . '\', \'' . $items['item_id'][$n] . '\', \'' . $userdata['user_login'] . '\')';
						}
						break;
					// ----------

					//
					// Лечилки
					//
					case 'cureHP15':
					case 'cureHP30':
					case 'cureHP45':
					case 'cureHP60':
					case 'cureHP120':
						if( $userdata['user_current_hp'] > 0 && $userdata['user_battle_id'] > 0 )
						{
							$use_magick = 'Bmagicklogin(\'' . $items['item_name'][$n] . '\', \'' . $items['item_img'][$n] . '\', \'' . $items['item_id'][$n] . '\', \'\', \'\', \'6\')';
						}
						break;
					case 'cureHP600':
						if( $userdata['user_current_hp'] > 0 && $userdata['user_battle_id'] > 0 )
						{
							$use_magick = 'Bmagicklogin(\'' . $items['item_name'][$n] . '\', \'' . $items['item_img'][$n] . '\', \'' . $items['item_id'][$n] . '\', \'\', \'\', \'5\')';
						}
						break;
					// ----------
				}
			}

			$use_magick = ( $use_magick ) ? ' style="cursor: pointer;" onclick="' . $use_magick . '"' : '';

			//
			// Возвращаем результат
			//
			if( $type == 'battle' )
			{
				return "<img src=\"http://static.ivacuum.ru/i/items/" . $items['item_img'][$n] . ".gif\" width=\"" . $width . "\" height=\"" . $height . "\" alt=\"" . $items['item_name'][$n] . $hit . $hp . $armour_head . $armour_body . $armour_waist . $armour_leg . "\nДолговечность: " . $items['item_current_durability'][$n] . "/" . $items['item_max_durability'][$n] . $etching . $inbuild_magic . "\"" . $use_magick . ">";
			// ----------
			}
			elseif( $type == 'inf' )
			{
				//
				// Информация о персонаже
				//
				return sprintf('<a href="/encicl/item/%s.html" target="_blank"><img src="http://static.ivacuum.ru/i/items/%s.gif" width="%d" height="%d" alt="" onmousemove="fastshow(\'<b>%s</b>%s%s%s%s%s%s<br />Долговечность: %d/%d%s%s\');" onmouseout="hideshow();" /></a>', $items['item_img'][$n], $items['item_img'][$n], $width, $height, $items['item_name'][$n], $hit, $hp, $armour_head, $armour_body, $armour_waist, $armour_leg, $items['item_current_durability'][$n], $items['item_max_durability'][$n], $etching, $inbuild_magic);
				// ----------
			}
			elseif( $type == 'setdown' )
			{
				//
				// Настройки/Инвентарь
				//
				return sprintf('<a href="/main.pl?setdown=%d"><img src="http://static.ivacuum.ru/i/items/%s.gif" width="%d" height="%d" alt="" onmousemove="fastshow(\'<b>Снять %s</b>%s%s%s%s%s%s<br />Долговечность: %d/%d%s%s\');" onmouseout="hideshow();" /></a>', $n, $items['item_img'][$n], $width, $height, $items['item_name'][$n], $hit, $hp, $armour_head, $armour_body, $armour_waist, $armour_leg, $items['item_current_durability'][$n], $items['item_max_durability'][$n], $etching, $inbuild_magic);
				// ----------
			}
			else
			{
				//
				// Прочее
				//
				return sprintf('<img src="http://static.ivacuum.ru/i/items/%s.gif" width="%d" height="%d" alt="" onmousemove="fastshow(\'<b>%s</b>%s%s%s%s%s%s<br />Долговечность: %d/%d%s%s\');" onmouseout="hideshow();" />', $items['item_img'][$n], $width, $height, $items['item_name'][$n], $hit, $hp, $armour_head, $armour_body, $armour_waist, $armour_leg, $items['item_current_durability'][$n], $items['item_max_durability'][$n], $etching, $inbuild_magic);
				// ----------
			}
			// ----------
		}
		// ----------
		else
		{
			//
			// Если слот пустой...
			//
			if( $type == 'battle' || $type == 'inf' )
			{
//				return '<img src="http://static.ivacuum.ru/i/items/' . $slot . '.gif" width="' . $width . '" height="' . $height . '" alt="Пустой слот ' . $slotname . '">';
				return sprintf('<img src="http://static.ivacuum.ru/i/items/%s.gif" width="%d" height="%d" alt="" onmousemove="fastshow(\'Пустой слот %s\');" onmouseout="hideshow();" />', $slot, $width, $height, $slotname);
			}
			else
			{
				// return '<img src="http://static.ivacuum.ru/i/items/' . $slot . '.gif" width="' . $width . '" height="' . $height . '" alt="Пустой слот ' . $slotname . '">';
				return sprintf('<img src="http://static.ivacuum.ru/i/items/%s.gif" width="%d" height="%d" alt="" onmousemove="fastshow(\'Пустой слот %s\');" onmouseout="hideshow();" />', $slot, $width, $height, $slotname);
			}
			// ----------
		}
	}
	//
	// ---------------

	// ---------------
	// Начало регенерации
	//
	function start_regen(&$userdata, $battle = true)
	{
		global $config, $db;

		//
		// Боты восстанавливаются быстрее игроков ;)
		//
		if( $userdata['user_bot'] )
		{
			$userdata['user_hpspeed'] *= ( $config['fast_game'] ) ? 250 : 10;
		}
		// ----------

		// Увеличиваем скорость пополнения HP (по уровню)
		$userdata['user_hpspeed'] *= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : ( ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1);

		if( !$battle || $userdata['user_battle_id'] == 0 || $userdata['user_gain_exp'] )
		{
			//
			// Включаем счетчики восстановления
			// (для HP)
			//
			if( $userdata['user_current_hp'] < $userdata['user_max_hp'] && $userdata['user_start_regen'] == 0 )
			{
				$sql = "UPDATE " . USERS_TABLE . " SET user_start_regen = " . time() . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}
			}
			// ----------

			//
			// Для маны
			//
			if( $userdata['user_current_mana'] < $userdata['user_max_mana'] && $userdata['user_start_regen_mana'] == 0 && $userdata['user_level'] >= 7 )
			{
				$sql = "UPDATE " . USERS_TABLE . " SET user_start_regen_mana = " . time() . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}
			}
			// ----------
		}
		else
		{
			if( $userdata['user_start_regen'] > 0 )
			{
				$sql = "UPDATE " . USERS_TABLE . " SET user_start_regen = 0 WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}
			}

			$userdata['user_hpspeed'] = 0;
			$userdata['user_manaspeed'] = 0;
		}
	}
	//
	// ---------------

	// ---------------
	// Добавляем в «Состояние» что-нибудь новенькое ;)
	//
	function status_add(&$userdata, $name, $mf, $mf_hidden = false, $time, $comment)
	{
		global $db, $message, $user;

		//
		// Проверяем наличие усиления
		//
		$sql = "SELECT * FROM " . STATUS_TABLE . " WHERE `status_user_id` = " . $userdata['user_id'] . " AND `status_time` > " . time() . " AND `status_comment` = '" . $comment . "'";
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные усилений...', '', __LINE__, __FILE__, $sql);
		}

		while( $row = $db->sql_fetchrow($result) )
		{
			$status['status_id'][] = $row['status_id'];
			$status['status_user_id'][] = $row['status_user_id'];
			$status['status_name'][] = $row['status_name'];
			$status['status_mf'][] = $row['status_mf'];
			$status['status_mf_hidden'][] = $row['status_mf_hidden'];
			$status['status_time'][] = $row['status_time'];
			$status['status_comment'][] = $row['status_comment'];
		}
		// ----------

		if( isset($status) && in_array($name, $status['status_name']) )
		{
			$key_id = array_search($name, $status['status_name']);

			// Если усиление уже действует, то просто продлеваем действие
			$sql = "UPDATE " . STATUS_TABLE . " SET status_mf = " . $mf . ", status_mf_hidden = " . round($mf_hidden) . ", status_time = " . ( time() + ( $time * 60 ) ) . " WHERE `status_id` = " . $status['status_id'][$key_id];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить состояние персонажа...', '', __LINE__, __FILE__, $sql);
			}

			$message .= 'Продлено усиление характеристики "' . $user->characteristic_name($name) . '"<br />';
		}
		elseif( isset($status) && $comment == 'эликсир' && ( $name == 'strength' || $name == 'agility' || $name == 'perception' || $name == 'intellect' ) && ( in_array('strength', $status['status_name']) || in_array('agility', $status['status_name']) || in_array('perception', $status['status_name']) || in_array('intellect', $status['status_name']) ) )
		{
			// Запрещаем использование нескольких статовых эликсиров
			$message .= 'Сначала должно закончится действие предыдущего эликсира';
		}
		else
		{
			// Если усиления нет, то добавляем его
			$sql = "INSERT INTO " . STATUS_TABLE . " " . $db->sql_build_array('INSERT', array(
				'status_user_id'		=> $userdata['user_id'],
				'status_name'			=> $name,
				'status_mf'				=> $mf,
				'status_mf_hidden'		=> $mf_hidden,
				'status_time'			=> time() + ( $time * 60 ),
				'status_comment'		=> $comment));
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить состояние персонажа...', '', __LINE__, __FILE__, $sql);
			}

			// Обновляем данные
			$userdata['user_' . $name] += $mf;

			$message .= 'Усилена характеристика "' . $user->characteristic_name($name) . '"<br />';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Избавляемся от вредных привычек
	//
	function status_delete()
	{
		global $db;

		$sql = "DELETE FROM " . STATUS_TABLE . " WHERE `status_time` != '-1' AND `status_time` < " . time();
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу избавиться от вредных привычек...', '', __LINE__, __FILE__, $sql);
		}
	}
	//
	// ---------------

	// ---------------
	// Обновление HP
	//
	function update_hp(&$userdata, $before_battle = false, $from_site = false)
	{
		global $db;

		//
		// Если счетчик включен, то обновляем HP
		// при открытии/обновлении страницы
		//
		if( $userdata['user_start_regen'] > 0 && $userdata['user_hpspeed'] > 0 )
		{
			//
			// Вычисляем скорость обновления
			//
			$hpdelay = 18 / ( $userdata['user_hpspeed'] / 100 );
			// ----------

			//
			// Вычисляем насколько увеличились HP
			// с момента последнего обновления страницы
			//
			$userdata['user_current_hp'] += ( ( $userdata['user_max_hp'] / 100 ) * ( time() - $userdata['user_start_regen'] ) / $hpdelay );
			// ----------
		}
		// ----------

		//
		// Заносим текущий уровень HP в БД (если HP по макс.)
		//
		if( $userdata['user_current_hp'] > $userdata['user_max_hp'] )
		{
			$users_table = ( $from_site == true ) ? BK_USERS_TABLE : USERS_TABLE;

			$sql = "UPDATE " . $users_table . " SET user_start_regen = 0, user_current_hp = user_max_hp WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить текущий уровень HP персонажа...', '', __LINE__, __FILE__, $sql);
			}

			$userdata['user_current_hp'] = $userdata['user_max_hp'];
		}
		elseif( $before_battle == true )
		{
			$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
				'user_start_regen'	=> 0,
				'user_current_hp'	=> intval($userdata['user_current_hp']))) . " WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}

			$userdata['user_current_hp'] = intval($userdata['user_current_hp']);
		}
		// ----------

		return $userdata['user_current_hp'];
	}
	//
	// ---------------

	// ---------------
	// Обновление маны
	//
	function update_mana(&$userdata, $before_battle = false, $from_site = false)
	{
		global $db;

		//
		// Если счетчик включен, то обновляем ману
		// при открытии/обновлении страницы
		//
		if( $userdata['user_start_regen_mana'] > 0 && $userdata['user_level'] >= 7 && $userdata['user_manaspeed'] > 0 )
		{
			//
			// Вычисляем скорость обновления
			//
			$manadelay = 86;
			$speed = $userdata['user_manaspeed'] / 10;
			$manadelay = $manadelay / $speed;
			// ----------

			//
			// Вычисляем насколько увеличилась мана
			// с момента последнего обновления страницы
			//
			$start_regen_mana = $userdata['user_start_regen_mana'];
			$regen_mana_time = time() - $start_regen_mana;
			$regen_mana_percentage = $regen_mana_time / $manadelay;
			$current_mana2 = $userdata['user_max_mana'];
			$current_mana2 = ( $current_mana2 / 100 ) * $regen_mana_percentage;
			$userdata['user_current_mana'] += $current_mana2;
			// ----------
		}
		// ----------

		//
		// Заносим текущий уровень маны в БД (если мана по макс.)
		//
		if( $userdata['user_current_mana'] > $userdata['user_max_mana'] )
		{
			$users_table = ( $from_site == true ) ? BK_USERS_TABLE : USERS_TABLE;

			$sql = "UPDATE " . $users_table . " SET user_start_regen_mana = 0, user_current_mana = user_max_mana WHERE user_id = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить текущий уровень маны...', '', __LINE__, __FILE__, $sql);
			}

			$userdata['user_current_mana'] = $userdata['user_max_mana'];
		}
		elseif( $before_battle == true )
		{
			$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
				'user_start_regen_mana'		=> 0,
				'user_current_mana'			=> $userdata['user_current_mana'])) . " WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}
		}
		// ----------

		return $userdata['user_current_mana'];
	}
	//
	// ---------------

	// ---------------
	// Получение апа/уровня
	//
	function update_up($upr, $skill, $spc, $money, $vitality, $level)
	{
		global $db, $user, $userdata;

//		for( $i = 0; $i < $up_count; $i++ )
//		{
//			$total_vitality += $vitality;
//		}

		//
		// Обновляем данные
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_max_hp'		=> $userdata['user_max_hp'] + ( $vitality * 6 ),
			'user_vitality'			=> $userdata['user_vitality'] + $vitality,
			'user_level'			=> $userdata['user_level'] + $level,
			'user_stats'			=> $userdata['user_stats'] + $upr + $vitality,
			'user_free_upr'		=> $userdata['user_free_upr'] + $upr,
			'user_free_skills'	=> $userdata['user_free_skills'] + $skill,
			'user_free_spc'		=> $userdata['user_free_spc'] + $spc,
			'user_money'			=> $userdata['user_money'] + $money)) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'up', $user->drwfl($userdata) . ' получил ' . $money . ' кр. за опыт ' . $userdata['user_exp']);

		if( $level > 0 )
		{
			// Запись о получении уровня
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'level', $user->drwfl($userdata) . ' получил ' . ( $userdata['user_level'] + $level ) . ' уровень');
		}

		//
		// Обновляем страницу
		//
		redirect(append_sid("main.php"));
		// ----------
	}
	// ----------
}

?>