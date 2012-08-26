<?php
/***************************************************************************
 *								 logs.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, November 10, 2004					   *
 *   copyright			: © 2004 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: logs.php, v 1.00 2005/11/14 23:04:00 V@cuum Exp $				   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

if( empty($userdata['user_id']) )
{
	$userdata['user_access_level'] = '';
	$userdata['user_bot'] = 0;
	$userdata['user_id'] = ANONYMOUS;
}

//
// Определяем необходимые переменные
//
$analiz2			= request_var('analiz2', '');
$log_id				= request_var('log', 0);
$log_text			= '';
$n					= 0;
$p					= request_var('p', 1);
$refresh			= request_var('refresh', '');
$sort_count			= 50;
$team[1]['chars']	= '';
$team[2]['chars']	= '';
$total_rows			= 0;
// ----------

//
// Обновление страницы
//
if( $refresh == 'Обновить' )
{
	redirect('logs.php?log=' . $log_id . '#end');
}
// ----------

//
// Получаем данные выбранного лога
//
$sql = "SELECT log_time_end, log_battle_type, log_status, log_room, log_comment FROM " . LOGS_TABLE . " WHERE `log_id` = " . $log_id;
if( !$result = $db->sql_query($sql) )
{
	site_message('Не могу получить данные лога...', '', __LINE__, __FILE__, $sql);
}

$row = $db->sql_fetchrow($result);
// ----------

if( !$row )
{
	// Проверка наличия боя в БД
	site_message('Не найден лог этого боя...');
}

//
// Получаем данные персонажей: имя[hp/max]
//
if( !$row['log_time_end'] )
{
	$sql = "SELECT user_battle_team, user_login, user_current_hp, user_max_hp FROM " . USERS_TABLE . " WHERE `user_battle_id` = " . $log_id . " AND `user_current_hp` > 0";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные персонажа...', '', __LINE__, __FILE__, $sql);
	}

	while( $row2 = $db->sql_fetchrow($result) )
	{
		$team[$row2['user_battle_team']]['chars'] .= '<font class="B' . $row2['user_battle_team'] . '">' . $row2['user_login'] . '</font> [' . intval($row2['user_current_hp']) . '/' . $row2['user_max_hp'] . '], ';
	}

	$team[1]['chars'] = substr($team[1]['chars'], 0, strlen($team[1]['chars']) - 2);
	$team[2]['chars'] = substr($team[2]['chars'], 0, strlen($team[2]['chars']) - 2);
}
// ----------

if( !$analiz2 )
{
	//
	// Получаем тексты боя
	//
	$sql = "SELECT log_text FROM " . LOGS_TEXT_TABLE . " WHERE `log_id` = " . $log_id;
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные лога...', '', __LINE__, __FILE__, $sql);
	}

	// Количество записей в бое
	$total_rows = $db->sql_numrows($result);
	// ----------

	//
	// Если указана несуществующая страница, то
	// перебрасываем на первую...
	//
	$pages = intval( ( $total_rows - 1 ) / $sort_count ) + 1;
	$p = ( !$p && !$row['log_time_end'] ) ? $pages : $p;

	if( $p > $pages || $p < 0 || !preg_match('#^[0-9]+$#', $p) )
	{
		$p = 1;

		redirect('logs.php?log=' . $log_id);
	}

	$start = ( $p * $sort_count ) - $sort_count;
	// ----------

	while( $row2 = $db->sql_fetchrow($result) )
	{
		//
		// Заполняем массив текстом
		//
		if( $n >= $start && $n < ( $start + $sort_count ) )
		{
			$log_text .= $row2['log_text'];

			$n++;
		}
		elseif( $n >= ( $start + $sort_count ) )
		{
			break;
		}
		else
		{
			$n++;
		}
		// ----------
	}
}

//
// Описание типа боя
//
switch( $row['log_battle_type'] )
{
	case 1:	$battle_type_desc = 'Физический бой'; break;
	case 2:	$battle_type_desc = 'Групповой бой'; break;
	case 4:	$battle_type_desc = 'Кулачный бой'; break;
	case 6:	$battle_type_desc = 'Кровавый бой'; break;
}
// ----------

site_header();

$template->set_filenames(array(
	'body'	=> 'logs_body.html')
);

$template->assign_vars(array(
	'BATTLE_TYPE'			=> $row['log_battle_type'],
	'BATTLE_TYPE_DESC'		=> $battle_type_desc,
	'CHARS'					=> ( !$analiz2 && !$row['log_time_end'] ) ? $team[1]['chars'] . ' против ' . $team[2]['chars'] . '<hr>' : '',
	'LOG_ID'				=> $log_id,
	'LOG_TEXT'				=> ( !$analiz2 ) ? $log_text : '',
	'PAGINATION'			=> ( $total_rows > $sort_count ) ? generate_pagination('logs.php?log=' . $log_id, $total_rows, $sort_count, $start) : 'Страницы: <font class="number">1</font>')
);

if( !$row['log_time_end'] )
{
	$template->assign_block_vars('fight', array());
}
else
{
	$template->assign_block_vars('fight_end', array());
}

//
// Готовим лог к выводу...
//
//for( $i = $start; $i < $n; $i++ )
//{
//	$template->assign_block_vars('log_text', array(
//		'TEXT' => $log_text[$i])
//	);
//}
// ----------

$template->pparse('body');

site_bottom();

?>