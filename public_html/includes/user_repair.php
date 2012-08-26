<?php

class user_repair extends user
{
	// ---------------
	// Подгонка вещей
	//
	function item_fit($row, $hp, $price)
	{
		global $db, $user, $userdata;

		//
		// Обновляем данные персонажа
		//
		$sql = "UPDATE " . USERS_TABLE . " SET user_money = (user_money - " . $price . ") WHERE `user_id` = " . $userdata['user_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные персонажа...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		$delimeter = ( ( $row['item_hp'] + $hp ) > 0 ) ? '+' : '-';

		//
		// Обновляем данные вещи
		//
		$sql = "UPDATE " . ITEMS_TABLE . " SET item_fit = " . $userdata['user_id'] . ", item_fit_login = '" . $userdata['user_login'] . "', item_hp = '" . $delimeter . ( $row['item_hp'] + $hp ) . "' WHERE `item_id` = " . $row['item_id'];
		if( !$db->sql_query($sql) )
		{
			site_message('Не могу обновить данные вещи...', '', __LINE__, __FILE__, $sql);
		}
		// ----------

		$userdata['user_money'] -= $price;

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], '1.9', 'fit', 'Предмет "' . $row['item_name'] . ' у "' . $userdata['user_login'] . '" подогнан за ' . $price . ' кр.');
	}
	//
	// ---------------

	function item_upgrade_artefact_preview($name, $level, $current_durability, $town)
	{
		global $userdata;

		switch( $name )
		{
			case 'ahelmet1':
				// Закрытый Шлем Развития
				switch( $level )
				{
					case '5': $message = '<td bgcolor="A5A5A5">&nbsp;</td><td align="center"><img src="i/items/ahelmet1.gif" alt="Закрытый шлем Развития"></td><td valign="top"><a href="./../bk/enc.php?item=ahelmet1" target="_blank">Закрытый шлем Развития</a>&nbsp;<img src="i/align0.gif" width="12" height="15"> (Масса: 9) <img src="i/artefact.gif" width="18" height="16" alt="Артефакт"><br><b>Цена: 300 кр.</b><br>Долговечность: ' . $current_durability . '/500<br><b>Требуется минимальное:</b><br>&bull; Сила: 25<br>&bull; Ловкость: 20<br>&bull; Выносливость: 20<br>&bull; Уровень: <b>' . ( ( $userdata['user_level'] >= ( $level + 1 ) ) ? $level + 1 : '<font color="red">' . ( $level + 1 ) . '</font>' ) . '</b><br><b>Действует на:</b><br>&bull; Ловкость: <b>+7</b><br>&bull; Интеллект: +5<br>&bull; Уровень жизни (HP): +33<br>&bull; Броня головы: <b>9</b>-<b>32</b> (<b>8</b>+d<b>24</b>)<br>&bull; Мф. против критического удара (%): +50<br>&bull; Мф. увертывания (%): +60<br><b>Свойства предмета:</b><br>&bull; Мф. против увертывания (%): +50<br>&bull; Мастерство владения топорами, секирами: +2<br>&bull; Мастерство владения мечами: +2<br><small>Сделано в ' . $town . '</small><br /><font color="brown"><small>Предмет не подлежит ремонту</small></font><br></td>'; break;
					case '6': $message = '<td bgcolor="A5A5A5">&nbsp;</td><td align="center"><img src="i/items/ahelmet1.gif" alt="Закрытый шлем Развития"></td><td valign="top"><a href="./../bk/enc.php?item=ahelmet1" target="_blank">Закрытый шлем Развития</a>&nbsp;<img src="i/align0.gif" width="12" height="15"> (Масса: 9) <img src="i/artefact.gif" width="18" height="16" alt="Артефакт"><br><b>Цена: 350 кр.</b><br>Долговечность: ' . $current_durability . '/500<br><b>Требуется минимальное:</b><br>&bull; Сила: 25<br>&bull; Ловкость: 20<br>&bull; Выносливость: 20<br>&bull; Уровень: <b>' . ( ( $userdata['user_level'] >= ( $level + 1 ) ) ? $level + 1 : '<font color="red">' . ( $level + 1 ) . '</font>' ) . '</b><br><b>Действует на:</b><br>&bull; Ловкость: +7<br>&bull; Интеллект: +5<br>&bull; Уровень жизни (HP): <b>+60</b><br>&bull; Броня головы: <b>11</b>-<b>40</b> (<b>10</b>+d<b>30</b>)<br>&bull; Защита от магии: <b>+10</b><br>&bull; Мф. против критического удара (%): +50<br>&bull; Мф. увертывания (%): <b>+70</b><br><b>Свойства предмета:</b><br>&bull; Мф. против увертывания (%): +50<br>&bull; Мастерство владения топорами, секирами: +2<br>&bull; Мастерство владения мечами: +2<br><small>Сделано в ' . $town . '</small><br /><font color="brown"><small>Предмет не подлежит ремонту</small></font><br></td>'; break;
					case '7': $message = '<td bgcolor="A5A5A5">&nbsp;</td><td align="center"><img src="i/items/ahelmet1.gif" alt="Закрытый шлем Развития"></td><td valign="top"><a href="./../bk/enc.php?item=ahelmet1" target="_blank">Закрытый шлем Развития</a>&nbsp;<img src="i/align0.gif" width="12" height="15"> (Масса: 9) <img src="i/artefact.gif" width="18" height="16" alt="Артефакт"><br><b>Цена: 400 кр.</b><br>Долговечность: ' . $current_durability . '/500<br><b>Требуется минимальное:</b><br>&bull; Сила: <b>' . ( ( $userdata['user_strength'] >= 30 ) ? '30' : '<font color="red">30</font>' ) . '</b><br>&bull; Ловкость: 20<br>&bull; Выносливость: <b>' . ( ( $userdata['user_vitality'] >= 30 ) ? '30' : '<font color="red">30</font>' ) . '</b><br>&bull; Уровень: <b>' . ( ( $userdata['user_level'] >= ( $level + 1 ) ) ? $level + 1 : '<font color="red">' . ( $level + 1 ) . '</font>' ) . '</b><br><b>Действует на:</b><br>&bull; Ловкость: +7<br>&bull; Интеллект: +5<br>&bull; Уровень жизни (HP): +60<br>&bull; Броня головы: <b>13</b>-<b>48</b> (<b>12</b>+d<b>36</b>)<br>&bull; Защита от магии: <b>+15</b><br>&bull; Мф. против критического удара (%): <b>+65</b><br>&bull; Мф. увертывания (%): <b>+90</b><br><b>Свойства предмета:</b><br>&bull; Мф. против увертывания (%): +50<br>&bull; Мастерство владения топорами, секирами: +2<br>&bull; Мастерство владения мечами: +2<br><small>Сделано в ' . $town . '</small><br /><font color="brown"><small>Предмет не подлежит ремонту</small></font><br></td>'; break;
					case '8': $message = '<td bgcolor="A5A5A5">&nbsp;</td><td align="center"><img src="i/items/ahelmet1.gif" alt="Закрытый шлем Развития"></td><td valign="top"><a href="./../bk/enc.php?item=ahelmet1" target="_blank">Закрытый шлем Развития</a>&nbsp;<img src="i/align0.gif" width="12" height="15"> (Масса: 9) <img src="i/artefact.gif" width="18" height="16" alt="Артефакт"><br><b>Цена: 500 кр.</b><br>Долговечность: ' . $current_durability . '/500<br><b>Требуется минимальное:</b><br>&bull; Сила: <b>' . ( ( $userdata['user_strength'] >= 35 ) ? '35' : '<font color="red">35</font>' ) . '</b><br>&bull; Ловкость: 20<br>&bull; Выносливость: <b>' . ( ( $userdata['user_vitality'] >= 35 ) ? '35' : '<font color="red">35</font>' ) . '</b><br>&bull; Уровень: <b>' . ( ( $userdata['user_level'] >= ( $level + 1 ) ) ? $level + 1 : '<font color="red">' . ( $level + 1 ) . '</font>' ) . '</b><br><b>Действует на:</b><br>&bull; Ловкость: +7<br>&bull; Интеллект: +5<br>&bull; Уровень жизни (HP): +60<br>&bull; Броня головы: <b>15</b>-<b>56</b> (<b>14</b>+d<b>42</b>)<br>&bull; Защита от урона: <b>+15</b><br>&bull; Защита от магии: +15<br>&bull; Мф. против критического удара (%): <b>+85</b><br>&bull; Мф. увертывания (%): +90<br>&bull; Мф. контрудара (%): <b>+5</b><br><b>Свойства предмета:</b><br>&bull; Мф. против увертывания (%): <b>+75</b><br>&bull; Мастерство владения топорами, секирами: +2<br>&bull; Мастерство владения мечами: +2<br><small>Сделано в ' . $town . '</small><br /><font color="brown"><small>Предмет не подлежит ремонту</small></font><br></td>'; break;
					case '9': $message = '<td bgcolor="A5A5A5">&nbsp;</td><td align="center"><img src="i/items/ahelmet1.gif" alt="Закрытый шлем Развития"></td><td valign="top"><a href="./../bk/enc.php?item=ahelmet1" target="_blank">Закрытый шлем Развития</a>&nbsp;<img src="i/align0.gif" width="12" height="15"> (Масса: 9) <img src="i/artefact.gif" width="18" height="16" alt="Артефакт"><br><b>Цена: 680 кр.</b><br>Долговечность: ' . $current_durability . '/500<br><b>Требуется минимальное:</b><br>&bull; Сила: <b>' . ( ( $userdata['user_strength'] >= 40 ) ? '40' : '<font color="red">40</font>' ) . '</b><br>&bull; Ловкость: 20<br>&bull; Выносливость: <b>' . ( ( $userdata['user_vitality'] >= 40 ) ? '40' : '<font color="red">40</font>' ) . '</b><br>&bull; Уровень: <b>' . ( ( $userdata['user_level'] >= ( $level + 1 ) ) ? $level + 1 : '<font color="red">' . ( $level + 1 ) . '</font>' ) . '</b><br><b>Действует на:</b><br>&bull; Ловкость: <b>+10</b><br>&bull; Интеллект: <b>+10</b><br>&bull; Уровень жизни (HP): <b>+90</b><br>&bull; Броня головы: <b>17</b>-<b>64</b> (<b>16</b>+d<b>48</b>)<br>&bull; Защита от урона: <b>+25</b><br>&bull; Защита от магии: <b>+20</b><br>&bull; Мф. против критического удара (%): <b>+100</b><br>&bull; Мф. увертывания (%): <b>+120</b><br>&bull; Мф. контрудара (%): <b>+10</b><br><b>Свойства предмета:</b><br>&bull; Мф. против увертывания (%): <b>+85</b><br>&bull; Мастерство владения топорами, секирами: <b>+4</b><br>&bull; Мастерство владения мечами: <b>+4</b><br><small>Сделано в ' . $town . '</small><br /><font color="brown"><small>Предмет не подлежит ремонту</small></font><br></td>'; break;
					default: $message = ''; break;
				}
				break;
			default: $message = ''; break;
		}

		return $message;
	}
}

$user = new user_repair();

?>