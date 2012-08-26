<?php

class magic
{
	// ---------------
	// Нападение
	//
	function attack($userdata, $row, $type)
	{
		global $root_path, $user;

		include($root_path . 'includes/user_zayavka.php');

		if( $row['user_login'] == '' )
		{
			$message = '<font color="red"><b>Выбранный персонаж не существует...</b></font><br />';
		}
		elseif( $userdata['user_login'] == $row['user_login'] )
		{
			$message = '<font color="red"><b>Вы не можете напасть на самого себя</b></font><br />';
		}
		elseif( $userdata['user_room'] != $row['user_room'] )
		{
			$message = '<font color="red"><b>Противник находится в другой комнате</b></font><br />';
		}
		elseif( $row['user_blocked'] )
		{
			$message = '<font color="red"><b>Персонаж заблокирован</b></font><br />';
		}
		elseif( mt_rand(1, 100) > 90 )
		{
			$message = '<font color="red"><b>Противник ускользнул от боя</b></font><br />';
		}
		elseif( $row['user_battle_id'] == 0 && $userdata['user_battle_id'] == 0 )
		{
			$message = $user->start_battle($row['user_id'], 'attack', $row);
		}
		else
		{
			// Перед нападением
			$user->start_regen($userdata, true);
			$user->update_hp($userdata, true);
			$user->update_mana($userdata, true);

			$team = ( $row['user_battle_team'] == 1 ) ? 2 : 1;
			$user->enter_to_battle($row['user_battle_id'], $userdata, $row['user_id'], $team);

			// Сообщаем о нападении
			$user->add_chat_message($userdata, '<img src="i/items/attack.gif" width="40" height="25"> <b>' . $userdata['user_login'] . '</b>, применив магию нападения, внезапно напал на "' . $row['user_login'] . '"', $userdata['user_login'] . ', ' . $row['user_login'], false, true);

			$user->add_log_message($row['user_battle_id'], '<font class="sysdate">' . date('H:i', time()) . '</font> ' . $user->drwfl($userdata) . ' вмешался в поединок!<br />');

//			$sql = "SELECT * FROM " . LOGS_TABLE . " WHERE `log_id` = " . $row['user_battle_id'];
//			if( !$result = $db->sql_query($sql) )
//			{
//				site_message('Не могу получить данные боя...', '', __LINE__, __FILE__, $sql);
//			}

			redirect('./battle.php');
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Вероятность срабатывания
	//
	function chance_to_use($userdata, $row)
	{
		global $db, $user;

		// Вероятность срабатывания
		$row['item_spell_percent'] += ( ( $userdata['user_intellect'] - $row['item_req_intellect'] ) * 3 );
		$row['item_spell_percent'] = ( $row['item_spell_percent'] > 99 ) ? 99 : $row['item_spell_percent'];

		//
		// Проверяем
		//
		if( mt_rand(1, 100) > $row['item_spell_percent'] )
		{
			$message = '<font color="red"><b>Не удалось прочесть заклинание</b></font>';
		}
		else
		{
			$message = '';
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Восстановление HP
	//
	function cureHP(&$userdata, &$row, $hp)
	{
		global $db, $user;

		if( !$row['user_login'] )
		{
			$message = '<font color="red"><b>Выбранный персонаж не существует</b></font>';
		}
		elseif( $userdata['user_battle_id'] != $row['user_battle_id'] )
		{
			$message = '<font color="red"><b>Выбранный персонаж не находится в вашем бое</b></font>';
		}
		else
		{
			if( $userdata['user_id'] != $row['user_id'] )
			{
				$row['user_current_hp'] = ( $row['user_current_hp'] + $hp > $row['user_max_hp'] ) ? $row['user_max_hp'] : $row['user_current_hp'] + $hp;

				$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . $row['user_current_hp'] . " WHERE `user_id` = " . $row['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$user->add_log_message($userdata['user_battle_id'], '<font class="sysdate">' . date('H:i', time()) . '</font> <span class="B' . $userdata['user_battle_team'] . '">' . $userdata['user_login'] . '</span> использовал заклятие восстановления энергии и восстановил уровень жизни <font color="#006699"><b>+' . $hp . '</b></font> у персонажа <span class="B' . $row['user_battle_team'] . '">' . $row['user_login'] . '</span> [' . $row['user_current_hp'] . '/' . $row['user_max_hp'] . ']<br />');
			}
			else
			{
				$userdata['user_current_hp'] = ( $userdata['user_current_hp'] + $hp > $userdata['user_max_hp'] ) ? $userdata['user_max_hp'] : $userdata['user_current_hp'] + $hp;

				$sql = "UPDATE " . USERS_TABLE . " SET user_current_hp = " . $userdata['user_current_hp'] . " WHERE `user_id` = " . $userdata['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				$user->add_log_message($userdata['user_battle_id'], '<font class="sysdate">' . date('H:i', time()) . '</font> <span class="B' . $userdata['user_battle_team'] . '">' . $userdata['user_login'] . '</span> использовал заклятие восстановления энергии и восстановил уровень жизни <font color="#006699"><b>+' . $hp . '</b></font> [' . $userdata['user_current_hp'] . '/' . $userdata['user_max_hp'] . ']<br />');
			}
		}
	}
	//
	// ---------------

	// ---------------
	// Ломаем свиток
	//
	function scroll_damage($row, &$userdata)
	{
		global $db, $user;

		if( ( $row['item_current_durability'] + 1 ) < $row['item_max_durability'] )
		{
			//
			// Ломаем предмет
			//
			$sql = "UPDATE " . ITEMS_TABLE . " SET item_current_durability = ( item_current_durability + 1 ) WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}
		elseif( ( $row['item_current_durability'] + 1 ) >= $row['item_max_durability'] )
		{
			// Для начала надо снять вещь
			$user->item_setdown($row['item_slot'], $userdata);

			$userdata['user_w' . $row['item_slot']] = 0;

			//
			// Удаляем предмет
			//
			$sql = "DELETE FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу удалить вещь...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}
	}
	//
	// ---------------
}

$magic = new magic();

?>