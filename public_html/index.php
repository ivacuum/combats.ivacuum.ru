<?php
/**
*
* @package combats.ivacuum.ru
* @copyright (c) 2009 V@cuum
*
*/

//$ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : '';

//if( $ip != '10.68.248.166' )
//{
//	die('Закрыто на обновление.');
//}

//die('Переезжаем.');

define('IN_COMBATS', true);

$root_path = './';
include($root_path . 'common.php');

$userdata['user_access_level'] = '';
$userdata['user_bot'] = 0;

//
// Удаляем старые сессии
//
$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE `session_user_id` = -1";
if( !$db->sql_query($sql) )
{
	site_message('Не могу удалить старые сессии...', '', __LINE__, __FILE__, $sql);
}

$findsession	= request_var('findsession', '');
$login			= ( isset($_POST['login']) ) ? trim($_POST['login']) : '';
$psw			= ( isset($_POST['psw']) ) ? trim($_POST['psw']) : '';

if( $findsession == true && !$login && !$psw )
{
	//
	// Страница для поиска сессий
	//
	site_header();

	$template->set_filenames(array(
		'body' => 'find_session_body.html')
	);

	$template->pparse('body');

	site_bottom();
	// ----------
}
elseif( $login && $psw )
{
	//
	// Поиск сессии...
	//
	$sql = "SELECT user_id, user_password FROM " . USERS_TABLE . " WHERE `user_login` = '" . $login . "'";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные пользователя...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( $row['user_password'] != md5($psw) )
	{
		// Проверка пароля
		site_message('Неверный пароль для персонажа: ' . $login);
	}

	$sql = "SELECT session_id FROM " . SESSIONS_TABLE . " WHERE `session_user_id` = " . $row['user_id'] . " LIMIT 1";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные сессии...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( $row['session_id'] )
	{
		site_message('Для входа используйте следующую ссылку:<br /><a href="./buttons.php?battle=1&sid=' . $row['session_id'] . '">http://vacuum/combats/buttons.php?battle=1&sid=' . $row['session_id'] . '</a>', 'Информация');
	}
	else
	{
		site_message('Сессия не найдена... Возможно, вы не пробовали войти в БК...');
	}
	// ----------
}

$template->set_filenames(array(
	'body' => 'index_body.html')
);

$template->pparse('body');

site_bottom();

?>