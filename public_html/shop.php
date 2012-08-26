<?php

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');

$userdata = session_pagestart($user_ip);

include($root_path . 'includes/page_header.php');

//
// Начинаем восстановление и обновляем HP и ману
//
$user->start_regen($userdata);
$user->update_hp($userdata);
$user->update_mana($userdata);
// ----------

//
// Если персонаж в бое, то он и должен там быть
//
if( $userdata['user_battle_id'] > 0 )
{
	redirect($root_path . 'battle.php');
}
// ----------

//
// Определяем переменные
//
$addcount		= request_var('addcount', '');
$count			= intval(request_var('count', 1));
$otdel			= ( isset($_GET['otdel']) ) ? $_GET['otdel'] : ( ( isset($_POST['otdel']) ) ? $_POST['otdel'] : '');
$message		= '';
$path			= request_var('path', '');
$sale			= request_var('sale', '');
$set			= request_var('set', '');
$shop_otdel		= ( isset($_COOKIE['shop_otdel']) ) ? $_COOKIE['shop_otdel'] : 'knifes';
$sl				= request_var('sl', 0);
// ----------

//
// Переход по комнатам
//
if( $userdata['user_room'] == '1.100' )
{
	redirect($root_path . 'main.php');
}
elseif( $path )
{
	$user->path($path);

	redirect($root_path . 'main.php');
}
// ----------

include($root_path . 'includes/user_shop.php');

//
// Отдел
//
if( $otdel != $shop_otdel && $otdel != '' )
{
	$user->set_cookie('shop_otdel', $otdel, ( time() + 3600 ));
	$shop_otdel = $otdel;
}
// ----------

if( $addcount && $userdata['user_access_level'] == ADMIN )
{
	// ---------------
	// Дозавоз
	//
	if( $count < 1 )
	{
		$message = 'Минимальное количество вещей для завоза: 1';
	}
	else
	{
		// Привозим вещь
		$message = $user->item_addcount($addcount, $count);
	}
	//
	// ---------------
}
elseif( $set && $count > 0 )
{
	// ---------------
	// Покупка вещей
	//

	//
	// Получаем данные покупаемой вещи
	//
	$sql = "SELECT * FROM " . SHOP_TABLE . " WHERE `item_img` = '" . $set . "'";
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные покупаемой вещи...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);

	$row['item_price_real'] = $row['item_price'];
	$row['item_price'] = ( $row['item_ekrprice'] > 0 && $row['item_artefact'] ) ? ( $row['item_ekrprice'] * 2 ) : ( ( $row['item_ekrprice'] > 0 && !$row['item_artefact'] ) ? ( $row['item_ekrprice'] * 10 ) : $row['item_price'] );
	// ----------

	//
	// Покупаем вещь (если можем)
	//
	if( $userdata['user_money'] >= ( $row['item_price'] * $count ) )
	{
		if( $row['item_count'] >= $count )
		{
			// Если денег хватает, то покупаем вещь (и если товара достаточно)
			$user->item_buy($row, $count);
			$userdata['user_money'] -= ( $row['item_price'] * $count );

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], '1.9', 'buy', '"' . $userdata['user_login'] . '" купил товар: "' . $row['item_name'] . '" (' . $count . ' шт.) за ' . ( $row['item_price'] * $count ) . ' кр.');

			$message = ( $count == 1 ) ? 'Вы купили "' . $row['item_name'] . '".' : 'Вы купили "' . $row['item_name'] . '" в количестве ' . $count . ' шт.';
		}
		else
		{
			$message = 'Вы не можете купить столько единиц товара';
		}
	}
	else
	{
		$message = 'У вас недостаточно денег';
	}
	//
	// ---------------
}
elseif( $sl )
{
	// ---------------
	// Продажа предмета
	//

	//
	// Получаем данные продаваемой вещи
	//
	$sql = "SELECT * FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $sl . " AND `item_user_id` = " . $userdata['user_id'];
	if( !$result = $db->sql_query($sql) )
	{
		site_message('Не могу получить данные продаваемой вещи...', '', __LINE__, __FILE__, $sql);
	}

	$row = $db->sql_fetchrow($result);
	// ----------

	if( !$row )
	{
		$message = 'Вещь не найдена в рюкзаке';
	}
	else
	{
		$sale_price = sprintf("%.2f", ( ( $row['item_price'] / 2 ) - ( $row['item_current_durability'] * 0.12 ) ));

		// Продаем вещь
		$user->item_sale($row, $sale_price);
		$userdata['user_money'] += $sale_price;

		// Сообщаем о продаже
		$message = 'Вы продали "' . $row['item_name'] . '".';

		$sale = true;
	}
	//
	// ---------------
}

