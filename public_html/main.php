<?php
/***************************************************************************
 *								 main.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: main.php, v 1.00 2005/11/19 19:26:00 V@cuum Exp $				   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

if( !$userdata['session_logged_in'] || $userdata['user_blocked'] )
{
	// Чужакам вход воспрещён
	redirect($root_path . 'return.php');
}
elseif( $userdata['user_battle_id'] > 0 )
{
	// Если персонаж в бое, то он и должен там быть
	redirect($root_path . 'battle.php');
}
// ----------

//
// Определяем переменные
//
$attack				= request_var('attack', '');
$attackc			= request_var('attackc', '');
$boxsort			= request_var('boxsort', '');
$changepsw			= request_var('changepsw', '');
$clear_abil			= intval(request_var('clear_abil', '0'));
$clubmap			= request_var('clubmap', '');
$delk				= request_var('delk', '');
$drop				= request_var('drop', '');
$dropall			= request_var('dropall', '');
$editanketa			= request_var('editanketa', '');
$edit				= ( isset($_GET['edit']) ) ? intval($_GET['edit']) : '';
$findlogin			= request_var('findlogin', '');
$friendadd			= request_var('friendadd', '');
$friendremove		= request_var('friendremove', '');
$friends			= request_var('friends', '');
$going_time			= 0;
$message			= '';
$n					= request_var('n', 0);
$param				= request_var('param', '');
$path				= request_var('path', '');
$podarok			= request_var('podarok', 0);
$saveanketa			= request_var('saveanketa', '');
$savekmp			= request_var('savekmp', '');
$set				= request_var('set', '');
$setcancel			= request_var('setcancel', '');
$setdown			= request_var('setdown', '');
$setedit			= request_var('setedit', 1);
$setimage			= request_var('setimage', '');
$setkredit			= sprintf('%.2f', request_var('setkredit', ''));
$setobject			= request_var('setobject', '');
$set_abil			= request_var('set_abil', '');
$set_special		= request_var('set_special', '');
$skills				= request_var('skills', '');
$skmp				= request_var('skmp', '');
$to_id				= request_var('to_id', '');
$upr				= request_var('upr', '');
$use				= request_var('use', '');
$zvanie				= '';
// ----------

// Определяем состояние
if( !$skills && !$upr )
{
	$user->obtain_status($userdata);
}

if( $attack && ( $userdata['user_room'] == '1.100' || $userdata['user_room'] == '1.107' || $userdata['user_room'] == '1.120' ) )
{
	//
	// Бесплатные нападения
	//
	$row = get_userdata('', $attack);

	include($root_path . 'includes/magic.php');

	$message = $magic->attack($userdata, $row, 'attack');
	// ----------
}
elseif( $config['fast_game'] && isset($_GET['start_bot_battle']) && $userdata['user_bot'] && $userdata['user_level'] < $config['fast_game_level'] )
{
	include($root_path . 'includes/user_zayavka.php');

	$sql = "SELECT user_id FROM " . USERS_TABLE . " WHERE `user_id` <> " . $userdata['user_id'] . " AND `user_id` <> " . $userdata['user_last_enemy'] . " AND `user_battle_id` = 0 AND `user_bot` = 1 AND `user_start_regen` < " . ( time() - 100 ) . " AND `user_level` = " . $userdata['user_level'];
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные заявок...', '', __LINE__, __FILE__, $sql);
	}

	$opponents = array();

	while( $row = $db->sql_fetchrow($result) )
	{
		$opponents[] = $row['user_id'];
	}

	if( $opponents )
	{
		$user->start_battle($opponents[mt_rand(0, count($opponents) - 1)], 'fiz');
	}
}

//
// Начинаем восстановление и обновляем HP и ману
//
$user->start_regen($userdata);
$user->update_hp($userdata);
$user->update_mana($userdata);
// ----------

//
// Если мы увидели что-то похожее в адресной
// строке, то надо действовать
//
$userdata['user_main_edit'] = ( isset($_COOKIE['edit']) ) ? $_COOKIE['edit'] : 1;

if( $edit == 1 || $edit == 2 || $edit == 3 || $edit == 4 )
{
	if( $edit != $userdata['user_main_edit'] )
	{
		$user->set_cookie('edit', $edit, 0);

		$userdata['user_main_edit'] = $edit;
	}

	include($root_path . 'includes/main_edit.php');
	exit;
}
elseif( $_SERVER['QUERY_STRING'] == 'edit=' || !empty($boxsort) || $delk || !empty($savekmp) || !empty($skmp) || !empty($set) || !empty($setdown) || !empty($use) || !empty($drop) )
{
	include($root_path . 'includes/main_edit.php');
	exit;
}
elseif( $changepsw )
{
	// ---------------
	// Смена пароля
	//
	if( $changepsw == 'Сменить пароль' )
	{
		// Определяем переменные
		$current_password		= request_var('oldpsw', '');
		$new_password			= request_var('newpsw', '');
		$new_password_repeat	= request_var('newpsw2', '');

		//
		// Проверки
		//
		if( md5($current_password) != $userdata['user_password'] )
		{
			$message = 'Вы ввели неправильный пароль';
		}
		elseif( $new_password != $new_password_repeat )
		{
			$message = 'Введенные пароли не совпадают';
		}
		elseif( md5($new_password) == $userdata['user_password'] )
		{
			$message = 'Новый пароль должен отличаться от старого';
		}
		elseif( strlen($new_password) < 6 || strlen($new_password) > 30 )
		{
			$message = 'Пароль не подходит по длине (от 6 до 30 символов)';
		}
		else
		{
			//
			// Обновляем пароль
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_password = '" . md5($new_password) . "' WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу обновить пароль...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			$message = 'Пароль успешно изменен';
		}
		// ----------
	}

	site_header();

	$template->set_filenames(array(
		'body' => 'main_changepsw.html')
	);
	
	$template->assign_vars(array(
		'LOGIN'			=> $userdata['user_login'],
		'MESSAGE'		=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font><br />' : '')
	);

	$template->pparse('body');

	site_bottom();
	//
	// ---------------
}
elseif( $clubmap )
{
	// ---------------
	// Карта клуба
	//
	switch( $userdata['user_room'] )
	{
		//
		// Первый этаж
		//
		case '1.100.1.1':
		case '1.100.1.5':
		case '1.100.1.9':
		case '1.100.1.10':
		case '1.100.1.11':
		case '1.100.1.12':
		case '1.100.1.13':	$template_file = 'map_1floor.html'; break;
		// ----------

		//
		// Второй этаж
		//
		case '1.100.1.6.1':
		case '1.100.1.6.2':
		case '1.100.1.6.3':
		case '1.100.1.6.4':
		case '1.100.1.6.5':	$template_file = 'map_2floor.html'; break;
		// ----------

		//
		// Залы
		//
		case '1.100.1.8.1':
		case '1.100.1.8.2':
		case '1.100.1.8.3':
		case '1.100.1.8.4':
		case '1.100.1.8.5':
		case '1.100.1.8.6':	$template_file = 'map_halls.html'; break;
		// ----------
	}

	//
	// Получаем данные онлайн-персонажей
	//
	$sql = "SELECT user_room FROM " . USERS_TABLE . " WHERE `user_session_time` > " . ( time() - $config['load_online_time'] ) . " OR `user_bot` = 1";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажей...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	$room = array();

	if( $template_file == 'map_1floor.html' )
	{
		// Обнуляем кол-во персонажей в комнатах
		$room['1.100.1.1'] = 0;
		$room['1.100.1.5'] = 0;
		$room['1.100.1.9'] = 0;
		$room['1.100.1.10'] = 0;
		$room['1.100.1.11'] = 0;
		$room['1.100.1.12'] = 0;
		$room['1.100.1.13'] = 0;
	}
	elseif( $template_file == 'map_2floor.html' )
	{
		// Обнуляем кол-во персонажей в комнатах
		$room['1.100.1.6.1'] = 0;
		$room['1.100.1.6.2'] = 0;
		$room['1.100.1.6.3'] = 0;
		$room['1.100.1.6.4'] = 0;
		$room['1.100.1.6.5'] = 0;
	}
	elseif( $template_file == 'map_halls.html' )
	{
		// Обнуляем кол-во персонажей в комнатах
		$room['1.100.1.8.1'] = 0;
		$room['1.100.1.8.2'] = 0;
		$room['1.100.1.8.3'] = 0;
		$room['1.100.1.8.4'] = 0;
		$room['1.100.1.8.5'] = 0;
		$room['1.100.1.8.6'] = 0;
	}

	while( $row = $db->sql_fetchrow($result) )
	{
		if( !isset($room[$row['user_room']]) )
		{
			$room[$row['user_room']] = '';
		}

		$room[$row['user_room']]++;
	}

	site_header();

	$template->set_filenames(array(
		'body'	=> $template_file)
	);

	// Текущая комната
	$current_room = str_replace('.', '_', $userdata['user_room']);

	if( $template_file == 'map_1floor.html' )
	{
		$template->assign_vars(array(
			'1_100_1_1'					=> $room['1.100.1.1'],		// Комната для новичков
			'1_100_1_5'					=> $room['1.100.1.5'],		// Комната перехода
			'1_100_1_9'					=> $room['1.100.1.9'],		// Центральная площадь
			'1_100_1_10'				=> $room['1.100.1.10'],		// Зал воинов
			'1_100_1_11'				=> $room['1.100.1.11'],		// Зал воинов 2
			'1_100_1_12'				=> $room['1.100.1.12'],		// Зал воинов 3
			'1_100_1_13'				=> $room['1.100.1.13'],		// Будуар
			'FLAG_' . $current_room		=> '<img src="i/flag2.gif" width="20" height="20" alt="Вы находитесь здесь" align="right">')
		);
	}
	elseif( $template_file == 'map_2floor.html' )
	{
		$template->assign_vars(array(
			'1_100_1_6_1'				=> $room['1.100.1.6.1'],	// Рыцарский зал
			'1_100_1_6_2'				=> $room['1.100.1.6.2'],	// Торговый зал
			'1_100_1_6_3'				=> $room['1.100.1.6.3'],	// Башня рыцарей-магов
			'1_100_1_6_4'				=> $room['1.100.1.6.4'],	// Комната Знахаря
			'1_100_1_6_5'				=> $room['1.100.1.6.5'],	// Второй этаж
			'FLAG_' . $current_room		=> '<img src="i/flag2.gif" width="20" height="20" alt="Вы находитесь здесь" align="right">')
		);
	}
	elseif( $template_file == 'map_halls.html' )
	{
		$template->assign_vars(array(
			'1_100_1_8_1'				=> $room['1.100.1.8.1'],	// Зал паладинов
			'1_100_1_8_2'				=> $room['1.100.1.8.2'],	// Совет белого братства
			'1_100_1_8_3'				=> $room['1.100.1.8.3'],	// Зал тьмы
			'1_100_1_8_4'				=> $room['1.100.1.8.4'],	// Царство тьмы
			'1_100_1_8_5'				=> $room['1.100.1.8.5'],	// Зал стихий
			'1_100_1_8_6'				=> $room['1.100.1.8.6'],	// Залы
			'FLAG_' . $current_room		=> '<img src="i/flag2.gif" width="20" height="20" alt="Вы находитесь здесь" align="right">')
		);
	}

	$template->pparse('body');

	site_bottom();
	//
	// ---------------
}
elseif( $userdata['user_level'] >= 4 && !$setcancel && ( $findlogin || $setkredit > 0 || $to_id > 0 ) )
{
	// ---------------
	// Передача предметов/кредитов
	//
	if( $findlogin )
	{
		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_login, user_room, user_align, user_klan, user_level FROM " . USERS_TABLE . " WHERE `user_login` = '" . $findlogin . "'";
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
		elseif( $row['user_level'] < 4 )
		{
			$message = 'К персонажам до 4-го уровня передачи предметов запрещены';
		}
		elseif( $userdata['user_login'] == $findlogin )
		{
			$message = 'И что вы хотите себе передать? ;)';
		}
		elseif( ( $userdata['user_align'] >= 2 && $userdata['user_align'] < 3 ) || ( $row['user_align'] >= 2 && $row['user_align'] < 3 ) )
		{
			$message = 'Хаосникам запрещено что-либо передавать';
		}
		// ----------

		if( !$message )
		{
			$message = ( $userdata['user_room'] != $row['user_room'] ) ? 'Вы не можете что-либо передать к "' . $row['user_login'] . '", т.к. он находится в другой комнате' : $message;
			$tologin = $row['user_login'];
			$to_id = $row['user_id'];
		}
		else
		{
			$tologin = '';
		}
	}
	elseif( $to_id > 0 )
	{
		//
		// Получаем данные персонажа
		//
		$sql = "SELECT user_id, user_login, user_room, user_align, user_klan, user_level FROM " . USERS_TABLE . " WHERE `user_id` = " . $to_id;
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
		elseif( $row['user_level'] < 4 )
		{
			$message = 'К персонажам до 4-го уровня передачи предметов запрещены';
		}
		elseif( $userdata['user_login'] == $row['user_login'] )
		{
			$message = 'И что вы хотите себе передать? ;)';
		}
		elseif( ( $userdata['user_align'] >= 2 && $userdata['user_align'] < 3 ) || ( $row['user_align'] >= 2 && $row['user_align'] < 3 ) )
		{
			$message = 'Хаосникам запрещено что-либо передавать';
		}
		// ----------

		if( !$message )
		{
			$message = ( $userdata['user_room'] != $row['user_room'] ) ? '<font color="red"><b>Вы не можете что-либо передать к "' . $row['user_login'] . '", т.к. он находится в другой комнате</b></font>' : $message;
			$tologin = $row['user_login'];
		}
		else
		{
			$tologin = '';
		}
	}
	else
	{
		$tologin = '';
	}

	// Загружает функции
	include($root_path . 'includes/user_transfer.php');

	if( !$message && $setkredit > 0 && $to_id > 0 )
	{
		// Перевод денег другому персонажу
		$message = $user->transfer_cr($to_id, $setkredit);
	}
	elseif( !$message && $setobject > 0 && $to_id > 0 )
	{
		// Передача предмета другому персонажу
		$message = $user->transfer_item($to_id, $setobject, $podarok);
	}

	site_header();

	$template->set_filenames(array(
		'body' => 'main_transfer.html')
	);

	if( $tologin == '' )
	{
		$template->assign_block_vars('before_transfer', array());
	}
	else
	{
		if( $setedit == 1 || $setedit == 2 || $setedit == 3 || $setedit == 4 )
		{
			if( $setedit != $userdata['user_main_edit'] )
			{
				$user->set_cookie('edit', $setedit, ( time() + 3600 ));

				$userdata['user_main_edit'] = $setedit;
			}
		}

		// Получаем данные вещей
		$items = array();
		$user->obtain_items($items, $userdata, 'transfer', ( ( isset($_COOKIE['edit']) && !isset($_GET['setedit']) ) ? $_COOKIE['edit'] : $setedit ));

		$template->assign_block_vars('after_transfer', array(
			'DRWFL'				=> $user->drwfl($row))
		);

		$template->assign_vars(array(
			'OVERALL_MASS'		=> $items['weight'],
			'MAX_MASS'			=> ( $userdata['user_strength'] * 4 ) + $items['plus_weight'],
			'ITEMS'				=> $items['count'])
		);
	}

	$template->assign_vars(array(
		'BUTTON_TO_ID'			=> ( $to_id > 0 ) ? '<input type="hidden" name="to_id" value="' . $to_id . '">' : '',
		'MESSAGE'				=> ( !$tologin && !$message ) ? '<font color="red"><b>К персонажам до 4-го уровня передачи предметов запрещены</b></font>' : ( ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : $message ),
		'MONEY'					=> ( ( $setkredit > 0 && $to_id > 0 ) || ( $setobject > 0 && $to_id > 0 ) ) ? $userdata['user_money'] : $user->int_money($userdata['user_money']),
		'TOLOGIN'				=> $tologin,
		'TO_ID'					=> $to_id)
	);

	$template->pparse('body');

	site_bottom();
	//
	// ---------------
}
elseif( $friendadd || $friendremove || $friends )
{
	// ---------------
	// Друзья
	//
	if( $friendadd )
	{
		//
		// Добавление друзей в список
		//
		$sql = "SELECT friend_id FROM " . FRIENDS_TABLE . " WHERE `friend_user_id` = " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные друзей...', '', __LINE__, __FILE__, $sql);
		}

		while( $row = $db->sql_fetchrow($result) )
		{
			$friends[] = $row['friend_id'];
		}

		// Проверяем количество
		if( count($friends) < ( 20 + $userdata['user_increase_friends'] ) )
		{
			// Получаем ID-номер добавляемого друга/подруги :)
			$sql = "SELECT user_id FROM " . USERS_TABLE . " WHERE `user_login` = '" . $friendadd . "'";
			if( !$result = $db->sql_query($sql) )
			{
				site_message('Не могу получить ID-номер друга...', '', __LINE__, __FILE__, $sql);
			}

			$row = $db->sql_fetchrow($result);

			// Проверяем существование персонажа
			if( !$row )
			{
				$message = 'Персонаж <b>' . $friendadd . '</b> не найден.';
			}
			elseif( count($friends) > 1 && in_array($row['user_id'], $friends) )
			{
				$message = 'Персонаж <b>' . $friendadd . '</b> уже в списке.';
			}
			else
			{
				//
				// Добавляем персонажа в список друзей
				//
				$sql = "INSERT INTO " . FRIENDS_TABLE . " " . $db->sql_build_array('INSERT', array(
					'friend_user_id'	=> $userdata['user_id'],
					'friend_id'			=> $row['user_id']));
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу добавить друга...', '', __LINE__, __FILE__, $sql);
				}
				// ----------

				$message = 'Персонаж <b>' . $friendadd . '</b> добавлен.';
			}
		}
		// ----------
	}
	elseif( $friendremove )
	{
		// Получаем ID-номер удаляемого друга/подруги :)
		$sql = "SELECT user_id FROM " . USERS_TABLE . " WHERE `user_login` = '" . $friendremove . "'";
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить ID-номер друга...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		// Проверяем существование персонажа
		if( !$row )
		{
			$message = 'Персонаж <b>' . $friendremove . '</b> не найден.';
		}
		else
		{
			// Получаем данные друга
			$sql = "SELECT friend_id FROM " . FRIENDS_TABLE . " WHERE `friend_user_id` = " . $userdata['user_id'] . " AND `friend_id` = " . $row['user_id'];
			if( !$result = $db->sql_query($sql) )
			{
				site_message('Не могу получить данные друга...', '', __LINE__, __FILE__, $sql);
			}

			$row = $db->sql_fetchrow($result);

			// Проверяем существование в списке
			if( !$row )
			{
				$message = 'Персонаж <b>' . $friendremove . '</b> не найден в списке.';
			}
			else
			{
				// Удаляем друга из списка
				$sql = "DELETE FROM " . FRIENDS_TABLE . " WHERE `friend_user_id` = " . $userdata['user_id'] . " AND `friend_id` = " . $row['friend_id'];
				if( !$db->sql_query($sql) )
				{
					site_message('Не могу удалить друга из списка...', '', __LINE__, __FILE__, $sql);
				}

				$message = 'Персонаж <b>' . $friendremove . '</b> убран из списка.';
			}
		}
		// ----------
	}

	//
	// Список друзей
	//
	$sql = "SELECT u.user_id, u.user_bot, u.user_login, u.user_town, u.user_session_time, u.user_room, u.user_align, u.user_klan, u.user_level FROM " . FRIENDS_TABLE . " f, " . USERS_TABLE . " u WHERE f.friend_user_id = " . $userdata['user_id'] . " AND f.friend_id = u.user_id";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные друзей...', '', __LINE__, __FILE__, $sql);
	}

	// Определяем переменные
	$friends_offline['login'] = array();
	$friends_offline['string'] = array();
	$friends_offline_string = '';
	$friends_online['login'] = array();
	$friends_online['string'] = array();
	$friends_online_string = '';
	$friends_othertown['login'] = array();
	$friends_othertown['string'] = array();
	$friends_othertown_string = '';

	while( $row = $db->sql_fetchrow($result) )
	{
		if( $userdata['user_town'] != $row['user_town'] )
		{
			// Персонажи, находящиеся в другом городе
			$friends_othertown['login'][] = $row['user_login'];
			$friends_othertown['string'][] = 'w(\'' . $row['user_login'] . '\', \'\', \'\', \'\', \'\', \'\');';
		}
		elseif( $row['user_bot'] || ( ( time() - $row['user_session_time'] ) < $config['load_online_time'] ) )
		{
			// Персонажи, находящиеся в клубе
			$friends_online['login'][] = $row['user_login'];
			$friends_online['string'][] = 'w(\'' . $row['user_login'] . '\', \'' . $row['user_id'] . '\', \'' . $row['user_align'] . '\', \'' . $row['user_klan'] . '\', \'' . $row['user_level'] . '\', \'' . $user->get_room_name($row['user_room']) . '\');';
		}
		elseif( ( time() - $row['user_session_time'] ) >= $config['load_online_time'] )
		{
			// Персонажи, которых сейчас нет в клубе
			$friends_offline['login'][] = $row['user_login'];
			$friends_offline['string'][] = 'w(\'' . $row['user_login'] . '\', \'' . $row['user_id'] . '\', \'' . $row['user_align'] . '\', \'' . $row['user_klan'] . '\', \'' . $row['user_level'] . '\', \'\');';
		}
	}

	// Сортируем массивы
	array_multisort($friends_online['login'], SORT_ASC, SORT_STRING, $friends_online['string']);
	array_multisort($friends_offline['login'], SORT_ASC, SORT_STRING, $friends_offline['string']);
	array_multisort($friends_othertown['login'], SORT_ASC, SORT_STRING, $friends_othertown['string']);

	for( $i = 0; $i < count($friends_online['login']); $i++ )
	{
		$friends_online_string .= $friends_online['string'][$i];
	}

	for( $i = 0; $i < count($friends_offline['login']); $i++ )
	{
		$friends_offline_string .= $friends_offline['string'][$i];
	}

	for( $i = 0; $i < count($friends_othertown['login']); $i++ )
	{
		$friends_othertown_string .= $friends_othertown['string'][$i];
	}

	site_header();

	$template->set_filenames(array(
		'body' => 'main_friends.html')
	);

	$template->assign_vars(array(
		'FRIENDS'		=> $friends_online_string . $friends_offline_string . $friends_othertown_string,
		'MESSAGE'		=> ( $message ) ? '<font color="red">' . $message . '</font>' : '')
	);

	$template->pparse('body');
	
	site_bottom();
	//
	// ---------------
}
elseif( $setimage )
{
	// ---------------
	// Выбор образа
	//
	if( $setimage == 'Образ' )
	{
		// Возвращение HTML-кода с образом
		function show_obraz($img)
		{
			return '<span style="background-color: black"><a href="main.php?setimage=' . $img . '"><img src="i/chars/0/' . $img . '.gif" width="120" height="220" alt="Выбрать этот образ" onmouseover="imover(this)" onmouseout="imout(this)" style=\'filter:Alpha(Opacity="80",FinishOpacity="60",Style="2");\'></a></span>&nbsp;&nbsp;&nbsp;';
		}

		//
		// Страница для выбора
		//
		site_header();

		$template->set_filenames(array(
			'body'	=> 'main_obraz.html')
		);

		$template->assign_vars(array(
			'LOGIN'		=> $userdata['user_login'],
			
			'0'			=> show_obraz('0'),
			'1'			=> show_obraz('1'),
			'2'			=> ( $userdata['user_magic_fire'] >= 5 || $userdata['user_access_level'] == ADMIN ) ? show_obraz('2') : '',
			'3'			=> show_obraz('3'),
			'4'			=> show_obraz('4'),
			'5'			=> show_obraz('5'),
			'6'			=> show_obraz('6'),
			'7'			=> ( $userdata['user_magic_water'] >= 5 || $userdata['user_access_level'] == ADMIN ) ? show_obraz('7') : '',
			'8'			=> show_obraz('8'),
			'9'			=> show_obraz('9'),
			'10'		=> show_obraz('10'),
			'11'		=> show_obraz('11'),
			'12'		=> show_obraz('12'),
			'13'		=> show_obraz('13'),
			'14'		=> ( $userdata['user_magic_earth'] >= 5 || $userdata['user_access_level'] == ADMIN ) ? show_obraz('14') : '',
			'15'		=> show_obraz('15'),
			'16'		=> show_obraz('16'),
			'17'		=> show_obraz('17'),
			'18'		=> ( $userdata['user_magic_air'] >= 5 || $userdata['user_access_level'] == ADMIN ) ? show_obraz('18') : '',
			'19'		=> show_obraz('19'),
			'20'		=> show_obraz('20'),
			'21'		=> show_obraz('21'),
			'22'		=> show_obraz('22'),
			'23'		=> show_obraz('23'),
			'24'		=> show_obraz('24'),
			'25'		=> ( $userdata['user_swords'] >= 5 || $userdata['user_access_level'] == ADMIN ) ? show_obraz('25') : '',
			'400'		=> ( $userdata['user_klan'] == 'Mercenaries' || $userdata['user_access_level'] == ADMIN ) ? show_obraz('400') : '',
			'500'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('500') : '',
			'501'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('501') : '',
			'502'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('502') : '',
			'600'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('600') : '',
			'10001'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('10001') : '',
			'10002'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('10002') : '',
			'10003'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('10003') : '',
			'10013'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('10013') : '',
			'10014'		=> ( $userdata['user_access_level'] == ADMIN ) ? show_obraz('10014') : '',
			'10015'		=> ( $userdata['user_align'] == '3.06' || $userdata['user_access_level'] == ADMIN ) ? show_obraz('10015') : '',
			'10016'		=> ( $userdata['user_align'] == '1.6' || $userdata['user_access_level'] == ADMIN ) ? show_obraz('10016') : '',
			'10017'		=> ( $userdata['user_align'] == '1.91' || $userdata['user_access_level'] == ADMIN ) ? show_obraz('10017') : '')
		);

		$template->pparse('body');

		site_bottom();
		//
		// ---------------
	}
	elseif( $setimage >= 0 && $setimage <= 24 )
	{
		if( $userdata['user_allow_change_obraz'] || $userdata['user_access_level'] == ADMIN )
		{
			//
			// Обновляем образ
			//
			$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
				'user_allow_change_obraz'	=> ( $userdata['user_access_level'] == ADMIN ) ? 1 : 0,
				'user_obraz'				=> $setimage)) . " WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу поставить образ...', '', __LINE__, __FILE__, $sql);
			}
			// ----------
		}

		redirect(append_sid($root_path . 'main.php'));
	}
	//
	// ---------------
}
elseif( $path )
{
	// ---------------
	// Переход по комнатам
	//
	$message = $user->path($path);
	//
	// ---------------
}
elseif( $editanketa )
{
	// ---------------
	// Редактирование анкеты
	//
	if( $saveanketa )
	{
		//
		// Определяем переменные
		//
		$chat_colour	= ( $_POST['ChatColor'] ) ? $_POST['ChatColor'] : 'black';
		$name			= ( $_POST['name'] ) ? trim($_POST['name']) : NULL;
		$city			= ( $_POST['city'] ) ? trim($_POST['city']) : NULL;
		$icq			= ( $_POST['icq'] ) ? $_POST['icq'] : NULL;
		$homepage		= ( $_POST['homepage'] ) ? trim($_POST['homepage']) : NULL;
		$hobby			= ( $_POST['hobby'] ) ? trim($_POST['hobby']) : NULL;
		$motto			= ( $_POST['motto'] ) ? trim($_POST['motto']) : NULL;
		// ----------

		//
		// Проверяем ICQ
		//
		if( !preg_match('#^[0-9]+$#', $icq))
		{
			$icq = NULL;
		}
		// ----------

		//
		// Проверяем домашнюю страничку
		//
		if( $homepage )
		{
			if( !preg_match('#^http[s]?:\/\/#i', $homepage) )
			{
				$homepage = 'http://' . $homepage;
			}

			if( !preg_match('#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i', $homepage) )
			{
				$homepage = '';
			}
		}
		// ----------

		//
		// Обновляем данные
		//
		$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', array(
			'user_chat_colour'		=> $chat_colour,
			'user_name'				=> $name,
			'user_city'				=> $city,
			'user_icq'				=> $icq,
			'user_homepage'			=> $homepage,
			'user_hobby'			=> $hobby,
			'user_motto'			=> $motto)) . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		// Обновляем переменные
		$userdata['user_chat_colour']	= $chat_colour;
		$userdata['user_name']			= $name;
		$userdata['user_city']			= $city;
		$userdata['user_icq']			= $icq;
		$userdata['user_homepage']		= $homepage;
		$userdata['user_hobby']			= $hobby;
		$userdata['user_motto']			= $motto;

		$message = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="red">Все прошло удачно...</font>';
	}

	site_header();

	$template->set_filenames(array(
		'body' => 'main_editanketa.html')
	);

	$template->assign_vars(array(
		'CHAT_COLOUR'		=> $userdata['user_chat_colour'],
		'CITY'				=> $userdata['user_city'],
		'HOBBY'				=> $userdata['user_hobby'],
		'HOMEPAGE'			=> $userdata['user_homepage'],
		'LOGIN'				=> $userdata['user_login'],
		'MESSAGE'			=> $message,
		'MOTTO'				=> $userdata['user_motto'],
		'NAME'				=> $userdata['user_name'])
	);

	$template->pparse('body');

	site_bottom();
	//
	// ---------------
}
elseif( $clear_abil || $set_abil || $set_special || $skills || $upr )
{
	// ---------------
	// Распределение способностей
	//
	$user->obtain_status($userdata, '', 'skills');
	include($root_path . 'includes/main_skills.php');
	exit;
	//
	// ---------------
}

// ---------------
// Центральная площадь
//
if( $userdata['user_room'] == '1.100' || $userdata['user_room'] == '1.107' || $userdata['user_room'] == '1.120' )
{
	// Выходим из банка
	if( isset($_COOKIE['in_bank']) )
	{
		$user->set_cookie('in_bank', '', ( time() - 31536000 ));
	}
	
	// Выходим из магазина
	if( isset($_COOKIE['shop_otdel']) )
	{
		$user->set_cookie('shop_otdel', '', ( time() - 31536000 ));
	}

	// Отображаем ссылки
	$links = $user->get_room_links($userdata['user_room']);
	$user->links_display($links);

	// Отображаем персонажа
	$user->show_character($userdata, 'main');
	
	// Выбираем шаблон
	switch( $userdata['user_room'] )
	{
		case '1.100':	$template_file = 'city_central.html'; break;
		case '1.107':	$template_file = 'city_strash.html'; break;
		case '1.120':	$template_file = 'city_big_trader.html'; break;
	}

	site_header();

	$template->set_filenames(array(
		'body'	=> $template_file)
	);

	$template->assign_vars(array(
		'GOING_TIME'				=> $user->get_going_time(),
		'MESSAGE'					=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '',
		'META'						=> ( $config['fast_game'] && $userdata['user_bot'] && $userdata['user_level'] < $config['fast_game_level'] ) ? '<meta http-equiv="refresh" content="5;url=main.php?start_bot_battle">' : '',
		'NIGHT'						=> ( date("H") >= 22 || date("H") <= 6 ) ? 'night/' : '',
		'STAR'						=> ( date("H") >= 22 || date("H") <= 6 ) ? '<div style="position: absolute; left: 104px; top: 17px; width: 12px; height: 17px; z-index: 90; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="i/images/subimages/star.gif" width="12" height="17" alt="" onclick=""></div>' : '',
		'STAR2'						=> ( date("H") >= 22 || date("H") <= 6 ) ? '<div style="position: absolute; left: 334px; top: 50px; width: 12px; height: 17px; z-index: 90; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="i/images/subimages/star2.gif" width="12" height="17" alt="" onclick=""></div>' : '')
	);

	$template->pparse('body');

	site_bottom();
}
elseif( $userdata['user_room'] == '1.100.1.101' )
{
	redirect(append_sid($root_path . 'repair.php'));
}
elseif( $userdata['user_room'] == '1.100.1.102' )
{
	redirect(append_sid($root_path . 'shop.php'));
}
elseif( $userdata['user_room'] == '1.100.1.110' )
{
	redirect(append_sid($root_path . 'bank.php'));
}
//
// ---------------

/*
$arrow[1] = '<img src="i/move/navigatin_55i.gif" width="22" height="20">';
$arrow[2] = '<img src="i/move/navigatin_59i.gif" width="21" height="20">';
$arrow[3] = '<img src="i/move/navigatin_64i.gif" width="20" height="21">';
$arrow[4] = '<img src="i/move/navigatin_52i.gif" width="19" height="22">';
$arrow[5] = '<img src="i/move/navigatin_67i.gif" width="19" height="22">';
$arrow[6] = '<img src="i/move/navigatin_56i.gif" width="21" height="20">';
$arrow[7] = '<img src="i/move/navigatin_62i.gif" width="22" height="21">';
$arrow[8] = '<img src="i/move/navigatin_65i.gif" width="21" height="20">';
$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow2(\'<strong>Обновить</strong>\')" onmouseout="hideshow()"></a>';

//
// Комната и описание
//
switch( $userdata['user_room'] )
{
	case '1.100.1.1':
		// ---------------
		// Комната для новичков
		//
		$images['navigatin'][67] = ( $userdata['user_previous_room'] == '1.100.1.8.1' ) ? 'navigatin_67b' : 'navigatin_67';
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.5' ) ? 'navigatin_59b' : 'navigatin_59';

		$arrow[5] = ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? '<a onclick="check(\'m6\');" id="m6" href="main.php?path=m6"><img src="i/move/' . $images['navigatin'][67] . '.gif" width="19" height="22" onmousemove="fastshow(\'Зал Паладинов\');" onmouseout="hideshow();"></a>' : $arrow[5];
		$arrow[2] = ( $userdata['user_level'] >= 1 ) ? '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Комната Перехода\');" onmouseout="hideshow();"></a>' : $arrow[2];
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />' . ( ( $userdata['user_level'] >= 1 ) ? 'Переходы:' : '' ) . ( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) ? '<br />Зал Паладинов' : '' ) .  ( ( $userdata['user_level'] >= 1 ) ? '<br />Комната Перехода' : '' ) . '\')" onmouseout="hideshow()"></a>';

		$room['desc'] = ( $userdata['user_stats'] == 15 && $userdata['user_free_upr'] > 0 ) ? 'Бойцовский Клуб приветствует Вас.<br>Чтобы сражаться с остальными на равных, вам нужно распределить начальные характеристики.<br>Для этого нажмите на <b>Способности</b>, а затем, нажимая на <img src="i/up.gif">, сформируйте своего персонажа.<br>Подробнее о значении характеристик можно узнать в <b>Библиотеке</b>.<br>Распределив все характеристики, нажмите на кнопку <b>Вернуться</b>.<br>Для проведения боя нажмите на кнопку <b>Поединки</b>.<br>Выберите раздел "<b>Бои Новичков</b>".<br>Более подробно о поединках можно прочитать в <b>Библиотеке</b>.' : 'Бойцовский Клуб приветствует Вас.<br>Для проведения боя нажмите на кнопку <b>Поединки</b>.<br>Выберите раздел "<b>Бои Новичков</b>".<br>Более подробно о поединках можно прочитать в <b>Библиотеке</b>.';
		break;
		//
		// ---------------
	case '1.100.1.5':
		// ---------------
		// Комната перехода
		//
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.10' ) ? 'navigatin_59b' : 'navigatin_59';
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.1' ) ? 'navigatin_62b' : 'navigatin_62';

		$arrow[2] = '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Зал воинов 3\');" onmouseout="hideshow();"></a>';
		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="main.php?path=m3"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Комната для новичков\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Комната для новичков<br />Зал Воинов 2\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Если вы пришли в эту комнату, значит уже "выросли из этих ползунков". Выйдя отсюда однажды, вы более не сможете вернуться.';
		break;
		//
		// ---------------
	case '1.100.1.6.1':
		// ---------------
		// Рыцарский зал
		//
		$images['navigatin'][52] = ( $userdata['user_previous_room'] == '1.100.1.6.3' ) ? 'navigatin_52b' : 'navigatin_52';
		$images['navigatin'][67] = ( $userdata['user_previous_room'] == '1.100.1.6.5' ) ? 'navigatin_67b' : 'navigatin_67';

		$arrow[4] = '<a onclick="check(\'m1\');" id="m1" href="main.php?path=m1"><img src="i/move/' . $images['navigatin'][52] . '.gif" width="19" height="22" onmousemove="fastshow(\'Башня рыцарей-магов\');" onmouseout="hideshow();"></a>';
		$arrow[5] = '<a onclick="check(\'m5\');" id="m5" href="main.php?path=m5"><img src="i/move/' . $images['navigatin'][67] . '.gif" width="19" height="22" onmousemove="fastshow(\'Этаж 2\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Башня рыцарей-магов<br />Этаж 2\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Вы уже не новичок. Вы уже боец. И не просто боец, а Боец с большой буквы. Осталось объяснить это вооон тому артнику...';
		break;
		//
		// ---------------
	case '1.100.1.6.2':
		// ---------------
		// Торговый зал
		//
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.6.5' ) ? 'navigatin_59b' : 'navigatin_59';
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.6.4' ) ? 'navigatin_62b' : 'navigatin_62';

		$arrow[2] = '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Этаж 2\');" onmouseout="hideshow();"></a>';
		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="main.php?path=m3"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Комната Знахаря\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Комната Знахаря<br />Этаж 2\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Ищете лекаря? Ваш доспех вам жмет и вы хотите другой? Нужен умелый наемник? Вы попали по адресу. Именно здесь можно купить или продать любой товар или услугу. Здешние умельцы и оденут и обуют вас в один момент.<br><small>Орден света предупреждает - настоящий воин должен быть немногословным.</small>';
		break;
		//
		// ---------------
	case '1.100.1.6.3':
		// ---------------
		// Башня рыцарей-магов
		//
		$images['navigatin'][67] = ( $userdata['user_previous_room'] == '1.100.1.6.1' ) ? 'navigatin_67b' : 'navigatin_67';

		$arrow[5] = '<a onclick="check(\'m5\');" id="m5" href="main.php?path=m5"><img src="i/move/' . $images['navigatin'][67] . '.gif" width="19" height="22" onmousemove="fastshow(\'Рыцарский зал\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Рыцарский зал\')" onmouseout="hideshow()"></a>';

		$room['desc'] = '';
		break;
		//
		// ---------------
	case '1.100.1.6.5':
		// ---------------
		// Этаж 2
		//
		$images['navigatin'][52] = ( $userdata['user_previous_room'] == '1.100.1.6.1' ) ? 'navigatin_52b' : 'navigatin_52';
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.6.3' ) ? 'navigatin_62b' : 'navigatin_62';

		$arrow[4] = '<a onclick="check(\'m1\');" id="m1" href="' . append_sid($root_path . 'main.php?path=m1') . '"><img src="i/move/' . $images['navigatin'][52] . '.gif" width="19" height="22" onmousemove="fastshow(\'Рыцарский зал\');" onmouseout="hideshow();"></a>';
		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="' . append_sid($root_path . 'main.php?path=m3') . '"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Торговый Зал\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="' . append_sid($root_path . 'main.php') . '"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Рыцарский зал<br />Торговый Зал\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Хотите попасть в Торговый Зал или комнату Знахаря? Вам направо. Хотите попасть в Рыцарский Зал? Вам прямо. Хотите попасть под раздачу? Стойте здесь и мешайтесь движению.';
		break;
		//
		// ---------------
	case '1.100.1.7.5':
		// ---------------
		// Этаж 3
		//
		$room['desc'] = 'Вы находитесь на самом верхнем этаже здания Бойцовского Клуба.';
		break;
		//
		// ---------------
	case '1.100.1.8.1':
		// ---------------
		// Зал паладинов
		//
		$images['navigatin'][52] = ( $userdata['user_previous_room'] == '1.100.1.1' ) ? 'navigatin_52b' : 'navigatin_52';
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.8.2' ) ? 'navigatin_62b' : 'navigatin_62';
		$images['navigatin'][64] = ( $userdata['user_previous_room'] == '1.100.1.8.6' ) ? 'navigatin_64b' : 'navigatin_64';

		$arrow[3] = '<a onclick="check(\'m6\');" id="m6" href="main.php?path=m6"><img src="i/move/' . $images['navigatin'][64] . '.gif" width="20" height="21" onmousemove="fastshow(\'Залы\');" onmouseout="hideshow();"></a>';
		$arrow[4] = '<a onclick="check(\'m1\');" id="m1" href="main.php?path=m1"><img src="i/move/' . $images['navigatin'][52] . '.gif" width="19" height="22" onmousemove="fastshow(\'Комната для новичков\');" onmouseout="hideshow();"></a>';
		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="main.php?path=m3"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Совет Белого Братства\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Комната для новичков<br />Совет Белого Братства<br />Залы\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Хорошо паладинам в этой небольшой и уютной комнатке. Здесь всегда не так много народу, поэтому никто не мешает выполнять свои обязанности, что-либо обсуждать. Практически идеальная комната для работы, если бы только не эти ньюбы...';
		break;
		//
		// ---------------
	case '1.100.1.8.2':
		// ---------------
		// Совет белого братства
		//
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.8.1' ) ? 'navigatin_59b' : 'navigatin_59';

		$arrow[2] = '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Зал Паладинов\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Зал Паладинов\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Тишина окутывает здешние углы, но только немногочисленные паладины, вошедшие сюда, сменяют её своими разговорами.';
		break;
		//
		// ---------------
	case '1.100.1.8.3':
		// ---------------
		// Зал тьмы
		//
		$images['navigatin'][56] = ( $userdata['user_previous_room'] == '1.100.1.8.6' ) ? 'navigatin_56b' : 'navigatin_56';

		$arrow[6] = '<a onclick="check(\'m2\');" id="m2" href="main.php?path=m2"><img src="i/move/' . $images['navigatin'][56] . '.gif" width="21" height="20" onmousemove="fastshow(\'Залы\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Залы\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'В этой комнате настолько темно, что находится в ней могут действительно только истинные темные.';
		break;
		//
		// ---------------
	case '1.100.1.8.5':
		// ---------------
		// Зал стихий
		//
		$images['navigatin'][55] = ( $userdata['user_previous_room'] == '1.100.1.8.6' ) ? 'navigatin_55b' : 'navigatin_55';

		$arrow[1] = '<a onclick="check(\'m8\');" id="m8" href="' . append_sid($root_path . 'main.php?path=m8') . '"><img src="i/move/' . $images['navigatin'][55] . '.gif" width="22" height="20" onmousemove="fastshow(\'Залы\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="' . append_sid($root_path . 'main.php') . '"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Залы\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'Уютная комнатка для нейтралов. Здесь можно спокойно пополнить силы после тяжелого боя.';
		break;
		//
		// ---------------
	case '1.100.1.8.6':
		// ---------------
		// Залы
		//
		$images['navigatin'][56] = ( $userdata['user_previous_room'] == '1.100.1.8.1' ) ? 'navigatin_56b' : 'navigatin_56';
		$images['navigatin'][64] = ( $userdata['user_previous_room'] == '1.100.1.8.3' ) ? 'navigatin_64b' : 'navigatin_64';
		$images['navigatin'][65] = ( $userdata['user_previous_room'] == '1.100.1.8.5' ) ? 'navigatin_65b' : 'navigatin_65';

		$arrow[3] = '<a onclick="check(\'m6\');" id="m6" href="main.php?path=m6"><img src="i/move/' . $images['navigatin'][64] . '.gif" width="20" height="21" onmousemove="fastshow(\'Зал Тьмы\');" onmouseout="hideshow();"></a>';
		$arrow[6] = '<a onclick="check(\'m2\');" id="m2" href="main.php?path=m2"><img src="i/move/' . $images['navigatin'][56] . '.gif" width="21" height="20" onmousemove="fastshow(\'Зал Паладинов\');" onmouseout="hideshow();"></a>';
		$arrow[8] = '<a onclick="check(\'m4\');" id="m4" href="main.php?path=m4"><img src="i/move/' . $images['navigatin'][65] . '.gif" width="21" height="20" onmousemove="fastshow(\'Зал Стихий\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Зал Паладинов<br />Зал Стихий<br />Зал Тьмы\')" onmouseout="hideshow()"></a>';

		$room['desc'] = 'И суровый паладин, и коварный служитель Тьмы, и равнодушный нейтрал - все вынуждены встречаться на этом пятачке ничейной земли, перед тем как разойтись по своим залам. И только искреннее дружелюбие бойцов разных склонностей помогает избежать бойни в этом месте. А также отсутствие бесплатных нападений.';
		break;
		//
		// ---------------
	case '1.100.1.9':
		// ---------------
		// Бойцовский Клуб
		//
		$images['navigatin'][52] = ( $userdata['user_previous_room'] == '1.100.1.11' ) ? 'navigatin_52b' : 'navigatin_52';
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.10' ) ? 'navigatin_59b' : 'navigatin_59';
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.12' ) ? 'navigatin_62b' : 'navigatin_62';
		$images['navigatin'][67] = ( $userdata['user_previous_room'] == '1.100.1.13' ) ? 'navigatin_67b' : 'navigatin_67';

		$arrow[2] = '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Зал воинов\');" onmouseout="hideshow();"></a>';
		$arrow[4] = '<a onclick="check(\'m1\');" id="m1" href="main.php?path=m1"><img src="i/move/' . $images['navigatin'][52] . '.gif" width="19" height="22" onmousemove="fastshow(\'Зал воинов 2\');" onmouseout="hideshow();"></a>';
		$arrow[5] = '<a onclick="check(\'m5\');" id="m5" href="main.php?path=m5"><img src="i/move/' . $images['navigatin'][67] . '.gif" width="19" height="22" onmousemove="fastshow(\'Будуар\');" onmouseout="hideshow();"></a>';
		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="main.php?path=m3"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Зал воинов 3\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Зал воинов<br />Зал воинов 2<br />Зал воинов 3<br />Будуар\')" onmouseout="hideshow()"></a>';

		$room['desc'] = ( $userdata['user_level'] >= 1 && $userdata['user_level'] <= 3 ) ? 'Вы добились определенных успехов и теперь залы Воинов станут вашим временным пристанищем. Помимо них, справа находится Будуар, но вы вряд ли сможете в него попасть...' : 'Пожалуй, только Будуар на этом этаже мог бы заинтересовать вас, но к сожалению вход в него вам закрыт. Помимо этого, можно посетить любой из залов Воинов, но что делать опытному бойцу среди новичков?';
		break;
		//
		// ---------------
	case '1.100.1.10':
		// ---------------
		// Зал воинов
		//
		$images['navigatin'][62] = ( $userdata['user_previous_room'] == '1.100.1.9' ) ? 'navigatin_62b' : 'navigatin_62';

		$arrow[7] = '<a onclick="check(\'m3\');" id="m3" href="main.php?path=m3"><img src="i/move/' . $images['navigatin'][62] . '.gif" width="22" height="21" onmousemove="fastshow(\'Бойцовский Клуб\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Бойцовский Клуб\')" onmouseout="hideshow()"></a>';

		$room['desc'] = ( $userdata['user_level'] >= 1 && $userdata['user_level'] <= 3 ) ? 'Вы уже знаете для чего вам кулаки и достигли некоторых успехов в Клубе. Зал Воинов приветствует вас и ждет ваших боев. Какими бы они ни были.' : 'Возможно, вы ошиблись этажом - настоящие сражения проходят выше.';
		break;
		//
		// ---------------
	case '1.100.1.11':
		// ---------------
		// Зал воинов 2
		//
		$images['navigatin'][67] = ( $userdata['user_previous_room'] == '1.100.1.9' ) ? 'navigatin_67b' : 'navigatin_67';

		$arrow[5] = '<a onclick="check(\'m5\');" id="m5" href="main.php?path=m5"><img src="i/move/' . $images['navigatin'][67] . '.gif" width="19" height="22" onmousemove="fastshow(\'Бойцовский Клуб\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Бойцовский Клуб\')" onmouseout="hideshow()"></a>';

		$room['desc'] = ( $userdata['user_level'] >= 1 && $userdata['user_level'] <= 3 ) ? 'Вы уже знаете для чего вам кулаки и достигли некоторых успехов в Клубе. Зал Воинов приветствует вас и ждет ваших боев. Какими бы они ни были.' : 'Возможно, вы ошиблись этажом - настоящие сражения проходят выше.';
		break;
		//
		// ---------------
	case '1.100.1.12':
		// ---------------
		// Зал воинов 3
		//
		$images['navigatin'][59] = ( $userdata['user_previous_room'] == '1.100.1.9' ) ? 'navigatin_59b' : 'navigatin_59';

		$arrow[2] = '<a onclick="check(\'m7\');" id="m7" href="main.php?path=m7"><img src="i/move/' . $images['navigatin'][59] . '.gif" width="21" height="20" onmousemove="fastshow(\'Бойцовский Клуб\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Бойцовский Клуб\')" onmouseout="hideshow()"></a>';

		$room['desc'] = ( $userdata['user_level'] >= 1 && $userdata['user_level'] <= 3 ) ? 'Вы уже знаете для чего вам кулаки и достигли некоторых успехов в Клубе. Зал Воинов приветствует вас и ждет ваших боев. Какими бы они ни были.' : 'Возможно, вы ошиблись этажом - настоящие сражения проходят выше.';
		break;
		//
		// ---------------
	case '1.100.1.13':
		// ---------------
		// Будуар
		//
		$images['navigatin'][52] = ( $userdata['user_previous_room'] == '1.100.1.9' ) ? 'navigatin_52b' : 'navigatin_52';

		$arrow[4] = '<a onclick="check(\'m1\');" id="m1" href="main.php?path=m1"><img src="i/move/' . $images['navigatin'][52] . '.gif" width="19" height="22" onmousemove="fastshow(\'Бойцовский Клуб\');" onmouseout="hideshow();"></a>';
		$refresh = '<a href="main.php"><img src="i/move/navigatin_58.gif" width="19" height="33" onmousemove="fastshow(\'<strong>Обновить</strong><br />Переходы:<br />Бойцовский Клуб\')" onmouseout="hideshow()"></a>';

		$room['desc'] = '';
		break;
		//
		// ---------------
}
*/

