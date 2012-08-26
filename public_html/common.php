<?php
/***************************************************************************
 *								common.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: common.php, v 1.00 2005/11/19 20:12:00 V@cuum Exp $			   *
 *																		   *
 *																		   *
 ***************************************************************************/

if( !defined('IN_COMBATS') )
{
	die("Попытка взлома!");
}

//
// Инициализируем необходимые переменные
//
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];
// ----------

//
// Устанавливаем контроль за ошибками
//
//error_reporting  (E_ERROR | E_WARNING | E_PARSE);
//error_reporting (E_ALL);
// ----------

// Be paranoid with passed vars
if( @ini_get('register_globals') )
{
	foreach( $_REQUEST as $var_name => $void )
	{
		unset(${$var_name});
	}
}

//
// Инициализируем некоторые конфигурационные массивы
//
$config = array();
$userdata = array();
// ----------

//
// Настройки БК
//
$config['admin_bank_id'] = 1090999999;						// Номер счета админов
$config['bots_bank_id'] = 1109852796;						// Номер счета ботов
$config['cookie_domain'] = '';								// Домен cookie
$config['cookie_path'] = '/combats';						// Путь для cookie
$config['cookie_secure'] = 0;								// Безопасные cookie
$config['debug_mode'] = true;								// Режим отладки
$config['fast_game'] = false;								// Быстрая игра
$config['fast_game_battle_exit'] = 3;						// Пауза перед выходом из боя
$config['fast_game_battle_refresh'] = 0;					// Пауза между ударами (сек)
$config['fast_game_level'] = 7;								// Уровень прокачки в быстрой игре
$config['fast_game_redirect'] = 3;							// Пауза перед входом в бой
$config['load_online_time'] = 900;							// Онлайн-время
$config['timezone'] = 4;									// Часовой пояс
// ----------

include($root_path . 'includes/db.php');
include($root_path . 'includes/template.php');
include($root_path . 'includes/sessions.php');
include($root_path . 'includes/functions.php');
include($root_path . 'includes/user.php');

// Префикс для таблиц
$table_prefix = 'combats_';

// Пользователи
define('ANONYMOUS', -1);
define('DELETED', -1);

// Склонности
define('USER', 0);
define('PALADIN', 1);
define('CAVALRY', '1.92');
define('HIGHEST_PALADIN', '1.99');
define('HAOS', 2);
define('DARK', 3);
define('NEUTRAL', 7);
define('ALCHEMIST', 50);
define('ANGEL', 100);

// Доступ
define('ADMIN', 'admin');

// Для БД
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);

// Метод обработки сессий
define('SESSION_METHOD_COOKIE', 100);
define('SESSION_METHOD_GET', 101);

// Таблицы
define('BANK_TABLE', $table_prefix . 'bank');
define('CHAT_TABLE', $table_prefix . 'chat');
define('FRIENDS_TABLE', $table_prefix . 'friends');
define('ITEMS_TABLE', $table_prefix . 'items');
define('KMP_TABLE', $table_prefix . 'kmp');
define('LOG_TABLE', $table_prefix . 'log');
define('LOGS_TABLE', $table_prefix . 'logs');
define('LOGS_HITS_TABLE', $table_prefix . 'logs_hits');
define('LOGS_TEXT_TABLE', $table_prefix . 'logs_text');
define('LOGS_USERS_TABLE', $table_prefix . 'logs_users');
define('SESSIONS_TABLE', $table_prefix . 'sessions');
define('SHOP_TABLE', $table_prefix . 'shop');
define('STATUS_TABLE', $table_prefix . 'status');
define('USERS_TABLE', $table_prefix . 'users');
define('ZAYAVKA_TABLE', $table_prefix . 'zayavka');

// Для функций
define('STRIP', (get_magic_quotes_gpc()) ? true : false);

//
// Определяем классы
//
$user = new user();
// ----------

//
// Инициализируем стиль оформления
//
$template_path = 'template/';

$template = new Template($root_path . $template_path);
// ----------

//
// Получаем и шифруем IP пользователя
//
$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : ( ( !empty($_ENV['REMOTE_ADDR']) ) ? $_ENV['REMOTE_ADDR'] : $REMOTE_ADDR );
$user_ip = encode_ip($client_ip);
// ----------

?>