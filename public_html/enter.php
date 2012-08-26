<?php
/***************************************************************************
 *								enter.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: enter.php, v 1.00 2005/11/12 17:36:00 V@cuum Exp $			   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

$mode = request_var('mode', 'login');

if( $mode == 'login' && !$userdata['session_logged_in'] )
{
	$login = isset($_POST['name']) ? trim($_POST['name']) : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';

	$sql = "SELECT user_id, user_blocked, user_login, user_password, user_room FROM " . USERS_TABLE . " WHERE `user_login` = '" . $login . "'";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные пользователя...', '', __LINE__, __FILE__, $sql);
	}

	if( $row = $db->sql_fetchrow($result) )
	{
		if( md5($password) == $row['user_password'] && !$row['user_blocked'] )
		{
			$session_id = session_begin($row['user_id'], $user_ip, FALSE);

			if( $session_id )
			{
				$user->add_chat_message($row, 'Вас приветствует: <span>' . $row['user_login'] . '</span>', false, false, true);
				$user->status_delete();

				//
				// Оповещение
				//
				$sql = "SELECT u.user_login FROM " . FRIENDS_TABLE . " f, " . USERS_TABLE . " u WHERE f.friend_user_id = u.user_id AND f.friend_id = " . $row['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					site_message('Не могу получить данные списка друзей...', '', __LINE__, __FILE__, $sql);
				}

				$recipients = '';

				while( $row2 = $db->sql_fetchrow($result) )
				{
					$recipients .= $row2['user_login'] . ', ';
				}

				if( $recipients )
				{
					$user->add_chat_message($row, '<font color="red">Внимание!</font> Вас приветствует: <span>' . $row['user_login'] . '</span>', substr($recipients, 0, -2), true, true);
				}
				// ----------

				redirect('buttons.php?battle=1');
			}
			else
			{
				site_message('Не могу преобразовать ID сессии...');
			}
		}
		else
		{
			if( $row['user_blocked'] == 1 )
			{
				site_message('Персонаж ' . $login . ' заблокирован...');
			}
			else
			{
				site_message('Неверный пароль для персонажа: ' . $login);
			}
		}
	}
	else
	{
		site_message('Невозможно зайти...');
	}
}
elseif( $mode == 'logout' && $userdata['session_logged_in'] )
{
	if( $userdata['session_logged_in'] )
	{
		session_end($userdata['session_id'], $userdata['user_id']);
	}

	redirect(append_sid('index.php'));
}
else
{
	redirect(append_sid('index.php'));
}

?>
