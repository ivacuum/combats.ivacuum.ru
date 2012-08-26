<?php

class user_shop extends user
{
	// ---------------
	// Увеличение кол-ва вещей
	//
	function item_addcount($item_img, $count)
	{
		global $db, $user, $userdata;

		//
		// Получаем данные вещи
		//
		$sql = "SELECT item_id, item_name, item_price FROM " . SHOP_TABLE . " WHERE `item_img` = '" . $item_img . "'";
		if( !$result = $db->sql_query($sql) )
		{
			site_message('Не могу получить данные вещи...', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);
		// ----------

		// Цена завоза
		$items_price = sprintf('%.2f', (( $row['item_price'] * $count * 85 ) / 100 ));

		//
		// Проверки
		//
		if( !$row )
		{
			$message = 'Предмет не найден в магазине';
		}
		elseif( !preg_match('#^[0-9]+$#', $count) )
		{
			$message = 'Неверно указано количество завозимых предметов';
		}
		elseif( $userdata['user_money'] < $items_price )
		{
			$message = 'У вас недостаточно денег';
		}
		else
		{
			//
			// Если денег достаточно, то привозим вещь
			//
			$sql = "UPDATE " . SHOP_TABLE . " SET item_count = (item_count + " . $count . ") WHERE `item_id` = " . $row['item_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу привезти вещь...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			//
			// Снимаем деньги
			//
			$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - " . $items_price . ") WHERE `user_id` = " . $userdata['user_id'];
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу взять деньги за доставку...', '', __LINE__, __FILE__, $sql);
			}
			// ----------

			// Обновляем переменные
			$userdata['user_money'] -= $items_price;

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], 'admin', 'addcount', $user->drwfl($userdata) . ' привез в магазин товар: <img align="absmiddle" src="i/items/' . $item_img . '.gif" alt="' . $row['item_name'] . '"> (' . $count . ' шт.) за ' . $items_price . ' кр.');