//
// Названия отделов
//
switch( $shop_otdel )
{
	case 'knifes':			$otdel_name = 'Оружие: кастеты, ножи'; break;
	case 'axes':			$otdel_name = 'Оружие: топоры'; break;
	case 'clubs':			$otdel_name = 'Оружие: дубины, булавы'; break;
	case 'swords':			$otdel_name = 'Оружие: мечи'; break;
	case 'bows':			$otdel_name = 'Оружие: луки и арбалеты'; break;
	case 'staffs':			$otdel_name = 'Оружие: магические посохи'; break;
	case 'boots':			$otdel_name = 'Одежда: сапоги'; break;
	case 'gloves':			$otdel_name = 'Одежда: перчатки'; break;
	case 'shirts':			$otdel_name = 'Одежда: рубахи'; break;
	case 'larmor':			$otdel_name = 'Одежда: легкая броня'; break;
	case 'harmor':			$otdel_name = 'Одежда: тяжелая броня'; break;
	case 'helmets':			$otdel_name = 'Одежда: шлемы'; break;
	case 'naruchi':			$otdel_name = 'Одежда: наручи'; break;
	case 'belts':			$otdel_name = 'Одежда: пояса'; break;
	case 'shields':			$otdel_name = 'Щиты'; break;
	case 'clips':			$otdel_name = 'Ювелирные товары: серьги'; break;
	case 'amulets':			$otdel_name = 'Ювелирные товары: ожерелья'; break;
	case 'rings':			$otdel_name = 'Ювелирные товары: кольца'; break;
	case 'neutralspells':	$otdel_name = 'Заклинания: нейтральные'; break;
	case 'atdefspells':		$otdel_name = 'Заклинания: боевые и защитные'; break;
	case 'amunition':		$otdel_name = 'Амуниция'; break;
	case 'extraitems':		$otdel_name = 'Дополнительные предметы'; break;
	case 'potions':			$otdel_name = 'Эликсиры'; break;
	case 'gifts':			$otdel_name = 'Подарки'; break;
}
// ----------

//
// Обрабатываем вещи, находящиеся на прилавке
//
$items = array();
$user->obtain_items($items, $userdata, ( ( $sale ) ? 'shop_sale' : 'shop' ));
// ----------

// Ссылки
$links = $user->get_room_links($userdata['user_room']);
$user->links_display($links);

$template->set_filenames(array(
	'body' => 'shop_body.html')
);

if( $userdata['user_access_level'] == ADMIN )
{
	$template->assign_block_vars('admin', array());
}

$template->assign_vars(array(
	'GOING_TIME'			=> $user->get_going_time(),
	'MESSAGE'				=> ( $message ) ? '&nbsp;<font color="red"><b>' . $message . '</b></font>' : '',
	'MONEY'					=> sprintf('%.2f', $userdata['user_money']),
	'OTDEL'					=> $otdel,
	'OTDEL_NAME'			=> ( $sale ) ? 'Скупка' : $otdel_name,
	'OTDEL_SALE'			=> ( $sale ) ? '<br />Здесь вы можете продать свои вещи, за жалкие гроши...<br />У вас в наличии:' : '',
	'WEIGHT'				=> $userdata['user_items_mass'] . '/' . ( ( $userdata['user_strength'] * 4 ) + $userdata['user_items_max_mass'] ))
);

$template->pparse('body');

include($root_path . 'includes/page_bottom.php');	

?>