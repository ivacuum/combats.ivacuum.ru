<?php

class user_bank extends user
{
	// ---------------
	// Пополнение счета
	//
	function add_kredit($money, $bank_user_id)
	{
		global $db, $user, $userdata;

		// Получаем номер счета
		$sql = "SELECT bank_id FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $bank_user_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить номер счёта...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		// Кладем деньги на банковский счет
		$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money + " . $money . ") WHERE `bank_user_id` = " . $bank_user_id;
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу пополнить счет...', '', __LINE__, __FILE__, $sql);
		}

		// Берем деньги у персонажа
		$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - " . $money . ") WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу взять деньги...', '', __LINE__, __FILE__, $sql);
		}

		// Обновляем переменные
		$userdata['user_money'] -= $money;

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'add_kredit', 'Персонаж "' . $userdata['user_login'] . '" положил на свой счет №' . $row['bank_id'] . ' ' . $money . ' кр.');

		return 'Вы удачно положили на свой счет ' . $money . ' кр.';
	}
	//
	// ---------------

	// ---------------
	// Вход в банк
	//
	function enter($bank_user_id)
	{
		global $_POST, $db, $user, $userdata;

		// Определяем переменные
		$bank_id	= ( isset($_POST['num']) ) ? $_POST['num'] : '';
		$password	= ( isset($_POST['psw']) ) ? trim($_POST['psw']) : '';

		// Получаем пароль от счета (если таковой существует)
		$sql = "SELECT bank_password FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $bank_user_id . " AND `bank_id` = " . $bank_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные счета...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		// Проверки...
		if( !preg_match('#^[0-9]+$#', $bank_id) )
		{
			$message = 'Неверно указан номер счета<br /><br />';
		}
		elseif( !$row )
		{
			$message = 'У вас нет банковского счета с таким номером<br /><br />';
		}
		elseif( md5($password) != $row['bank_password'] )
		{
			$message = 'Вы ввели неправильный пароль<br /><br />';
		}
		else
		{
			$message = '';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Снятие денег со счета
	//
	function get_kredit($money, $bank_user_id)
	{
		global $db, $user, $userdata;

		//
		// Получаем состояние банковского счета
		//
		$sql = "SELECT bank_id, bank_money FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $bank_user_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные банковского счета...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		if( $row['bank_money'] >= $money )
		{
			// Снимаем деньги со счета
			$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money - " . $money . ") WHERE `bank_user_id` = " . $bank_user_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу снять деньги с банковского счета...', '', __LINE__, __FILE__, $sql);
			}

			// Переводим деньги персонажу
			$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money + " . $money . ") WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу перевести деньги персонажу...', '', __LINE__, __FILE__, $sql);
			}

			// Обновляем переменные
			$userdata['user_money'] += $money;

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'get_kredit', 'Персонаж "' . $userdata['user_login'] . '" снял со своего счета №' . $row['bank_id'] . ' ' . $money . ' кр.');

			$message = 'Вы удачно сняли со своего счета ' . $money . ' кр.';
		}
		else
		{
			$message = 'Недостаточно денег на счёте';
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Перевод денег на другой счет
	//
	function transfer_kredit($money, $bank_user_id, $bank_transfer_id)
	{
		global $config, $db, $num2, $user, $userdata;

		// Получаем количество денег на счёте
		$sql = "SELECT bank_money FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $bank_user_id;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные банковского счета...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		// Комиссия
		$commision = ( ( ( $money / 100 ) * 3 ) < 1 ) ? 1 : ( ( $money / 100 ) * 3 );
		$commision = sprintf('%.2f', $commision);
		$money -= $commision;
		$money = sprintf('%.2f', $money);

		if( $userdata['user_access_level'] == ADMIN || $userdata['user_align'] == 50 )
		{
			$money += $commision;
			$commision = 0;
		}

		if( $row['bank_money'] >= ( $money + $commision ) )
		{
			// Переводим деньги на другой счет
			$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money + " . $money . ") WHERE `bank_id` = " . $bank_transfer_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу перевести деньги на другой счет...', '', __LINE__, __FILE__, $sql);
			}

			// Снимаем деньги с отправляемого счета
			$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money - " . ( $money + $commision ) . ") WHERE `bank_user_id` = " . $bank_user_id;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу снять деньги с вашего банковского счета...', '', __LINE__, __FILE__, $sql);
			}

			//
			// Получаем номер счёта отправляющего деньги
			//
			$sql = "SELECT bank_id FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $bank_user_id;
			if( !$result = $db->sql_query($sql) )
			{
				site_message('Не могу получить ID владельца счёта...', '', __LINE__, __FILE__, $sql);
			}
				
			$row = $db->sql_fetchrow($result);

			$bank_id = $row['bank_id'];
			// ----------

			//
			// Получаем номер счёта и имя получающего деньги
			//
			if( $bank_transfer_id == $config['admin_bank_id'] )
			{
				$bank_transfer_access_level = ADMIN;
				$bank_transfer_username = 'к администрации БК';
				$bank_transfer_user_align = '';
				$bank_transfer_user_id = '-1';
			}
			elseif( $bank_transfer_id == $config['bots_bank_id'] )
			{
				$bank_transfer_access_level = '';
				$bank_transfer_username = 'к ботам';
				$bank_transfer_user_align = '';
				$bank_transfer_user_id = '-2';
			}
			else
			{
				$sql = "SELECT b.bank_user_id, u.user_access_level, u.user_login, u.user_align FROM " . BANK_TABLE . " b, " . USERS_TABLE . " u WHERE b.bank_id = " . $bank_transfer_id . " AND b.bank_user_id = u.user_id";
				if( !$result = $db->sql_query($sql) )
				{
					site_message('Не могу получить ID владельца счёта...', '', __LINE__, __FILE__, $sql);
				}

				$row = $db->sql_fetchrow($result);

				$bank_transfer_access_level = $row['user_access_level'];
				$bank_transfer_username = 'к персонажу "' . $row['user_login'] . '"';
				$bank_transfer_user_align = $row['user_align'];
				$bank_transfer_user_id = $row['bank_user_id'];
			}

			$admin_log_message = 'Персонаж "' . $userdata['user_login'] . '" перевел со своего банковского счета' . ( ( $userdata['user_access_level'] == ADMIN || $userdata['user_align'] == 50 ) ? '' : ' №' . $bank_id ) . ' на счет' . ( ( $bank_transfer_access_level == ADMIN || $bank_transfer_user_align == 50 ) ? '' : ' №' . $bank_transfer_id ) . ' ' . $bank_transfer_username . ' ' . ( $money + $commision ) . ' кр.' . ( ( $userdata['user_access_level'] == ADMIN || $userdata['user_align'] == 50 ) ? '' : ' Дополнительно снято ' . $commision . ' кр. за услуги банка CB');

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'transfer', $admin_log_message);

			if( $bank_transfer_user_id > 0 )
			{
				$user->add_admin_log_message($bank_transfer_user_id, '1.9', 'transfer', $admin_log_message);
			}

			$message = 'Вы удачно перевели ' . $money . ' кр. на счет №' . $bank_transfer_id;
		}
		else
		{
			$message = 'Недостаточно денег на счёте';
		}

		return $message;
	}
	//
	// ---------------
}

$user = new user_bank();

?>