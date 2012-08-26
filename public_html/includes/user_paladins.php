<?php

class user_paladins extends user
{
	// ---------------
	// Наложение заклятия смерти
	//
	function death($username, $reason)
	{
		global $db, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_blocked, user_vip, user_town FROM " . USERS_TABLE . " WHERE `user_login` = '" . $username . "'";
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
			$message = 'Персонаж не найден';
		}
		elseif( $row['user_blocked'] )
		{
			$message = 'Персонаж уже заблокирован';
		}
		elseif( $row['user_town'] != $userdata['user_town'] )
		{
			$message = 'Персонаж находится в другом городе';
		}
		elseif( $row['user_vip'] && $userdata['user_access_level'] != ADMIN && $userdata['user_align'] != '1.99' )
		{
			$message = 'Вы не можете заблокировать VIP-персонажа';
		}
		else
		{
			// Блокируем персонажа
			$sql = "UPDATE " . USERS_TABLE . " SET `user_blocked` = 1, `user_blocked_reason` = '" . ( ( $reason ) ? date('d.m.Y H:i', time()) . ' ' . $reason : '') . "' WHERE `user_id` = " . $row['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу заблокировать персонажа...', '', __LINE__, __FILE__, $sql);
			}

			// Запись в чат и личное дело
			$user->add_admin_log_message($row['user_id'], 'admin', 'death', $user->drwfl($userdata) . ' наложил заклинание смерти на "' . $username . '"' . ( ( $reason ) ? ' по причине: ' . $reason : '' ));
			$user->add_chat_message($userdata, '<img src="i/items/death.gif" width="40" height="25"> ' . ( ( $userdata['user_access_level'] == ADMIN ) ? 'Ангел "' : ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 'Паладин "' : '')) . $userdata['user_login'] . '" наложил заклинание смерти на "' . $username . '"', $userdata['user_login'] . ', ' . $username, false, true);

			$message = 'Успешно наложено заклятие смерти на "' . $username . '".';
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Смена склонности
	//
	function make_align($username, $align)
	{
		global $db, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_blocked, user_align, user_town FROM " . USERS_TABLE . " WHERE `user_login` = '" . $username . "'";
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
			$message = 'Персонаж не найден';
		}
		elseif( $row['user_blocked'] )
		{
			$message = 'Персонаж находится в блоке';
		}
		elseif( $row['user_town'] != $userdata['user_town'] )
		{
			$message = 'Персонаж находится в другом городе';
		}
		elseif( $row['user_align'] > 2 && $row['user_align'] < 3 )
		{
			$message = 'Персонаж находится в хаосе';
		}
		elseif( $userdata['user_access_level'] != ADMIN && $userdata['user_align'] != '1.99' )
		{
			$message = 'Вы не можете менять склонность персонажам';
		}
		else
		{
			switch( $align )
			{
				case '1':		$message = ( $row['user_align'] != 0 && $row['user_align'] != '1.4' && $row['user_align'] != '1.5' && $row['user_align'] != '1.6' && $row['user_align'] != 7 ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.4':		$message = ( $row['user_align'] != 1 && $row['user_align'] != '1.5' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.5':		$message = ( $row['user_align'] != 1 && $row['user_align'] != '1.4' && $row['user_align'] != '1.7' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.6':		$message = ( $row['user_align'] != 0 && $row['user_align'] < 1 && $row['user_align'] > 2 ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.7':		$message = ( $row['user_align'] != '1.5' && $row['user_align'] != '1.75' && $row['user_align'] != '1.9' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.75':	$message = ( $row['user_align'] != '1.7' && $row['user_align'] != '1.9' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.9':		$message = ( $row['user_align'] != '1.7' && $row['user_align'] != '1.75' && $row['user_align'] != '1.91' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.91':	$message = ( $row['user_align'] != '1.9' && $row['user_align'] != '1.92' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.92':	$message = ( $row['user_align'] != '1.91' && $row['user_align'] != '1.99' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '1.99':	$message = ( $userdata['user_access_level'] != ADMIN ) ? 'Вы не можете установить данную склонность' : ''; break;
				case '3.01':	$message = ( $row['user_align'] != 0 && $row['user_align'] != '3.05' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.05':	$message = ( $row['user_align'] != '3.01' && $row['user_align'] != '3.07' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.06':	$message = ( $row['user_align'] != 0 && $row['user_align'] <= 3 && $row['user_align'] > 4 ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.07':	$message = ( $row['user_align'] != '3.05' && $row['user_align'] != '3.09' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.09':	$message = ( $row['user_align'] != '3.07' && $row['user_align'] != '3.091' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.091':	$message = ( $row['user_align'] != '3.09' && $row['user_align'] != '3.99' ) ? 'Вы не можете сменить склонность данным образом' : ''; break;
				case '3.99':	$message = ( $userdata['user_access_level'] != ADMIN ) ? 'Вы не можете установить данную склонность' : ''; break;
				default:		$message = '';
			}

			if( !$message )
			{
				switch( $align )
				{
					case '1':		$align_name = 'паладином'; break;
					case '1.4':		$align_name = 'таможенным паладином'; break;
					case '1.5':		$align_name = 'паладином солнечной улыбки'; break;
					case '1.6':		$align_name = 'инквизитором'; break;
					case '1.7':		$align_name = 'паладином огненной зари'; break;
					case '1.75':	$align_name = 'хранителем знаний'; break;
					case '1.9':		$align_name = 'паладином неба'; break;
					case '1.91':	$align_name = 'старший паладином неба'; break;
					case '1.92':	$align_name = 'кавалером'; break;
					case '1.99':	$align_name = 'верховным паладином'; break;
					case '2.5':		$align_name = 'истинным хаосником'; break;
					case '3':		$align_name = 'темным'; break;
					case '3.01':	$align_name = 'тарманом-служителем'; break;
					case '3.05':	$align_name = 'тарманом-надсмотрщиком'; break;
					case '3.06':	$align_name = 'карателем'; break;
					case '3.07':	$align_name = 'тарманом-убийцей'; break;
					case '3.09':	$align_name = 'тарманом-палачом'; break;
					case '3.091':	$align_name = 'тарманом-владыкой'; break;
					case '3.99':	$align_name = 'верховным тарманом'; break;
					case '7':		$align_name = 'свободным нейтралом'; break;
					case '50':		$align_name = 'алхимиком'; break;
				}

				// Устанавливаем склонность
				$sql = "UPDATE " . USERS_TABLE . " SET user_align = '" . $align . "' WHERE `user_id` = " . $row['user_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
				}

				//
				// Запись в чат и личное дело
				//
				if( !$align )
				{
					$user->add_admin_log_message($row['user_id'], 'admin', 'change_align', $user->drwfl($userdata) . ' ' . ( ( $row['user_align'] >= 1 && $row['user_align'] < 2 ) ? 'лишил паладинства' : ( ( $row['user_align'] >= 3 && $row['user_align'] < 4 ) ? 'изгнал из темного братства' : 'лишил склонности') ) . ' "' . $username . '"');
					$user->add_chat_message($userdata, ( ( $userdata['user_access_level'] == ADMIN ) ? 'Ангел "' : ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 'Паладин "' : '')) . $userdata['user_login'] . '" ' . ( ( $row['user_align'] >= 1 && $row['user_align'] < 2 ) ? 'лишил паладинства' : ( ( $row['user_align'] >= 3 && $row['user_align'] < 4 ) ? 'изгнал из темного братства' : 'лишил склонности') ) . ' "' . $username . '"', $userdata['user_login'] . ', ' . $username, false, true);

					$message = 'Вы успешно лишили склонности "' . $username . '"';
				}
				else
				{
					$user->add_admin_log_message($row['user_id'], 'admin', 'change_align', $user->drwfl($userdata) . ' сделал ' . $align_name . ' "' . $username . '"');
					$user->add_chat_message($userdata, ( ( $userdata['user_access_level'] == ADMIN ) ? 'Ангел "' : ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 'Паладин "' : '')) . $userdata['user_login'] . '" сделал ' . $align_name . ' "' . $username . '"', $userdata['user_login'] . ', ' . $username, false, true);

					$message = 'Вы успешно сделали ' . $align_name . ' "' . $username . '"';
				}
				// ----------
			}
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Наложение заклятия "Молчание"
	//
	function silence($username, $time)
	{
		global $db, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_room FROM " . USERS_TABLE . " WHERE `user_login` = '" . $username . "'";
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
			$message = 'Персонаж не найден';
		}
		elseif( $userdata['user_access_level'] != ADMIN && $row['user_room'] != $userdata['user_room'] )
		{
			$message = 'Персонаж "' . $username . '" находится в другой комнате';
		}
		else
		{
			//
			// Накладываем молчанку
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_silence = " . ( time() + ( $time * 60 ) ) . " WHERE `user_login` = '" . $username . "'";
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу наложить заклятие молчания...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			// Запись в чат и личное дело
			$user->add_admin_log_message($row['user_id'], '1.9', 'sleep', '"' . $userdata['user_login'] . '" наложил заклятие молчания на "' . $username . '" сроком ' . $user->spell_time($time));
			$user->add_chat_message($userdata, '<img src="i/items/sleep.gif" width="40" height="25"> ' . ( ( $userdata['user_access_level'] == ADMIN ) ? 'Ангел "' : ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 'Паладин "' : '')) . $userdata['user_login'] . '" наложил заклятие молчания на "' . $username . '" сроком ' . $user->spell_time($time), $userdata['user_login'] . ', ' . $username, false, true);

			$message = 'Успешно наложено заклятие молчания на "' . $username . '" сроком ' . $user->spell_time($time) . '.';
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Преобразование времени действия заклятия
	//
	function spell_time($time)
	{
		if( $time < 60 )
		{
			$message = $time . ' минут';
		}
		elseif( $time == 60 )
		{
			$message = '1 час';
		}
		elseif( $time == 120 )
		{
			$message = '2 часа';
		}
		elseif( $time == 180 )
		{
			$message = '4 часа';
		}
		elseif( $time == 360 )
		{
			$message = '6 часов';
		}
		elseif( $time == 720 )
		{
			$message = '12 часов';
		}
		elseif( $time == 1440 )
		{
			$message = 'одни сутки';
		}
		elseif( $time == 2880 )
		{
			$message = 'два дня';
		}
		elseif( $time == 4320 )
		{
			$message = 'три дня';
		}
		elseif( $time == 10080 )
		{
			$message = 'неделя';
		}
		elseif( $time == 44640 )
		{
			$message = 'месяц';
		}
		elseif( $time == 262080 )
		{
			$message = 'полгода';
		}
		elseif( $time == 525600 )
		{
			$message = 'год';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Снятие заклятия "Молчание"
	//
	function sleep_off($username)
	{
		global $db, $user, $userdata;

		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_room FROM " . USERS_TABLE . " WHERE `user_login` = '" . $username . "'";
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
			$message = '<font color="red">Персонаж не найден</font>';
		}
		elseif( $userdata['user_access_level'] != ADMIN && $row['user_room'] != $userdata['user_room'] )
		{
			$message = '<font color="red">Персонаж "' . $username . '" находится в другой комнате</font>';
		}
		else
		{
			$message = '';
		}
		// ----------

		if( !$message )
		{
			//
			// Снимаем молчанку
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_silence = 0 WHERE `user_login` = '" . $username . "'";
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу снять заклятие молчания...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			// Запись в чат и личное дело
			$user->add_admin_log_message($row['user_id'], '1.9', 'sleep_off', '"' . $userdata['user_login'] . '" снял заклятие молчания с "' . $username . '"');
			$user->add_chat_message($userdata, '<img src="i/items/sleep_off.gif" width="40" height="25"> ' . ( ( $userdata['user_access_level'] == ADMIN ) ? 'Ангел "' : ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? 'Паладин "' : '')) . $userdata['user_login'] . '" снял заклятие молчания с "' . $username . '"', $userdata['user_login'] . ', ' . $username, false, true);

			$message = 'Успешно снято заклятие молчания с "' . $username . '".';
		}

		return $message;
	}
	//
	// ---------------
}

$user = new user_paladins();

?>