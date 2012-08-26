<?php
/***************************************************************************
 *								 ch.php									   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: index.php, v 1.01 2006/02/23 16:30:00 V@cuum Exp $			   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);
$root_path = './';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

site_header();

//
// Определяем необходимые переменные
//
$colour		= request_var('color', 'black');
$colour		= ( $colour != $userdata['user_chat_colour'] ) ? $userdata['user_chat_colour'] : $colour;
$lid		= intval(request_var('lid', $userdata['user_lid']));
$om			= intval(request_var('om', 0));
$online		= intval(request_var('online', 0));
$show		= intval(request_var('show', 1));
$sys		= intval(request_var('sys', 0));
$text		= request_var('text', '');
// ----------

mt_srand(time() + (double)microtime() * 1000000);

if( $online )
{
	// Получаем номер комнаты
	$n = ( isset($_GET['n']) ) ? $_GET['n'] : $userdata['user_room'];

	//
	// Получаем данные пользователей онлайн
	//
	$sql = "SELECT user_id, user_bot, user_silence, user_login, user_align, user_klan, user_level, user_birthday_town, user_session_time FROM " . USERS_TABLE . " WHERE `user_town` = '" . $userdata['user_town'] . "' AND `user_room` = '" . $n . "' AND `user_blocked` = 0 AND `user_id` <> 0";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные пользователей...', '', __LINE__, __FILE__, $sql);
	}
	// ----------

	$j = 0;

	//
	// Обрабатываем данные
	//
	while( $row = $db->sql_fetchrow($result) )
	{
		if( $row['user_bot'] || ( ( time() - $row['user_session_time'] ) < $config['load_online_time'] ) )
		{
			$online_list['id'][$j] = $row['user_id'];
			$online_list['login'][$j] = $row['user_login'];
			$online_list['align'][$j] = $row['user_align'];
			$online_list['klan'][$j] = $row['user_klan'];
			$online_list['level'][$j] = $row['user_level'];
			$online_list['sleep'][$j] = ( $row['user_silence'] > time() ) ? 1 : 0;
			$online_list['town'][$j] = $row['user_birthday_town'];

			$j++;
		}
	}
	// ----------

	// Сортируем персонажей по алфавиту
	array_multisort($online_list['login'], SORT_ASC, SORT_STRING, $online_list['id'], $online_list['align'], $online_list['klan'], $online_list['level'], $online_list['sleep'], $online_list['town']);

	$template->set_filenames(array(
		'online_body' => 'ch_online_body.html')
	);

	//
	// Выводим онлайн-список
	//
	for( $i = 0; $i < $j; $i++ )
	{
		$template->assign_block_vars('online', array(
			'ALIGN'		=> $online_list['align'][$i],
			'ATTACK'	=> 0,
			'CITY'		=> $user->city_name($online_list['town'][$i], 'int'),
			'ID'		=> $online_list['id'][$i],
			'KLAN'		=> $online_list['klan'][$i],
			'LEVEL'		=> $online_list['level'][$i],
			'LOGIN'		=> $online_list['login'][$i],
			'SLEEP'		=> $online_list['sleep'][$i])
		);
	}
	// ----------

	if( $n == $userdata['user_room'] )
	{
		$template->assign_block_vars('our_room', array());
	}

	$template->assign_vars(array(
		'ALIGN'			=> $userdata['user_align'],
		'CHARS'			=> $j,
		'LEVEL'			=> $userdata['user_level'],
		'OPENER'		=> ( $n == $userdata['user_room'] ) ? '' : 'window.opener.',
		'RND_VALUE'		=> substr(mt_rand(), 0, 5),
		'ROOM'			=> $user->get_room_name($n),
		
		'U_REFRESH'		=> append_sid($root_path . 'ch.php?online=' . substr(mt_rand(), 0, 5)))
	);

	$template->pparse('online_body');

	$db->sql_close();
	exit;
}

if( $text )
{
	// Если нет молчанки, то вперед
	if( $userdata['user_silence'] <= time() )
	{
		// Раскрашиваем сообщение
		$text = '<font color=' . $colour . '>' . addslashes($text) . '</font>';

		// Добавляем сообщение
		$user->add_chat_message($userdata, $text);
		$msg_id = $db->sql_nextid();
	}

	$show = 1;

	print '<script>top.CLR1();</script>';
}

if( isset($show) )
{
	$lid = ( !$lid ) ? $userdata['user_lid'] : $lid;

	//
	// Получаем данные чата
	//
	$sql = "SELECT * FROM " . CHAT_TABLE . " LIMIT " . $lid . ", 10000";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные чата...');
	}
	// ----------

	$text = '';
	
	//
	// Обрабатываем сообщения
	//
	while( $row = $db->sql_fetchrow($result) )
	{
		if( $row['msg_to'] != '' )
		{
			$recipients = explode(", ", $row['msg_to']);
		}
		else
		{
			$recipients = array();
		}

		if( $lid )
		{
			if( $row['msg_system'] )
			{
				$text .= '<font class=sysdate>' . date('H:i', $row['msg_time']) . '</font> <font color=red>Внимание!</font> ' . $row['msg_text'] . '<br>';
			}
			elseif( $row['msg_sys'] == 1 )
			{
				if( $sys == 1 && !$om && $recipients == NULL )
				{
					if( $row['msg_room'] == $userdata['user_room'] && ( time() - $row['msg_time'] ) < 180 )
					{
						// Системные сообщения
						$text .= '<font class=sysdate>' . date('H:i', $row['msg_time']) . '</font> ' . $row['msg_text'] . '<br>';
					}
				}
				elseif( $sys == 1 && $row['msg_private'] == 0 && ( time() - $row['msg_time'] ) < 180 && ( in_array($userdata['user_login'], $recipients) || !$om ) )
				{
					// Системные сообщения (личные)
					$text .= '<font class=sysdate>' . date('d.m.y H:i', $row['msg_time']) . '</font> ' . $row['msg_text'] . '<br>';
				}
				elseif( $sys == 1 && $row['msg_private'] == 1 && in_array($userdata['user_login'], $recipients) )
				{
					// Системные сообщения (приватные)
					$text .= '<font class=date2>' . date('H:i', $row['msg_time']) . '</font> ' . $row['msg_text'] . '<br>';
				}
			}
			elseif( ( time() - $row['msg_time'] ) < 180 && ( in_array($userdata['user_login'], $recipients) || $userdata['user_login'] == $row['msg_author'] ) )
			{
				// Сообщения, отправленные игроку
				$text .= '<font class=date>' . date('H:i', $row['msg_time']) . '</font> [<span>' . $row['msg_author'] . '</span>] ' . $row['msg_text'] . '<br>';
			}
			elseif( !$om )
			{
				if( ( time() - $row['msg_time'] ) < 180 && $userdata['user_room'] == $row['msg_room'] && $row['msg_private'] == 0 )
				{
					// Прочие разговоры
					$text .= '<font class=date>' . date('H:i', $row['msg_time']) . '</font> [<span>' . $row['msg_author'] . '</span>] ' . $row['msg_text'] . '<br>';
				}
			}
		}

		$lid++;
	}
	// ----------

	//
	// Обновляем данные пользователя
	//
	if( $lid != $userdata['user_lid'] )
	{
		$sql = "UPDATE " . USERS_TABLE . " SET user_lid = " . $lid . " WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
	}
	// ----------

	$online_reload = ( ( time() - $userdata['user_room_time'] ) < 15 ) ? 'top.rld(true)' : '';

	print '<script>top.slid(' . $lid . ');' . $online_reload . '</script>';
	print '<script>top.frames["chat"].am(\'' . addslashes($text) . '\');</script>';
}

$db->sql_close();
exit;

?>