$links = $user->get_room_links($userdata['user_room']);
$user->links_display($links);
// ----------

// Отображаем персонажа
$user->show_character($userdata, 'main');

include($root_path . 'includes/main_path.php');

site_header();

$template->set_filenames(array(
	'body' => 'main_body.html')
);

$template->assign_vars(array(
	'MESSAGE'				=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '',
	'META'					=> ( $config['fast_game'] && $userdata['user_bot'] && $userdata['user_level'] < $config['fast_game_level'] ) ? '<meta http-equiv="refresh" content="' . $config['fast_game_redirect'] . ';url=main.php?start_bot_battle">' : '',
//	'ROOM_DESC'				=> $room['desc'],
	'ROOM_NAME'				=> $user->get_room_name($userdata['user_room']),
	'ROOMS'					=> $path->room($userdata['user_room']),

//	'ARROW1'				=> $arrow[1],
//	'ARROW2'				=> $arrow[2],
//	'ARROW3'				=> $arrow[3],
//	'ARROW4'				=> $arrow[4],
//	'ARROW5'				=> $arrow[5],
//	'ARROW6'				=> $arrow[6],
//	'ARROW7'				=> $arrow[7],
//	'ARROW8'				=> $arrow[8],
//	'REFRESH'				=> $refresh,
	'GOING_TIME'			=> $user->get_going_time(),

	'BATTLE_BTN'			=> ( $user->check_battle_room() == true ) ? '<input type="button" value="Поединки" class="btn" style="font-weight: bold" onclick="location.href=\'zayavka.php\';">' : '')
);

$template->pparse('body');

site_bottom();

?>