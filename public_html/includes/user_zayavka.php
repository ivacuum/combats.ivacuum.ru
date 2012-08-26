<?php

class user_zayavka extends user
{
	// ---------------
	// Вход в бой
	//
	function enter_to_battle($log_id, $userdata, $enemy_id, $team)
	{
		global $db;

		//
		// Вставляем данные
		//
		$sql = "INSERT INTO " . LOGS_USERS_TABLE . " " . $db->sql_build_array('INSERT', array(
			'log_id'				=> $log_id,
			'log_user_align'		=> $userdata['user_align'],
			'log_user_id'			=> $userdata['user_id'],
			'log_user_klan'			=> $userdata['user_klan'],
			'log_user_level'		=> $userdata['user_level'],
			'log_user_login'		=> $userdata['user_login'],
			'log_user_team'			=> $team));
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные боя...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем данные персонажа
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_battle_id'		=> $log_id,
			'user_battle_team'		=> $team,
			'user_last_enemy'		=> $enemy_id)) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Создаем бой
	//
	function start_battle($enemy_id, $type, $enemy_userdata = false)
	{
		global $config, $db, $root_path, $user, $userdata;

		if( $userdata['user_last_enemy'] == $enemy_id )
		{
			if( $type != 'attack' )
			{
				redirect($root_path . 'zayavka.pl');
			}
		}
	
		// Получаем данные противника
		$row = ( $enemy_userdata ) ? $enemy_userdata : get_userdata($enemy_id);

		//
		// Обновляем HP и ману
		//
		$user->start_regen($userdata, true);
		$user->update_hp($userdata, true);
		$user->update_mana($userdata, true);

		$user->start_regen($row);
		$user->update_hp($row);
		$user->update_mana($row);
		// ----------

		if( ( $row['user_current_hp'] / $row['user_max_hp'] ) < '0.33' && $type == 'attack' )
		{
			$message = 'Противник слишком слаб';
		}
		elseif( $row['user_current_hp'] > 0 )
		{
			//
			// Обновляем уровень HP
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . $row['user_current_hp'] . ", user_max_hp = " . $row['user_max_hp'] . ", user_current_mana = " . $row['user_current_mana'] . ", user_max_mana = " . $row['user_max_mana'] . ", user_start_regen = 0, user_start_regen_mana = 0 WHERE `user_id` = " . $row['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			//
			// Создаем бой
			//
			$sql = "INSERT INTO " . LOGS_TABLE . " " . $db->sql_build_array('INSERT', array(
				'log_time_start'		=> time(),
				'log_room'				=> $userdata['user_room'],
				'log_battle_type'		=> 1));
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу создать новый бой...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			$battle_id = $db->sql_nextid();

			//
			// Засовываем персонажей в бой
			//
			$user->enter_to_battle($battle_id, $userdata, $row['user_id'], 1);
			$user->enter_to_battle($battle_id, $row, $userdata['user_id'], 2);
			// ----------

			//
			// Добавляем запись в лог боя
			//
			$user->add_log_message($battle_id, 'Часы показывали <font class="date">' . date('d.m.y H:i', time()) . '</font>, когда ' . $user->drwfl($userdata) . ' и ' . $user->drwfl($row) . ' бросили вызов друг другу.<br />');
			// ----------

			if( $type == 'attack' )
			{
				// Сообщаем о нападении
				$user->add_chat_message($userdata, '<img src="http://static.ivacuum.ru/i/items/attack.gif" width="40" height="25"> <b>' . $userdata['user_login'] . '</b>, применив магию нападения, внезапно напал на "' . $row['user_login'] . '"', $userdata['user_login'] . ', ' . $row['user_login'], false, true);
			}
			else
			{
				// Сообщаем о начале поединка
				$user->add_chat_message($userdata, '<a href="/logs.pl?log=' . $battle_id . '" target=_blank>Бой</a> между <b>' . $userdata['user_login'] . '</b> и <b>' . $row['user_login'] . '</b> начался.<br>', false, false, true);
			}

			// В бой!
			redirect($root_path . 'battle.pl');
		}
		else
		{
			$message = 'Вы не можете напасть на ' . $row['user_login'] . '... ' . ( ( $row['user_gender'] == 'Мужской' ) ? 'он мертв' : 'она мертва' );
		}

		return $message;
	}
	//
	// ---------------
}

$user = new user_zayavka();

?>