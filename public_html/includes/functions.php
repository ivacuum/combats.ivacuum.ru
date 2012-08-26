<?php

function set_var(&$result, $var, $type)
{
	settype($var, $type);
	$result = $var;

	if( $type == 'string' )
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", '\xFF'), array("\n", "\n", ' '), $result)));
		$result = preg_replace("#\n{3,}#", "\n\n", $result);
		$result = (STRIP) ? stripslashes($result) : $result;
	}
}

function request_var($var_name, $default)
{
	if( !isset($_REQUEST[$var_name]) )
	{
		return $default;
	}
	else
	{
		$var = $_REQUEST[$var_name];
		$type = gettype($default);

		if( is_array($var) )
		{
			foreach( $var as $k => $v )
			{
				if( is_array($v) )
				{
					foreach( $v as $_k => $_v )
					{
						set_var($var[$k][$_k], $_v, $type);
					}
				}
				else
				{
					set_var($var[$k], $v, $type);
				}
			}
		}
		else
		{
			set_var($var, $var, $type);
		}

		return $var;
	}
}

//
// Путь к корневому каталогу сайта
//
function bk_realpath($path)
{
	global $root_path;

	return (!@function_exists('realpath') || !@realpath($root_path . 'includes/functions.php')) ? $path : @realpath($path);
}
// ----------

// ---------------
// Вывод ошибок
//
function site_message($msg_text, $msg_title = '', $error_line = '', $error_file = '', $sql = '')
{
	global $db, $starttime, $root_path, $template, $userdata, $user_ip;

	//
	// Получаем данные пользователя (если нужно)
	//
	if( empty($userdata) )
	{
		$userdata = session_pagestart($user_ip);
	}

	if( $userdata['session_logged_in'] == 0 )
	{
		$userdata['user_access_level'] = 0;
		$userdata['user_bot'] = 0;
	}
	// ----------

	$sql_store = $sql;

	//
	// Информация для админов и модераторов
	//
	if( $userdata['user_access_level'] == ADMIN || $userdata['user_bot'] == 1 )
	{
		$sql_error = $db->sql_error();

		$error_text = '';

		if( $sql_error['message'] != '' )
		{
			$error_text .= '<br /><br />SQL ошибка: ' . $sql_error['code'] . ' ' . $sql_error['message'];
		}

		if( $sql_store != '' )
		{
			$error_text .= '<br /><br />Запрос: ' . $sql_store;
		}

		if( $error_line != '' && $error_file != '' )
		{
			$error_file = str_replace('D:\Servers\home\localhost', 'http:\\\vacuum', $error_file);
			$error_text .= '<br /><br />Строка: ' . $error_line . '<br />Файл: ' . $error_file;
		}
	}
	// ----------

	//
	// Устанавливаем оформление (если нужно)
	//
	if( empty($template) )
	{
		$template = new Template($root_path . 'templates');
	}
	// ----------

	$msg_title = ( $msg_title != '' ) ? $msg_title : 'Произошла ошибка';
	$page_title = $msg_title;

	//
	// Шапка
	//
	include_once $root_path . 'includes/page_header.php';
	// ----------

	if( $userdata['user_access_level'] == ADMIN || $userdata['user_bot'] == 1 )
	{
		if( $error_text != '' )
		{
			$msg_text .= '<br /><br /><b>Информация для администратора:</b>' . $error_text;
		}
	}

	$template->set_filenames(array(
		'body' => 'message_body.html')
	);

	$template->assign_vars(array(
		'MESSAGE_TEXT'	=> $msg_text,
		'MESSAGE_TITLE'	=> $msg_title)
	);

	$template->pparse('body');

	//
	// Ноги )
	//
	include($root_path . 'includes/page_bottom.php');
	// ----------
}
//
// ---------------

//
// Кодирование IP
//
function encode_ip($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}
// ----------

//
// Декодирование IP
//
function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}
// ----------