			$message = 'Доставлен предмет "' . $row['item_name'] . '" (' . $count . ' шт.) за ' . $items_price . ' кр.';
		}
		// ----------

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Покупка вещей
	//
	function item_buy($row, $count = false)
	{
		global $config, $db, $userdata;

		//
		// Отдел, слот и тип вещи
		//
		switch( $row['item_otdel'] )
		{
			case 'knifes':			$otdel = 1; $slot = 3; $type = 'knife'; break;
			case 'axes':			$otdel = 1; $slot = 3; $type = 'axe'; break;
			case 'clubs':			$otdel = 1; $slot = 3; $type = 'club'; break;
			case 'swords':			$otdel = 1; $slot = 3; $type = 'sword'; break;
			case 'bows':			$otdel = 1; $slot = 3; $type = 'bow'; break;
			case 'staffs':			$otdel = 1; $slot = 3; $type = 'staff'; break;
			case 'boots':			$otdel = 1; $slot = 12; $type = 'boots'; break;
			case 'gloves':			$otdel = 1; $slot = 11; $type = 'gloves'; break;
			case 'shirts':			$otdel = 1; $slot = 400; $type = 'shirt'; break;
			case 'larmor':			$otdel = 1; $slot = 4; $type = 'larmor'; break;
			case 'harmor':			$otdel = 1; $slot = 4; $type = 'harmor'; break;
			case 'helmets':			$otdel = 1; $slot = 9; $type = 'helmet'; break;
			case 'naruchi':			$otdel = 1; $slot = 13; $type = 'naruchi'; break;
			case 'belts':			$otdel = 1; $slot = 5; $type = 'belt'; break;
			case 'shields':			$otdel = 1; $slot = 10; $type = 'shield'; break;
			case 'clips':			$otdel = 1; $slot = 1; $type = 'clip'; break;
			case 'amulets':			$otdel = 1; $slot = 2; $type = 'amulet'; break;
			case 'rings':			$otdel = 1; $slot = 6; $type = 'ring'; break;
			case 'neutralspells':	$otdel = 2; $slot = 100; $type = 'scroll_' . $row['item_img']; break;
			case 'atdefspells':		$otdel = 2; $slot = 100; $type = 'scroll_' . $row['item_img']; break;
			case 'potions':			$otdel = 3; $slot = ''; $type = 'potion'; break;
			case 'gifts':			$otdel = 4; $slot = ''; $type = 'gift'; break;
		}
		// ----------

		//
		// Добавляем вещь в таблицу
		//
		for( $i = 0; $i < $count; $i++ )
		{
			$sql = "INSERT INTO " . ITEMS_TABLE . " " . $db->sql_build_array('INSERT', array(
				'item_user_id'						=> $userdata['user_id'],
				'item_is_equip'						=> 0,
				'item_slot'							=> $slot,
				'item_inventory_otdel'				=> $otdel,
				'item_sort_order'					=> get_max_row('item_sort_order', 'first_item', ITEMS_TABLE, '`item_user_id` = ' . $userdata['user_id']) + 1 + $i,
				'item_type'							=> $type,
				'item_artefact'						=> $row['item_artefact'],
				'item_attacks'						=> $row['item_attacks'],
				'item_name'							=> $row['item_name'],
				'item_img'							=> $row['item_img'],
				'item_align'						=> $row['item_align'],
				'item_price'						=> $row['item_price_real'],
				'item_ekrprice'						=> $row['item_ekrprice'],
				'item_weight'						=> $row['item_weight'],
				'item_current_durability'			=> 0,
				'item_max_durability'				=> $row['item_max_durability'],
				'item_application_time'				=> $row['item_application_time'],
				'item_magic_time'					=> $row['item_magic_time'],
				'item_spell_percent'				=> $row['item_spell_percent'],
				'item_start_lifetime'				=> time(),
				'item_lifetime'						=> $row['item_lifetime'],
				'item_symbols_num'					=> $row['item_symbols_num'],
				'item_req_strength'					=> $row['item_req_strength'],
				'item_req_agility'					=> $row['item_req_agility'],
				'item_req_perception'				=> $row['item_req_perception'],
				'item_req_vitality'					=> $row['item_req_vitality'],
				'item_req_intellect'				=> $row['item_req_intellect'],
				'item_req_wisdom'					=> $row['item_req_wisdom'],
				'item_req_spirituality'				=> $row['item_req_spirituality'],
				'item_req_freedom'					=> $row['item_req_freedom'],
				'item_req_freedom_of_spirit'		=> $row['item_req_freedom_of_spirit'],
				'item_req_holiness'					=> $row['item_req_holiness'],
				'item_req_level'					=> $row['item_req_level'],
				'item_req_knifes'					=> $row['item_req_knifes'],
				'item_req_axes'						=> $row['item_req_axes'],
				'item_req_clubs'					=> $row['item_req_clubs'],
				'item_req_swords'					=> $row['item_req_swords'],
				'item_req_staffs'					=> $row['item_req_staffs'],
				'item_req_magic_air'				=> $row['item_req_magic_air'],
				'item_req_magic_earth'				=> $row['item_req_magic_earth'],
				'item_req_magic_fire'				=> $row['item_req_magic_fire'],
				'item_req_magic_water'				=> $row['item_req_magic_water'],
				'item_req_magic_light'				=> $row['item_req_magic_light'],
				'item_req_magic_grey'				=> $row['item_req_magic_grey'],
				'item_req_magic_dark'				=> $row['item_req_magic_dark'],
				'item_req_current_mana'				=> $row['item_req_current_mana'],
				'item_req_inbuild_spell'			=> $row['item_req_inbuild_spell'],
				'item_strength'						=> $row['item_strength'],
				'item_agility'						=> $row['item_agility'],
				'item_perception'					=> $row['item_perception'],
				'item_intellect'					=> $row['item_intellect'],
				'item_hpspeed'						=> $row['item_hpspeed'],
				'item_manaspeed'					=> $row['item_manaspeed'],
				'item_hp'							=> $row['item_hp'],
				'item_mana'							=> $row['item_mana'],
				'item_decrease_usage_mana'			=> $row['item_decrease_usage_mana'],
				'item_plus_weight'					=> $row['item_plus_weight'],
				'item_mf_armour_head'				=> $row['item_mf_armour_head'],
				'item_mf_armour_body'				=> $row['item_mf_armour_body'],
				'item_mf_armour_waist'				=> $row['item_mf_armour_waist'],
				'item_mf_armour_leg'				=> $row['item_mf_armour_leg'],
				'item_armour_head'					=> $row['item_armour_head'],
				'item_armour_body'					=> $row['item_armour_body'],
				'item_armour_waist'					=> $row['item_armour_waist'],
				'item_armour_leg'					=> $row['item_armour_leg'],
				'item_protect_damage'				=> $row['item_protect_damage'],
				'item_protect_piercing'				=> $row['item_protect_piercing'],
				'item_protect_chopping'				=> $row['item_protect_chopping'],
				'item_protect_crushing'				=> $row['item_protect_crushing'],
				'item_protect_cutting'				=> $row['item_protect_cutting'],
				'item_protect_magic'				=> $row['item_protect_magic'],
				'item_protect_air'					=> $row['item_protect_air'],
				'item_protect_earth'				=> $row['item_protect_earth'],
				'item_protect_fire'					=> $row['item_protect_fire'],
				'item_protect_water'				=> $row['item_protect_water'],
				'item_reduce_protect_magic'			=> $row['item_reduce_protect_magic'],
				'item_reduce_protect_air'			=> $row['item_reduce_protect_air'],
				'item_reduce_protect_earth'			=> $row['item_reduce_protect_earth'],
				'item_reduce_protect_fire'			=> $row['item_reduce_protect_fire'],
				'item_reduce_protect_water'			=> $row['item_reduce_protect_water'],
				'item_mf_power_damage'				=> $row['item_mf_power_damage'],
				'item_mf_power_piercing'			=> $row['item_mf_power_piercing'],
				'item_mf_power_chopping'			=> $row['item_mf_power_chopping'],
				'item_mf_power_crushing'			=> $row['item_mf_power_crushing'],
				'item_mf_power_cutting'				=> $row['item_mf_power_cutting'],
				'item_mf_power_magic'				=> $row['item_mf_power_magic'],
				'item_mf_power_air'					=> $row['item_mf_power_air'],
				'item_mf_power_earth'				=> $row['item_mf_power_earth'],
				'item_mf_power_fire'				=> $row['item_mf_power_fire'],
				'item_mf_power_water'				=> $row['item_mf_power_water'],
				'item_mf_power_critical_hit'		=> $row['item_mf_power_critical_hit'],
				'item_mf_critical_hit'				=> $row['item_mf_critical_hit'],
				'item_mf_anticritical_hit'			=> $row['item_mf_anticritical_hit'],
				'item_mf_dodging'					=> $row['item_mf_dodging'],
				'item_mf_antidodging'				=> $row['item_mf_antidodging'],
				'item_mf_counterblow'				=> $row['item_mf_counterblow'],
				'item_mf_shield_block'				=> $row['item_mf_shield_block'],
				'item_mf_parry'						=> $row['item_mf_parry'],
				'item_mf_hit_through_armour'		=> $row['item_mf_hit_through_armour'],
				'item_knifes'						=> $row['item_knifes'],
				'item_axes'							=> $row['item_axes'],
				'item_clubs'						=> $row['item_clubs'],
				'item_swords'						=> $row['item_swords'],
				'item_staffs'						=> $row['item_staffs'],
				'item_magic_air'					=> $row['item_magic_air'],
				'item_magic_earth'					=> $row['item_magic_earth'],
				'item_magic_fire'					=> $row['item_magic_fire'],
				'item_magic_water'					=> $row['item_magic_water'],
				'item_magic_light'					=> $row['item_magic_light'],
				'item_magic_grey'					=> $row['item_magic_grey'],
				'item_magic_dark'					=> $row['item_magic_dark'],
				'item_can_repair'					=> $row['item_can_repair'],
				'item_min_hit'						=> $row['item_min_hit'],
				'item_max_hit'						=> $row['item_max_hit'],
				'item_secondhand'					=> $row['item_secondhand'],
				'item_twohand'						=> $row['item_twohand'],
				'item_ice_attacks'					=> $row['item_ice_attacks'],
				'item_fire_attacks'					=> $row['item_fire_attacks'],
				'item_electric_attacks'				=> $row['item_electric_attacks'],
				'item_piercing_attacks'				=> $row['item_piercing_attacks'],
				'item_chopping_attacks'				=> $row['item_chopping_attacks'],
				'item_crushing_attacks'				=> $row['item_crushing_attacks'],
				'item_cutting_attacks'				=> $row['item_cutting_attacks'],
				'item_piercing_armour'				=> $row['item_piercing_armour'],
				'item_chopping_armour'				=> $row['item_chopping_armour'],
				'item_crushing_armour'				=> $row['item_crushing_armour'],
				'item_cutting_armour'				=> $row['item_cutting_armour'],
				'item_set'							=> $row['item_set'],
				'item_set_num'						=> $row['item_set_num'],
				'item_inbuild_magic'				=> $row['item_inbuild_magic'],
				'item_inbuild_magic_desc'			=> $row['item_inbuild_magic_desc'],
				'item_inbuild_magic_num'			=> $row['item_inbuild_magic_num'],
				'item_inbuild_magic_time'			=> $row['item_inbuild_magic_time'],
				'item_comment'						=> $row['item_comment']));
			if( !$db->sql_query($sql) )
			{
				site_message('Не могу создать вещь...', '', __LINE__, __FILE__, $sql);
			}
		}
		// ----------

		//
		// Берем деньги за покупку
		//
		$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - " . ( $row['item_price'] * $count ) . ") WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу взять деньги за покупку...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Отдаем деньги хозяинам магазина
		//
		$sql = "UPDATE " . BANK_TABLE . " SET bank_money = (bank_money + " . ( $row['item_price'] * $count ) . ") WHERE `bank_id` = " . $config['admin_bank_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу заплатить за покупку...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Убираем вещь с прилавка
		//
		$sql = "UPDATE " . SHOP_TABLE . " SET item_count = (item_count - " . $count . "), item_sell_count = (item_sell_count + " . $count . ") WHERE `item_id` = " . $row['item_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу взять вещь с прилавка...', '', __LINE__, __FILE__, $sql);
		}
		// ----------
	}
	//
	// ---------------

	// ---------------
	// Продажа вещи
	//
	function item_sale($row, $price)
	{
		global $db, $user, $userdata;

		//
		// Удаляем вещь
		//
		$sql = "DELETE FROM " . ITEMS_TABLE . " WHERE `item_id` = " . $row['item_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу удалить вещь...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		//
		// Отдаем деньги
		//
		$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money + " . $price . ") WHERE `user_id` = " . $row['item_user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу отдать деньги...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'sell', '"' . $userdata['user_login'] . '" продал в магазин товар: "' . $row['item_name'] . '" за ' . $price . ' кр.');
	}
	//
	// ---------------
}

$user = new user_shop();

?>