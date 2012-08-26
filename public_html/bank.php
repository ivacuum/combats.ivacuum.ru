<?php
/***************************************************************************
 *								 bank.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, February 23, 2005					   *
 *   copyright			: © 2005 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: bank.php, v 1.00 2005/11/12 17:55:00 V@cuum Exp $				   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

include($root_path . 'includes/user_bank.php');

//
// Начинаем восстановление и обновляем HP и ману
//
$user->start_regen($userdata);
$user->update_hp($userdata);
$user->update_mana($userdata);
// ----------

//
// Определяем переменные
//
$add				= request_var('add', '');
$add_kredit			= request_var('add_kredit', '');
$add_sum			= ( isset($_POST['add_sum']) ) ? sprintf('%.2f', $_POST['add_sum']) : 0;
$bank_user_id		= $userdata['user_id'];
$change_psw			= request_var('change_psw', '');
$enter				= request_var('enter', '');
$get_kredit			= request_var('get_kredit', '');
$get_sum			= ( isset($_POST['get_sum']) ) ? sprintf('%.2f', $_POST['get_sum']) : 0;
$in_bank			= ( isset($_COOKIE['in_bank']) ) ? $_COOKIE['in_bank'] : 0;
$message			= '';
$new_psw			= request_var('new_psw', '');
$new_psw2			= request_var('new_psw2', '');
$notepad			= request_var('notepad', '');
$num				= request_var('num', '');
$num2				= request_var('num2', '');
$open				= request_var('open', '');
$path				= request_var('path', '');
$save_notepad		= request_var('save_notepad', '');
$transfer_kredit	= request_var('transfer_kredit', '');
$transfer_sum		= ( isset($_POST['transfer_sum']) ) ? sprintf('%.2f', $_POST['transfer_sum']) : 0;
// ----------

//
// Переход по комнатам
//
if( $userdata['user_room'] != '1.100.1.110' )
{
	redirect($root_path . 'main.php');
}
elseif( $path )
{
	$user->path($path);

	redirect($root_path . 'main.php');
}
// ----------

if( $add == 'Открыть счет' )
{
	// ---------------
	// Открытие нового счёта
	//

	// Определяем переменные
	$psw			= ( isset($_POST['psw']) ) ? trim($_POST['psw']) : '';
	$psw_repeat		= ( isset($_POST['psw_repeat']) ) ? trim($_POST['psw_repeat']) : '';

	//
	// Проверки
	//
	if( $psw != $psw_repeat )
	{
		$message = 'Введенные пароли не совпадают';
	}
	elseif( strlen($psw) < 6 || strlen($psw) > 30 )
	{
		$message = 'Введенные пароль не подходит по длине (от 6 до 30 символов)';
	}
	elseif( $userdata['user_money'] < 3 )
	{
		$message = 'У вас недостаточно денег';
	}
	elseif( $in_bank )
	{
		$message = 'На данный момент вы уже управляете счетом';
	}
	else
	{
		//
		// Открываем счет
		//
		$sql = "INSERT INTO " . BANK_TABLE . " " . $db->sql_build_array('INSERT', array(
			'bank_id'			=> time(),
			'bank_user_id'		=> $userdata['user_id'],
			'bank_password'		=> md5($psw)));
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу открыть счет...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Обновляем данные персонажа
		//
		$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - 3) WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		$message = 'Вы открыли свой счет. Его номер ' . time() . '<br /><br />';
	}
	//
	// ---------------
}
elseif( $add_kredit == 'Положить кредиты на счет' )
{
	// ---------------
	// Пополнение счета
	//
	if( !$in_bank )
	{
		$message = 'Войдите в банк, используя свой номер счета и пароль...<br /><br />';
	}
	elseif( $add_sum > 0 )
	{
		$message = $user->add_kredit($add_sum, $in_bank);
	}
	else
	{
		$message = 'Маловато будет...';
	}
	//
	// ---------------
}
elseif( $change_psw == 'Сменить пароль' )
{
	// ---------------
	// Смена пароля
	//
	if( !$in_bank )
	{
		$message = 'Войдите в банк, используя свой номер счета и пароль...<br /><br />';
	}
	elseif( $new_psw != $new_psw2 )
	{
		$message = 'Введенные пароли не совпадают';
	}
	elseif( strlen($new_psw) < 6 || strlen($new_psw) > 30 || strlen($new_psw2) < 6 || strlen($new_psw2) > 30 )
	{
		$message = 'Новый пароль не подходит по длине (от 6 до 30 символов)';
	}
	else
	{
		$sql = "SELECT bank_password FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $in_bank;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить старый пароль...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		if( $row['bank_password'] == md5($new_psw) )
		{
			$message = 'Новый пароль должен отличаться от старого';
		}
		else
		{
			$sql = "UPDATE " . BANK_TABLE . " SET bank_password = '" . md5($new_psw) . "' WHERE `bank_user_id` = " . $in_bank;
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить пароль...', '', __LINE__, __FILE__, $sql);
			}

			$message = 'Пароль успешно изменён';
		}
	}
	//
	// ---------------
}
elseif( $enter == 'Войти' )
{
	// ---------------
	// Вход в банк
	//
	if( $num == $config['admin_bank_id'] && $userdata['user_access_level'] == ADMIN )
	{
		$bank_user_id = 1;
	}
	elseif( $num == $config['bots_bank_id'] && $userdata['user_bot'] == 1 )
	{
		$bank_user_id = -2;
	}

	//
	// Проверки
	//
	if( $in_bank )
	{
		$message = 'Вы уже вошли в банк...';
	}
	else
	{
		$message = $user->enter($bank_user_id);
	}
	// ----------

	if( !$message )
	{
		$user->set_cookie('in_bank', $bank_user_id, ( time() + 3600 ));
		$in_bank = $bank_user_id;
	}
	//
	// ---------------
}
elseif( $get_kredit == 'Снять кредиты со счета' )
{
	// ---------------
	// Снимаем деньги со счета
	//
	if( !$in_bank )
	{
		$message = 'Войдите в банк, используя свой номер счета и пароль...<br /><br />';
	}
	elseif( $get_sum > 0 )
	{
		$message = $user->get_kredit($get_sum, $in_bank);
	}
	else
	{
		$message = 'Маловато будет...';
	}
	//
	// ---------------
}
elseif( $save_notepad == 'Сохранить изменения' )
{
	// ---------------
	// Записная книжка
	//
	if( !$in_bank )
	{
		$message = 'Войдите в банк, используя свой номер счета и пароль...<br /><br />';
	}
	else
	{
		$sql = "UPDATE " . BANK_TABLE . " SET bank_notepad = '" . $notepad . "' WHERE `bank_user_id` = " . $in_bank;
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу сохранить изменения...', '', __LINE__, __FILE__, $sql);
		}

		$message = 'Записная книжка успешно обновлена';
	}
	//
	// ---------------
}
elseif( $transfer_kredit == 'Перевести кредиты на другой счет' )
{
	// ---------------
	// Перевод денег на другой счёт
	//
	if( !$in_bank )
	{
		$message = 'Войдите в банк, используя свой номер счета и пароль...<br /><br />';
	}
	elseif( ( ( $userdata['user_access_level'] == ADMIN || $userdata['user_align'] == 50 ) && $transfer_sum <= 0 ) || ( $userdata['user_access_level'] != ADMIN && $userdata['user_align'] == 50 && $transfer_sum <= 1 ) )
	{
		$message = 'Слишком мало денег для перевода';
	}
	elseif( !preg_match('#^[0-9]+$#', $num2) || $num2 <= 0 )
	{
		$message = 'Укажите номер счёта для перевода';
	}
	else
	{
		$message = $user->transfer_kredit($transfer_sum, $in_bank, $num2);
	}
	//
	// ---------------
}

$links = $user->get_room_links($userdata['user_room']);
$user->links_display($links);

site_header();

$template->set_filenames(array(
	'body' => 'bank_body.html')
);

if( $open == 'Открыть счет' )
{
	if( $userdata['user_money'] < 3 )
	{
		$message = 'У вас недостаточно денег для открытия нового счёта<br /><br />';
	}
	else
	{
		$template->set_filenames(array(
			'body' => 'bank_add.html')
		);
	}
}

if( $in_bank )
{
	$sql = "SELECT * FROM " . BANK_TABLE . " WHERE `bank_user_id` = " . $in_bank;
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	$template->set_filenames(array(
		'body' => 'bank_inside.html')
	);

	$template->assign_vars(array(
		'BANK_MONEY'		=> sprintf('%.2f', $row['bank_money']),
		'MESSAGE'			=> ( $message) ? '<font color="red"><b>' . $message . '</b></font>' : '',
		'MONEY'				=> sprintf('%.2f', $userdata['user_money']),
		'NOTEPAD'			=> $row['bank_notepad'])
	);
}

$template->assign_vars(array(
	'GOING_TIME'			=> $user->get_going_time(),
	'MESSAGE'				=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '')
);

$template->pparse('body');

site_bottom();

?>