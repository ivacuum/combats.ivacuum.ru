<?php

function session_begin($user_id, $user_ip, $auto_create = 0)
{
	global $_COOKIE, $_GET, $SID, $db;

	$cookiename = 'combats';
	$cookiepath = '/';
	$cookiedomain = '';
	$cookiesecure = '0';

	if( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
	{
		$session_id = isset($_COOKIE[$cookiename . '_sid']) ? $_COOKIE[$cookiename . '_sid'] : '';
		$sessiondata = isset($_COOKIE[$cookiename . '_data']) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata = array();
		$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	if( !preg_match('/^[A-Za-z0-9]*$/', $session_id) ) 
	{
		$session_id = '';
	}

	$last_visit = 0;
	$current_time = time();
	$expiry_time = $current_time - 3600;

	$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_id` = " . $user_id;
	if( !($result = $db->sql_query($sql)) )
	{
		site_message('Не могу получить данные пользователя №' . $user_id . '...');
	}

	$userdata = $db->sql_fetchrow($result);

	if( $user_id != ANONYMOUS )
	{
		$login = 1;
	}
	else
	{
		$login = 0;
	}

	$chat_colour = $userdata['user_chat_colour'];
	$user_login = $userdata['user_login'];

	preg_match('/(..)(..)(..)(..)/', $user_ip, $user_ip_parts);

	$sql = "UPDATE " . SESSIONS_TABLE . " SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_logged_in = $login WHERE session_id = '" . $session_id . "' AND session_ip = '$user_ip'";
	if( !$db->query($sql) || !$db->affected_rows() )
	{
		$session_id = md5(uniqid($user_ip));

		$sql = "INSERT INTO " . SESSIONS_TABLE . " (session_id, session_user_id, session_start, session_time, session_ip, session_logged_in) VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', $login)";
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу создать новую сессию...');
		}
	}

	if( $user_id != ANONYMOUS )
	{
		$last_visit = ( $userdata['user_session_time'] > 0 ) ? $userdata['user_session_time'] : $current_time; 

		$sql = "UPDATE " . USERS_TABLE . " SET user_session_time = $current_time, user_lastvisit = $last_visit WHERE `user_id` = " . $user_id;
		if( !$db->query($sql) )
		{
			site_message('Не могу обновить сессию пользователя №' . $user_id . '...');
		}

		$userdata['user_lastvisit'] = $last_visit;

		$sessiondata['userid'] = $user_id;
	}

	$userdata['session_id'] = $session_id;
	$userdata['session_ip'] = $user_ip;
	$userdata['session_user_id'] = $user_id;
	$userdata['session_logged_in'] = $login;
	$userdata['session_start'] = $current_time;
	$userdata['session_time'] = $current_time;

	setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 900, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie('user_login', $user_login, 0, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie('ChatColor', $chat_colour, 0, $cookiepath, $cookiedomain, $cookiesecure);

	$SID = 'sid=' . $session_id;

	return $userdata;
}

function session_pagestart($user_ip)
{
	global $_COOKIE, $db, $_GET, $SID;

	$cookiename = 'combats';
	$cookiepath = '/';
	$cookiedomain = '';
	$cookiesecure = '0';

	$current_time = time();
	unset($userdata);

	if( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
	{
		$sessiondata = isset( $_COOKIE[$cookiename . '_data'] ) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
		$session_id = isset( $_COOKIE[$cookiename . '_sid'] ) ? $_COOKIE[$cookiename . '_sid'] : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata = array();
		$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	if( !preg_match('/^[A-Za-z0-9]*$/', $session_id) )
	{
		$session_id = '';
	}

	if( !empty($session_id) )
	{
		$sql = "SELECT u.*, s.* FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u WHERE s.session_id = '$session_id' AND u.user_id = s.session_user_id";
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные пользователя и его сессии...');
		}

		$userdata = $db->sql_fetchrow($result);

		if( isset($userdata['user_id']) )
		{
			$ip_check_s = substr($userdata['session_ip'], 0, 6);
			$ip_check_u = substr($user_ip, 0, 6);

			if( $ip_check_s == $ip_check_u )
			{
				$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? 'sid=' . $session_id : '';

				if( $current_time - $userdata['session_time'] > 300 )
				{
					$sql = "UPDATE " . SESSIONS_TABLE . " SET session_time = '" . $current_time . "' WHERE `session_id` = '" . $userdata['session_id'] . "'";
					if( !$db->sql_query($sql) )
					{
						site_message('Не могу обновить сессию...');
					}

					if( $userdata['user_id'] != ANONYMOUS )
					{
						$sql = "UPDATE " . USERS_TABLE . " SET user_session_time = '" . $current_time . "' WHERE `user_id` = " . $userdata['user_id'];
						if( !$db->sql_query($sql) )
						{
							site_message('Не могу обновить данные пользователя №' . $userdata['user_id'] . '...');
						}
					}

					$expiry_time = $current_time - 3600;
					$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE `session_time` < '" . $expiry_time . "' AND `session_id` <> '" . $session_id . "'";
					if( !$db->sql_query($sql) )
					{
						site_message('Не могу удалить старые сессии...');
					}

					setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
					setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
				}

				return $userdata;
			}
		}
	}

	$user_id = ( isset($sessiondata['userid']) ) ? intval($sessiondata['userid']) : ANONYMOUS;

	if( !($userdata = session_begin($user_id, $user_ip, true)) )
	{
		site_message('Не могу создать сессию!');
	}

	return $userdata;

}

function session_end($session_id, $user_id)
{
	global $db;
	global $_COOKIE, $_GET, $SID;

	$cookiename = 'combats';
	$cookiepath = '/';
	$cookiedomain = '';
	$cookiesecure = '0';

	$current_time = time();

	if( isset($_COOKIE[$cookiename . '_sid']) )
	{
		$session_id = isset( $_COOKIE[$cookiename . '_sid'] ) ? $_COOKIE[$cookiename . '_sid'] : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	if( !preg_match('/^[A-Za-z0-9]*$/', $session_id) )
	{
		return;
	}

	$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE `session_id` = '" . $session_id . "' AND `session_user_id` = " . $user_id;
	if( !$db->sql_query($sql) )
	{
		site_message('Не могу удалить сессию пользователя №' . $user_id . '!');
	}

	setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);

	return true;
}

function append_sid($url, $non_html_amp = false)
{
	global $SID;

	if( !empty($SID) && !preg_match('#sid=#', $url) )
	{
		$url .= ( ( strpos($url, '?') != false ) ?  ( ( $non_html_amp ) ? '&' : '&amp;' ) : '?' ) . $SID;
	}

	return $url;
}

?>