/**
* Полный путь к корневой папка сайта в виде URL
* Пример: http://vacuum.elcomnet.ru/script_path
*/
function site_url()
{
	$server_name = ( !empty($_SERVER['SERVER_NAME']) ) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');
	$server_port = ( !empty($_SERVER['SERVER_PORT']) ) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$https = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ) ? 1 : 0;

	$script_path = ( !empty($_SERVER['PHP_SELF']) ) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');

	if( !$script_path )
	{
		$script_path = ( !empty($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}

	$script_path = trim(dirname($script_path)) . '/';

	$site_url = ( ( $https ) ? 'https://' : 'http://' ) . $server_name;
	$site_url .= ( !empty($server_port) && ( $https && $server_port <> 443 ) || ( !$https && $server_port <> 80 ) ) ? ':' . $server_port : '';
	$site_url .= $script_path;

	return $site_url;
}

//
// Перебрасывает пользователя на определенную страницу
//
function redirect($url)
{
	if( !empty($db) )
	{
		$db->close();
	}

	/**
	* Если ссылка относительная, то добавляем site_url()
	*/
	$url = ( !substr($url, 0, 4) == 'http' ) ? site_url() . $url : $url;

	if( strpos(urldecode($url), "\n") !== false || strpos(urldecode($url), "\r") !== false || strpos($url, ';') !== false )
	{
		trigger_error('Неверный URL.', E_USER_ERROR);
	}

	header('Location: ' . $url);
	exit;
}
// ----------

//
// Создание ссылок на страницы
//
function generate_pagination($url, $num_items, $per_page, $start_item, $add_prevnext_text = true)
{
	$seperator = '&nbsp;';

	$total_pages = ceil($num_items / $per_page);

	if( $total_pages == 1 || !$num_items )
	{
		return false;
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = 'Страницы: ';
	$page_string .= ( $on_page == 1 ) ? '<font class="number">1</font>' : '<a href="' . $url . '&amp;p=1">1</a>';

	if( $total_pages > 10 )
	{
		$start_cnt = min(max(1, $on_page - 9), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 9), 9);

		$page_string .= ( $start_cnt > 1 ) ? ' ... ' : $seperator;

		for( $i = $start_cnt + 1; $i < $end_cnt; $i++ )
		{
			$page_string .= ( $i == $on_page ) ? '<font class="number">' . $i . '</font>' : '<a href="' . $url . '&amp;p=' . $i . '">' . $i . '</a>';
			if( $i < $end_cnt - 1 )
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ( $end_cnt < $total_pages ) ? ' ... ' : $seperator;
	}
	else
	{
		$page_string .= $seperator;

		for( $i = 2; $i < $total_pages; $i++ )
		{
			$page_string .= ( $i == $on_page ) ? '<font class="number">' . $i . '</font>' : '<a href="' . append_sid($url . '&amp;p=' . $i) . '">' . $i . '</a>';
			if( $i < $total_pages )
			{
				$page_string .= $seperator;
			}
		}
	}

	$page_string .= ( $on_page == $total_pages ) ? '<font class="number">' . $total_pages . '</font>' : '<a href="' . append_sid($url . '&amp;p=' . $total_pages) . '">' . $total_pages . '</a>';

	return $page_string;
}
// ----------

// ---------------
// «Конечности» страницы ;)
//
function site_bottom()
{
	global $config, $db, $script_start_time, $template, $userdata;

	$template->set_filenames(array(
		'bottom' => 'page_bottom.html')
	);

	$mtime = explode(' ', microtime());
	$totaltime = $mtime[0] + $mtime[1] - $script_start_time;
	$url = '';

	//
	// Получаем переменные отправленные методами GET и POST
	//
	if( $config['debug_mode'] && $userdata['user_access_level'] == ADMIN )
	{
		foreach( $_GET as $key => $value )
		{
			$url .= $key . '=' . $value . '&';
		}

		foreach( $_POST as $key => $value )
		{
			$url .= $key . '=' . $value . '&';
		}

		$url = ( $url ) ? '?' . $url : '';
	}
	// ----------

	$debug_info = ( $userdata['user_access_level'] == ADMIN || $userdata['user_bot'] ) ? sprintf('[ Время: %.3fсек | Запросов: ' . ( $db->total_queries + 2 ) . ' ]', $totaltime) : '';
	$debug_info .= ( $config['debug_mode'] && $userdata['user_access_level'] == ADMIN ) ? '<br /><br />[ http://' . $_SERVER['SERVER_NAME'] . /*$_SERVER['REDIRECT_URL'] .*/ substr($url, 0, -1) . ' ]' : '';

	$template->assign_vars(array(
		'DEBUG_INFO' => $debug_info)
	);

	$template->pparse('bottom');

	$db->sql_close();
	exit;
}

// ---------------
// Шапка
//
function site_header()
{
	// Запрет кэширования страниц
	header ('Cache-Control: no-cache, pre-check=0, post-check=0');
	header ('Expires: 0');
	header ('Pragma: no-cache');
}
//
// ---------------

//
// Получение данных пользователя через ID или логин
//
function get_userdata($id = false, $user_login = false)
{
	global $db;

	if( $id )
	{
		$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_id` = " . $id . " AND `user_id` <> " . ANONYMOUS;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		return $row;
	}
	elseif( $user_login )
	{
		$sql = "SELECT * FROM " . USERS_TABLE . " WHERE `user_login` = '" . $user_login . "' AND `user_id` <> " . ANONYMOUS;
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		return $row;
	}
	else
	{
		return false;
	}
}
// ----------

//
// Подсчет числа строк
//
function get_total_rows($select, $from, $where)
{
	global $db;

	$where = ( !empty($where) ) ? $where : 1;

	$sql = "SELECT " . $select . " FROM " . $from . " WHERE " . ( ( !empty($where) ) ? $where : 1 );
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить кол-во строк...', '', __LINE__, __FILE__, $sql);
	}

	return $db->sql_numrows($result);
}
// ----------

//
// Получаем и возвращаем маскимальное значение
//
function get_max_row($row, $as, $from, $where)
{
	global $db;

	$where = ( !empty($where) ) ? $where : 1;

	$sql = "SELECT MAX(" . $row . ") AS " . $as . " FROM " . $from . " WHERE " . $where;
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить последнюю строчку...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	return $row[$as];
}
// ----------
?>