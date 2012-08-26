<?php

class user_transfer extends user
{
	// ---------------
	// Перевод денег к другому персонажу
	//
	function transfer_cr($user_id, $money)
	{
		global $db, $message, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_login, user_align, user_klan, user_level FROM " . USERS_TABLE . " WHERE `user_id` = " . $user_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		//
		// Проверки
		//
		if( !$row )
		{
			$message = '<font color="red"><b>Персонаж не найден</b></font>';
		}
		elseif( $userdata['user_money'] < $money )
		{
			$message = '<font color="red"><b>Недостаточно денег для перевода</b></font>';
		}
		elseif( ( $userdata['user_align'] >= 2 && $userdata['user_align'] < 3 ) || ( $row['user_align'] >= 2 && $row['user_align'] < 3 ) )
		{
			$message = '<font color="red"><b>Хаосникам запрещено что-либо передавать</b></font>';
		}
		// ----------

		if( !$message )
		{
			//
			// Переводим деньги
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_money = ( user_money + " . $money . " ) WHERE `user_id` = " . $user_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу перевести деньги...', '', __LINE__, __FILE__, $sql);
			}

			$sql = "UPDATE " . USERS_TABLE . " SET user_money = ( user_money - " . $money . " ) WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу перевести деньги...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'transfer', 'Переведены ' . $money . ' кр. от &nbsp;' . $user->drwfl($userdata) . '&nbsp; к &nbsp;' . $user->drwfl($row));
			$user->add_admin_log_message($user_id, '1.9', 'transfer', 'Переведены ' . $money . ' кр. от &nbsp;' . $user->drwfl($userdata) . '&nbsp; к &nbsp;' . $user->drwfl($row));

			// Запись в чат
			$user->add_chat_message($userdata, '<font color="red">Внимание!</font> "' . $userdata['user_login'] . '" перевел на ваш счет ' . $money . ' кр.', $row['user_login'], true, true);

			// Обновляем переменные
			$userdata['user_money'] -= $money;

			$message = '<font color="red"><b>Удачно переведены ' . $money . ' кр. к персонажу "' . $row['user_login'] . '"</b></font>';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Передача предметов
	//
	function transfer_item($user_id, $item_id, $podarok)
	{
		global $db, $message, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_login, user_align, user_klan, user_level FROM " . USERS_TABLE . " WHERE `user_id` = " . $user_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		//
		// Получаем данные вещи
		//
		$sql = "SELECT item_id, item_user_id, item_is_equip, item_gift_from, item_name, item_img FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $item_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
		}

		$row['item'] = $db->sql_fetchrow($result);
		// ----------

		//
		// Проверки
		//
		if( !$row )
		{
			$message = '<font color="red"><b>Персонаж не найден</b></font>';
		}
		elseif( $userdata['user_money'] < 1 )
		{
			$message = '<font color="red"><b>Недостаточно денег для перевода</b></font>';
		}
		elseif( ( $userdata['user_align'] >= 2 && $userdata['user_align'] < 3 ) || ( $row['user_align'] >= 2 && $row['user_align'] < 3 ) )
		{
			$message = '<font color="red"><b>Хаосникам запрещено что-либо передавать</b></font>';
		}
		elseif( $row['item']['item_user_id'] != $userdata['user_id'] )
		{
			$message = '<font color="red"><b>Вы не можете передать чужую вещь</b></font>';
		}
		elseif( $row['item']['item_is_equip'] == 1 )
		{
			$message = '<font color="red"><b>Вы не можете передать одетую на вас вещь</b></font>';
		}
		elseif( $row['item']['item_gift_from'] != '' )
		{
			$message = '<font color="red"><b>Подарок нельзя передавать</b></font>';
		}
		// ----------

		if( !$message )
		{
			//
			// Передаем вещь
			//
			$sql = "UPDATE " . ITEMS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
				'item_user_id'			=> $user_id,
				'item_sort_order'		=> get_max_row('item_sort_order', 'first_item', ITEMS_TABLE, '`item_user_id` = ' . $user_id) + 1,
				'item_gift_from'			=> ( $podarok == 1 ) ? $userdata['user_login'] : NULL,
				'item_gift_from_real'	=> ( $podarok == 1 ) ? $userdata['user_login'] : NULL)) . " WHERE `item_id` = " . $item_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу передать вещь...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			//
			// Снимаем 1 кр. за передачу
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_money = ( user_money - 1 ) WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу снять деньги за перевод...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'transfer', ( ( $podarok == 1 ) ? 'Подарен' : 'Передан' ) . ' предмет <img align="absmiddle" src="i/items/' . $row['item']['item_img'] . '.gif" alt="' . $row['item']['item_name'] . '"> от &nbsp;' . $user->drwfl($userdata) . '&nbsp; к &nbsp;' . $user->drwfl($row));
			$user->add_admin_log_message($user_id, '1.9', 'transfer', ( ( $podarok == 1 ) ? 'Подарен' : 'Передан' ) . ' предмет <img align="absmiddle" src="i/items/' . $row['item']['item_img'] . '.gif" alt="' . $row['item']['item_name'] . '"> от &nbsp;' . $user->drwfl($userdata) . '&nbsp; к &nbsp;' . $user->drwfl($row));

			// Запись в чат
			$user->add_chat_message($userdata, '<font color="red">Внимание!</font> "' . $userdata['user_login'] . '" ' . ( ( $podarok == 1 ) ? 'подарил' : 'передал' ) . ' вам "' . $row['item']['item_name'] . '"', $row['user_login'], true, true);

			// Обновляем переменные
			$userdata['user_money'] -= 1;

			$message = '<font color="red"><b>Удачно ' . ( ( $podarok == 1 ) ? 'подарен' : 'передан' ) . ' предмет "' . $row['item']['item_name'] . '" к персонажу "' . $row['user_login'] . '"</b></font>';
		}

		return $message;
	}
	//
	// ---------------
}

$user = new user_transfer();

?>