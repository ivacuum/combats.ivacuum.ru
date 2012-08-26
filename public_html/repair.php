<?php

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

include($root_path . 'includes/page_header.php');

//
// Если персонаж в бое, то он и должен там быть
//
if( $userdata['user_battle_id'] > 0 )
{
	redirect($root_path . 'battle.php');
}
// ----------

//
// Начинаем восстановление и обновляем HP и ману
//
$user->start_regen($userdata);
$user->update_hp($userdata);
$user->update_mana($userdata);
// ----------

//
// Определяем переменные
//
$fit		= request_var('fit', 0);
$full		= request_var('full', 0);
$medium		= request_var('medium', 0);
$message	= '';
$path		= request_var('path', '');
$room		= request_var('room', '');
$rp			= request_var('rp', 0);
// ----------

//
// Переход по комнатам
//
if( $userdata['user_room'] == '1.100' )
{
	redirect($root_path . 'main.php');
}

if( $path )
{
	$user->path($path);

	redirect($root_path . 'main.php');
}
// ----------

if( $fit > 0 )
{
	// ---------------
	// Подгонка предмета
	//
	$sql = "SELECT item_id, item_type, item_fit, item_fit_login, item_name, item_req_level, item_hp FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $fit . " AND `item_user_id` = " . $userdata['user_id'];
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( !$row )
	{
		$message = 'Вещь не найдена в рюкзаке';
	}
	elseif( $userdata['user_money'] < ( 10 + ( 5 * $row['item_req_level'] ) ) )
	{
		$message = 'У вас недостаточно денег';
	}
	elseif( $row['item_fit'] || $row['item_fit_login'] )
	{
		$message = 'Предмет уже подогнан';
	}
	elseif( $row['item_type'] != 'larmor' && $row['item_type'] != 'harmor' )
	{
		$message = 'Подгонка этой вещи невозможна';
	}
	elseif( $row['item_req_level'] < 1 )
	{
		$message = 'Подгонка этой вещи невозможна';
	}
	else
	{
		// Определяем HP и цену
		$fit_hp = 6 + ( 6 * $row['item_req_level'] );
		$fit_price = 10 + ( 5 * $row['item_req_level'] );

		include($root_path . 'includes/user_repair.php');

		// Подгоняем предмет
		$user->item_fit($row, $fit_hp, $fit_price);

		$message = 'Вы удачно подогнали под себя "' . $row['item_name'] . '".';
	}

	$room = 4;
	//
	// ---------------
}
elseif( $rp > 0 )
{
	// ---------------
	// Починка предмета
	//
	$sql = "SELECT item_is_equip, item_name, item_img, item_price, item_current_durability, item_can_repair FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $rp . " AND `item_user_id` = " . $userdata['user_id'];
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	if( !$row || $row['item_is_equip'] )
	{
		$message = 'Вещь не найдена в рюкзаке';
	}
	elseif( $userdata['user_money'] < ( 0.1 * $repair_durability ) )
	{
		$message = 'У вас недостаточно денег';
	}
	elseif( !$row['item_can_repair'] )
	{
		$message = 'Предмет не подлежит ремонту';
	}
	else
	{
		$repair_durability = ( $full ) ? $row['item_current_durability'] : ( ( $medium ) ? 10 : 1);

		$damage = $user->item_repair($userdata['user_id'], $rp, $repair_durability, ( ( $userdata['user_bot'] == 1 ) ? true : false ));

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'repair', '"' . $userdata['user_login'] . '" отремонтировал предмет "' . $row['item_name'] . '" за ' . ( 0.1 * $repair_durability ) . ' кр.');

		$userdata['user_money'] -= 0.1 * $repair_durability;
		$message = ( !$damage ) ? 'Вы удачно отремонтировали вещь' : 'Вы удачно отремонтировали вещь, но её максимальная долговечность уменьшилась.';
	}

	$room = '';
	//
	// ---------------
}

//
// Обрабатываем вещи, находящиеся в рюкзаке
//
$items = array();
$user->obtain_items($items, $userdata, 'repair');
// ----------

