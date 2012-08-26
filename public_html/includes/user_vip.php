<?php

class user_paladins extends user
{
	// ---------------
	// Переадресация
	//
	function redirect($url)
	{
		global $db, $userdata;

		// Обновляем данные
		$sql = "UPDATE " . USERS_TABLE . " SET `user_redirect` = '" . $url . "' WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$message = 'Редирект успешно установлен';

		return $message;
	}
	//
	// ---------------
}

$user = new user_paladins();

?>