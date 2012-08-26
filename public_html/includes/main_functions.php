<?php

// ---------------
// Показываем вещь в слоте
//
function show_item($userdata, $items, $slot, $type = false)
{
	global $db, $root_path;

	switch( $slot )
	{
		case 'w1': $slotname = 'серьги'; $width = 60; $height = 20; break;
		case 'w2': $slotname = 'ожерелье'; $width = 60; $height = 20; break;
		case 'w3': $slotname = 'оружие'; $width = 60; $height = 60; break;
		case 'w4':
		case 'w400': $slotname = 'броня'; $width = 60; $height = 80; break;
		case 'w5': $slotname = 'пояс'; $width = 60; $height = 40; break;
		case 'w6':
		case 'w7':
		case 'w8': $slotname = 'кольцо'; $width = 20; $height = 20; break;
		case 'w9': $slotname = 'шлем'; $width = 60; $height = 60; break;
		case 'w10': $slotname = 'щит'; $width = 60; $height = 60; break;
		case 'w11': $slotname = 'перчатки'; $width = 60; $height = 40; break;
		case 'w12': $slotname = 'обувь'; $width = 60; $height = 40; break;
		case 'w13': $slotname = 'наручи'; $width = 60; $height = 40; break;
		case 'w14': $slotname = 'правый карман'; $width = 40; $height = 20; break;
		case 'w15': $slotname = 'левый карман'; $width = 40; $height = 20; break;

		case 'w100':
		case 'w101':
		case 'w102':
		case 'w103':
		case 'w104':
		case 'w105':
		case 'w106':
		case 'w107':
		case 'w108':
		case 'w109':
		case 'w110':
		case 'w111': $slotname = ''; $width = 40; $height = 25; break;

	}

	if( $userdata['user_' . $slot] != 0 )
	{
		$n = substr($slot, 1, strlen($slot));

		//
		// Вывод характеристик вещи
		//
		$armour_head = ( $items['item_armour_head'][$n] != 0 && $items['item_mf_armour_head'][$n] != 0 ) ? "\nБроня головы: " . ( 1 + $items['item_mf_armour_head'][$n] ) . "-" . ( $items['item_mf_armour_head'][$n] + $items['item_armour_head'][$n] ) . " (" . intval($items['item_mf_armour_head'][$n]) . "+d" . intval($items['item_armour_head'][$n]) . ")" : ( ( $items['item_armour_head'][$n] != 0 ) ? "\nБроня головы: 1-" . intval($items['item_armour_head'][$n]) . " (d" . intval($items['item_armour_head'][$n]) . ")" : '');
		$armour_body = ( $items['item_armour_body'][$n] != 0 && $items['item_mf_armour_body'][$n] != 0 ) ? "\nБроня корпуса: " . ( 1 + $items['item_mf_armour_body'][$n] ) . "-" . ( $items['item_mf_armour_body'][$n] + $items['item_armour_body'][$n] ) . " (" . intval($items['item_mf_armour_body'][$n]) . "+d" . intval($items['item_armour_body'][$n]) . ")" : ( ( $items['item_armour_body'][$n] != 0 ) ? "\nБроня корпуса: 1-" . intval($items['item_armour_body'][$n]) . " (d" . intval($items['item_armour_body'][$n]) . ")" : '');
		$armour_waist = ( $items['item_armour_waist'][$n] != 0 && $items['item_mf_armour_waist'][$n] != 0 ) ? "\nБроня пояса: " . ( 1 + $items['item_mf_armour_waist'][$n] ) . "-" . ( $items['item_mf_armour_waist'][$n] + $items['item_armour_waist'][$n] ) . " (" . intval($items['item_mf_armour_waist'][$n]) . "+d" . intval($items['item_armour_waist'][$n]) . ")" : ( ( $items['item_armour_waist'][$n] != 0 ) ? "\nБроня пояса: 1-" . intval($items['item_armour_waist'][$n]) . " (d" . intval($items['item_armour_waist'][$n]) . ")" : '');
		$armour_leg = ( $items['item_armour_leg'][$n] != 0 && $items['item_mf_armour_leg'][$n] != 0 ) ? "\nБроня ног: " . ( 1 + $items['item_mf_armour_leg'][$n] ) . "-" . ( $items['item_mf_armour_leg'][$n] + $items['item_armour_leg'][$n] ) . " (" . intval($items['item_mf_armour_leg'][$n]) . "+d" . intval($items['item_armour_leg'][$n]) . ")" : ( ( $items['item_armour_leg'][$n] != 0 ) ? "\nБроня ног: 1-" . intval($items['item_armour_leg'][$n]) . " (d" . intval($items['item_armour_leg'][$n]) . ")" : '');

		$hit = ( $items['item_min_hit'][$n] != 0 && $items['item_max_hit'][$n] != 0 ) ? "\nУдар: " . intval($items['item_min_hit'][$n]) . " - " . intval($items['item_max_hit'][$n]) : '';
		$hp = ( $items['item_hp'][$n] != 0 ) ? "\nУровень жизни: " . $items['item_hp'][$n] : '';

		$etching = ( $items['item_etching'][$n] != '' ) ? "\nНа ручке выгравирована надпись: " . $items['item_etching'][$n] : '';
		// ----------

		//
		// Встройка
		//
		$inbuild_magic = '';

		if( $items['item_inbuild_magic'][$n] != '' && $items['item_inbuild_magic_desc'][$n] != '' && $items['item_inbuild_magic_num'][$n] >= 0 )
		{
			$inbuild_magic = "\nВстроена магия: " . $items['item_inbuild_magic_desc'][$n] . " / " . $items['item_inbuild_magic_num'][$n] . " шт. ";

			switch( $items['item_inbuild_magic_time'][$n] )
			{
				case 'battle': $inbuild_magic .= 'на бой'; break;
				case 'day': $inbuild_magic .= 'в сутки'; break;
				default: $inbuild_magic .= ''; break;
			}

			$inbuild_magic .= '" class="ismagic';
		}
		// ----------

		$use_magick = ( $items['item_type'][$n] == 'neutral_scroll' || $items['item_inbuild_magic'][$n] != '' ) ? ' style="cursor: hand" onclick="UseMagick(\'' . $items['item_name'][$n] . '\', \'main.php\', \'' . $items['item_img'][$n] . '\', \'\', \'' . $slot . '\', \'\')"' : '';

		//
		// Возвращаем результат
		//
		if( $type == 'battle' )
		{
			return '<img src="' . $root_path . 'i/items/' . $items['item_img'][$n] . '.gif" width="' . $width . '" height="' . $height . '" alt="' . $items['item_name'][$n] . $hit . $hp . $armour_head . $armour_body . $armour_waist . $armour_leg . '
Долговечность: ' . $items['item_current_durability'][$n] . '/' . $items['item_max_durability'][$n] . $etching . $inbuild_magic . '">';
		// ----------
		}
		elseif( $type == 'inf' )
		{
			//
			// Информация о персонаже
			//
			return "<a href=\"./../bk/enc.php?item=" . $items['item_img'][$n] . "\" target=\"_blank\"><img src=\"i/items/" . $items['item_img'][$n] . ".gif\" width=\"" . $width . "\" height=\"" . $height . "\" alt=\"" . $items['item_name'][$n] . $hit . $hp . $armour_head . $armour_body . $armour_waist . $armour_leg . "\nДолговечность: " . $items['item_current_durability'][$n] . "/" . $items['item_max_durability'][$n] . $etching . $inbuild_magic . "\" border=\"0\"></a>";
			// ----------
		}
		elseif( $type == 'setdown' )
		{
			//
			// Настройки/Инвентарь
			//
			return '<td><a href="' . append_sid($root_path . 'main.php?setdown=' . $n) . '"><img src="' . $root_path . 'i/items/' . $items['item_img'][$n] . '.gif" width="' . $width . '" height="' . $height . '" alt="Снять ' . $items['item_name'][$n] . $hit . $hp . $armour_head . $armour_body . $armour_waist . $armour_leg . '
Долговечность: ' . $items['item_current_durability'][$n] . '/' . $items['item_max_durability'][$n] . $etching . $inbuild_magic . '"></a></td>';
			// ----------
		}
		else
		{
			//
			// Прочее
			//
			return '<td><img src="' . $root_path . 'i/items/' . $items['item_img'][$n] . '.gif" width="' . $width . '" height="' . $height . '" alt="' . $items['item_name'][$n] . '' . $hit . $hp . $armour_head . $armour_body . $armour_waist . $armour_leg . '
Долговечность: ' . $items['item_current_durability'][$n] . '/' . $items['item_max_durability'][$n] . $etching . $inbuild_magic . '"' . $use_magick . '></td>';
			// ----------
		}
		// ----------
	}
	// ----------
	else
	{
		//
		// Если слот пустой...
		//
		if( $type == 'battle' )
		{
			return '<img src="i/items/' . $slot . '.gif" width="' . $width . '" height="' . $height . '" alt="Пустой слот ' . $slotname . '">';
		}
		elseif( $type == 'inf' )
		{
			return '<img src="' . $root_path . 'i/items/' . $slot . '.gif" width="' . $width . '" height="' . $height . '" alt="Пустой слот ' . $slotname . '">';
		}
		else
		{
			return '<td><img src="' . $root_path . 'i/items/' . $slot . '.gif" width="' . $width . '" height="' . $height . '" alt="Пустой слот ' . $slotname . '"></td>';
		}
		// ----------
	}
}
//
// ---------------

?>