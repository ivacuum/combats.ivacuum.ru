<?php
/***************************************************************************
 *								inf.php									   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: inf.php, v 1.00 2005/11/09 19:13:00 V@cuum Exp $				   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

if( empty($userdata['user_id']) )
{
	$userdata['user_access_level'] = '';
	$userdata['user_bot'] = 0;
	$userdata['user_id'] = ANONYMOUS;
}

// Определяем переменные
$id		= request_var('id', '');
$login	= request_var('login', '');

if( $id != '' && $login == '' )
{
	// Проверяем - является ли ID числом
	if( !preg_match('#^[0-9]+$#', $id) )
	{
		site_message('ID указан неверно...');
	}

	//
	// Получаем данные через ID
	//
	$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_id` = " . $id;
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( !$row || $id <= 0 )
	{
		site_message('Персонаж не найден...');
	}
	// ----------
}
elseif( $login != '' && $id == '' )
{
	//
	// Получаем данные через логин
	//
	$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_login` = '" . $login . "'";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( !$row )
	{
		site_message('Персонаж не найден...');
	}
	// ----------
}
elseif( $id == '' && $login == '' )
{
	site_message('Персонаж не найден...');
}
elseif( $id != '' && $login != '' )
{
	site_message('Персонаж не найден...');
}

//
// Скрытие инфы
//
if( $row['user_redirect'] && $userdata['user_access_level'] != ADMIN && $row['user_id'] != $userdata['user_id'] )
{
	redirect($row['user_redirect']);
}
// ----------

$items = $user->get_equip_items($row, true, 'inf');
$items = $user->obtain_status($row, $items, 'inf');

// Обновляем HP
$row['user_hpspeed'] *= ( $row['user_bot'] && $config['fast_game'] ) ? 250 : ( ( $row['user_bot'] ) ? 10 : 1);

// Увеличиваем скорость пополнения HP (по уровню)
$row['user_hpspeed'] *= ( $row['user_level'] == 0 || $row['user_level'] == 1 ) ? 3 : ( ( $row['user_level'] == 2 || $row['user_level'] == 3 ) ? 2 : 1);
$user->update_hp($row);

if( $userdata['user_access_level'] == ADMIN )
{
	// Получаем данные логов
	$sql = "SELECT * FROM " . LOG_TABLE . " WHERE `log_user_id` = " . $row['user_id'] . " ORDER BY `log_time` DESC";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные...', '', __LINE__, __FILE__, $sql);
	}

	$admin_log = '';
	$n = 0;

	while( $log = $db->sql_fetchrow($result) )
	{
		if( $n < 50 )
		{
			$admin_log .= '<tr><td style="font-size: 11px" valign="top"><font color="green">' . date('d.m.Y H:i', $log['log_time']) . '</font> ' . $log['log_text'] . '</td></tr>';
			$n++;
		}
	}

	$admin_log = ( $admin_log == '' ) ? '<tr><td><font color="red">Информации не поступало</font></td></tr>' : $admin_log;
}

//
// Магические способности
//
$magic_abilities = '';
//$magic_abilities .= ( $row['user_level'] >= 8 ) ? '<img src="http://static.ivacuum.ru/i/items/inv_protection.gif" width="40" height="25" alt="Защита от травм"><br />' : '';
// ----------

site_header();

$template->set_filenames(array(
	'body' => 'inf_body.html')
);

for( $i = 0; $i < $items['flowers_count']; $i++ )
{
	$template->assign_block_vars('flowers', array(
		'DESC'				=> $items['item_gift_desc']['flowers'][$i],
		'FROM'				=> $items['item_gift_from']['flowers'][$i],
		'IMG'				=> $items['item_img']['flowers'][$i])
	);
}

if( $row['user_blocked'] )
{
	$template->assign_block_vars('blocked', array());

	if( $row['user_blocked_reason'] )
	{
		$template->assign_block_vars('paladins_message', array(
			'MESSAGE'		=> $row['user_blocked_reason'])
		);
	}
}

if( $userdata['user_access_level'] == ADMIN )
{
	$template->assign_block_vars('admin_log', array(
		'TEXT'				=> $admin_log)
	);

	//
	// Свитки
	//
	if( $row['user_w100'] || $row['user_w101'] || $row['user_w102'] || $row['user_w103'] || $row['user_w104'] || $row['user_w105'] || $row['user_w106'] || $row['user_w107'] || $row['user_w108'] || $row['user_w109'] )
	{
		$template->assign_block_vars('scrolls', array());

		$template->assign_vars(array(
			'I_SCROLL1'		=> $user->show_item($row, $items, 'w100', 'inf'),
			'I_SCROLL2'		=> $user->show_item($row, $items, 'w101', 'inf'),
			'I_SCROLL3'		=> $user->show_item($row, $items, 'w102', 'inf'),
			'I_SCROLL4'		=> $user->show_item($row, $items, 'w103', 'inf'),
			'I_SCROLL5'		=> $user->show_item($row, $items, 'w104', 'inf'),
			'I_SCROLL6'		=> $user->show_item($row, $items, 'w105', 'inf'),
			'I_SCROLL7'		=> $user->show_item($row, $items, 'w106', 'inf'),
			'I_SCROLL8'		=> $user->show_item($row, $items, 'w107', 'inf'),
			'I_SCROLL9'		=> $user->show_item($row, $items, 'w108', 'inf'),
			'I_SCROLL10'	=> $user->show_item($row, $items, 'w109', 'inf'))
		);
	}
	// ----------
}

$template->assign_vars(array(
	'BATTLE_ID'				=> $row['user_battle_id'],
	'BIRTHDAY'				=> ( $row['user_character_birthday'] > 0 ) ? date('d.m.Y H:i', $row['user_character_birthday']) : 'до начала времен',
	'BIRTHDAY_TOWN'			=> $user->city_name($row['user_birthday_town'], 'text'),
	'CURRENT_HP'			=> ( $row['user_battle_id'] > 0 ) ? intval($row['user_current_hp']) : $row['user_current_hp'],
	'DRWFL'					=> $user->drwfl($row),
	'FLAG'					=> ( $row['user_align'] >= 1 && $row['user_align'] < 2 ) ? '<a target="_blank" onfocus="this.blur()" href="./../index.php"><img src="http://static.ivacuum.ru/i/flag_light.gif"></a>' : ( ( $row['user_align'] >= 3 && $row['user_align'] < 4 ) ? '<a target="_blank" onfocus="this.blur()" href="./../index.php"><img src="http://static.ivacuum.ru/i/flag_dark.gif"></a>' : '<a target="_blank" onfocus="this.blur()" href="./../index.php"><img src="http://static.ivacuum.ru/i/flag_gray.gif"></a>'),
	'GENDER_NUM'			=> ( $row['user_gender'] == 'Мужской' ) ? 0 : 1,
	'HPSPEED'				=> $row['user_hpspeed'],
	'IN_FIGHT'				=> ( $row['user_battle_id'] > 0 ) ? $template->assign_block_vars('in_fight', array()) : '',
	'LEVEL'					=> $row['user_level'],
	'LOGIN'					=> $row['user_login'],
	'MAGIC_ABILITIES'		=> ( $magic_abilities ) ? 'Магические способности:<br />' . $magic_abilities : '',
	'MAX_HP'				=> $row['user_max_hp'],
	'OBRAZ'					=> $row['user_obraz'],
	'ONLINE'				=> ( ( time() - $row['user_session_time'] ) < $config['load_online_time'] || $row['user_bot'] == 1 ) ? 'Персонаж сейчас находится в клубе.' : 'Персонаж не в клубе или у него выключен чат.<br />',
	'ROOM'					=> ( ( time() - $row['user_session_time'] ) < $config['load_online_time'] || $row['user_bot'] == 1 ) ? '<div align="center"><b>"' . $user->get_room_name($row['user_room']) . '"</b></div>' : '',
	'TOWN'					=> $user->city_name($row['user_town'], 'text'),
	'VIP'					=> ( $row['user_vip'] ) ? '<img src="http://static.ivacuum.ru/i/vip2.gif" width="35" height="26" alt="VIP"> <b>VIP Клуб БК</b><br />' : '',
	'ZODIAC'				=> $row['user_zodiac'],
	'ZVANIE'				=> ( $row['user_align'] >= 1 && $row['user_align'] < 2 ) ? '<b>Паладинский орден</b> - ' . $row['user_zvanie'] . '<br />' : ( ( $row['user_klan'] ) ? '<b>' . $row['user_klan'] . '</b> - ' . $row['user_zvanie'] . '<br />' : ( ( $row['user_align'] > 3 && $row['user_align'] < 4 ) ? '<b>Армада</b> - ' . $row['user_zvanie'] . '<br />' : '')),
	
	'STRENGTH'				=> $user->characteristic_full('strength', $items, $row),
	'AGILITY'				=> $user->characteristic_full('agility', $items, $row),
	'PERCEPTION'			=> $user->characteristic_full('perception', $items, $row),
	'VITALITY'				=> $user->characteristic_full('vitality', $items, $row),
	'INTELLECT'				=> $user->characteristic_full('intellect', $items, $row),
	'WISDOM'				=> $user->characteristic_full('wisdom', $items, $row),
	'SPIRITUALITY'			=> $user->characteristic_full('spirituality', $items, $row),
	'FREEDOM'				=> $user->characteristic_full('freedom', $items, $row),
	'FREEDOM_OF_SPIRIT'		=> $user->characteristic_full('freedom_of_spirit', $items, $row),
	'HOLINESS'				=> $user->characteristic_full('holiness', $items, $row),
	'WINS'					=> $row['user_wins'],
	'LOSSES'				=> $row['user_losses'],
	'DRAWS'					=> $row['user_draws'],
	
	'CLIP'					=> $user->show_item($row, $items, 'w1', 'inf'),
	'AMULET'				=> $user->show_item($row, $items, 'w2', 'inf'),
	'WEAPON'				=> $user->show_item($row, $items, 'w3', 'inf'),
	'ARMOR'					=> ( $row['user_w4'] == 0 && $row['user_w400'] > 0 ) ? $user->show_item($row, $items, 'w400', 'inf') : $user->show_item($row, $items, 'w4', 'inf'),
	'RING1'					=> $user->show_item($row, $items, 'w6', 'inf'),
	'RING2'					=> $user->show_item($row, $items, 'w7', 'inf'),
	'RING3'					=> $user->show_item($row, $items, 'w8', 'inf'),
	'HELMET'				=> $user->show_item($row, $items, 'w9', 'inf'),
	'GLOVES'				=> $user->show_item($row, $items, 'w11', 'inf'),
	'SHIELD'				=> $user->show_item($row, $items, 'w10', 'inf'),
	'BOOTS'					=> $user->show_item($row, $items, 'w12', 'inf'),
	'BELT'					=> $user->show_item($row, $items, 'w5', 'inf'),
	'NARUCHI'				=> $user->show_item($row, $items, 'w13', 'inf'),
	'R_KARMAN'				=> $user->show_item($row, $items, 'w14', 'inf'),
	'L_KARMAN'				=> $user->show_item($row, $items, 'w15', 'inf'),

	'SILENCE'				=> ( $row['user_silence'] > time() ) ? '<img src="http://static.ivacuum.ru/i/items/sleep.gif" width="40" height="25">На персонажа наложено заклятие молчания. Будет молчать ещё ' . $user->create_time($row['user_silence'] - time()) . '<br />' : '',

	'GENDER'				=> $row['user_gender'],
	'HOBBY'					=> ( $row['user_hobby'] ) ? 'Увлечения / хобби:<br><code>' . nl2br($row['user_hobby']) . '</code><br />' : '',
	'HOMEPAGE'				=> ( $row['user_homepage'] ) ? 'Домашняя страница: <a href="' . $row['user_homepage'] . '" target="_blank">' . $row['user_homepage'] . '</a><br />' : '',
	'MOTTO'					=> ( $row['user_motto'] ) ? 'Девиз: <code>' . $row['user_motto'] . '</code><br />' : '',
	'NAME'					=> $row['user_name'])
);

$template->pparse('body');

site_bottom();

?>