$links = $user->get_room_links($userdata['user_room']);
$user->links_display($links);

//include($root_path . 'includes/user_repair.php');

$template->set_filenames(array(
	'body'	=> 'repair_body.html')
);

$template->assign_vars(array(
	'REPAIR'			=> ( !$room ) ? '<td nowrap align="center" bgcolor="A5A5A5">&nbsp;&nbsp;<b>Ремонт</b>&nbsp;&nbsp;</td>' : '<td nowrap align="center">&nbsp;&nbsp;<a href="repair.php">Ремонт</a>&nbsp;&nbsp;</td>',
	'ETCHING'			=> ( $room == 1 ) ? '<td nowrap align="center" bgcolor="A5A5A5">&nbsp;&nbsp;<b>Гравировка</b>&nbsp;&nbsp;</td>' : '<td nowrap align="center">&nbsp;&nbsp;<a href="repair.php?room=1">Гравировка</a>&nbsp;&nbsp;</td>',
	'RECHARGE'			=> ( $room == 2 ) ? '<td nowrap align="center" bgcolor="A5A5A5">&nbsp;&nbsp;<b>Перезарядка магии</b>&nbsp;&nbsp;</td>' : '<td nowrap align="center">&nbsp;&nbsp;<a href="repair.php?room=2">Перезарядка магии</a>&nbsp;&nbsp;</td>',
	'UPGRADE'			=> ( $room == 3 ) ? '<td nowrap align="center" bgcolor="A5A5A5">&nbsp;&nbsp;<b>Усиление артефактов</b>&nbsp;&nbsp;</td>' : '<td nowrap align="center">&nbsp;&nbsp;<a href="repair.php?room=3">Усиление артефактов</a>&nbsp;&nbsp;</td>',
	'DESTINY'			=> ( $room == 4 ) ? '<td nowrap align="center" bgcolor="A5A5A5">&nbsp;&nbsp;<b>Подгонка</b>&nbsp;&nbsp;</td>' : '<td nowrap align="center">&nbsp;&nbsp;<a href="repair.php?room=4">Подгонка</a>&nbsp;&nbsp;</td>',
	'DESC'				=> ( $room == 1 ) ? 'Нанесение надписей на оружие' : ( ( $room == 2 ) ? 'Перезарядка встроенной магии' : ( ( $room == 3 ) ? 'Протирка и полировка артефактов' : ( ( $room == 4 ) ? 'Подгонка вещей' : 'Починка поврежденных предметов'))),

//	'ARTEFACT_PREVIEW'	=> $user->item_upgrade_artefact_preview('ahelmet1', '10', '5', 'Emeralds city'),
	'GOING_TIME'		=> $user->get_going_time(),
	'MESSAGE'			=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : '',
	'MONEY'				=> sprintf('%.2f', $userdata['user_money']),
	'WEIGHT'			=> $items['weight'] . '/' . ( ( $userdata['user_strength'] * 4 ) + $items['plus_weight'] ),

	'U_REFRESH'			=> append_sid($root_path . 'repair.php?room=' . $room))
);

// Отдел (для обработчика шаблонов)
$otdel_name = ( $room == 1 ) ? 'etching' : ( ( $room == 2 ) ? 'recharge' : ( ( $room == 3 ) ? 'upgrade' : ( ( $room == 4 ) ? 'destiny' : 'repair')));

if( !$items['count'] )
{
	// Если вещей в рюкзаке нет, то...
	$template->assign_block_vars('no_items', array(
		'MESSAGE'		=> ( $room == 1 ) ? 'У вас в рюкзаке нет оружия, на которое можно нанести гравировку' : ( ( $room == 2 ) ? 'У вас в рюкзаке нет предметов со встроенной магией' : ( ( $room == 3 ) ? 'У вас в рюкзаке нет артефактов, которые можно усилить' : ( ( $room == 4 ) ? 'У вас в рюкзаке нет предметов, которые можно подогнать' : 'У вас в рюкзаке нет поврежденных предметов'))))
	);
}
// ----------

$template->pparse('body');

include($root_path . 'includes/page_bottom.php');	

?>