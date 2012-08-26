<?php

class path
{
	// ---------------
	// Вывод комнат
	//
	function room($room, $message = '')
	{
		global $path, $userdata;

		switch( $room )
		{
			// ---------------
			// Залы
			//
			case '1.100.1.8.1':
			case '1.100.1.8.2':
			case '1.100.1.8.3':
			case '1.100.1.8.4':
			case '1.100.1.8.5':
			case '1.100.1.8.6':
				// Карта
				$message .= '<div style="position: relative; cursor: pointer;" id="ione"><img src="http://static.ivacuum.ru/i/images/300x225/club/navig1.jpg" alt="" border="1">';

				// Зал Паладинов
				$message .= $path->room_html('1.100.1.8.1', $userdata['user_room']);

				// Зал стихий
				$message .= $path->room_html('1.100.1.8.5', $userdata['user_room']);

				// Зал Тьмы
				$message .= $path->room_html('1.100.1.8.3', $userdata['user_room']);

				// Совет Белого Братства
				$message .= $path->room_html('1.100.1.8.2', $userdata['user_room']);

				// Царство Тьмы
				$message .= $path->room_html('1.100.1.8.4', $userdata['user_room']);

				// Бойцовский Клуб
				$message .= $path->room_html('1.100.1.9', $userdata['user_room']);

				// Комната для новичков
				$message .= $path->room_html('1.100.1.1', $userdata['user_room']);
				break;
			//
			// ---------------

			// ---------------
			// Бойцовский Клуб
			//
			case '1.100.1.9':
			case '1.100.1.10':
			case '1.100.1.11':
			case '1.100.1.12':
			case '1.100.1.13':
				// Карта
				$message .= '<div style="position: relative; cursor: pointer;" id="ione"><img src="http://static.ivacuum.ru/i/images/300x225/club/navig.jpg" alt="" border="1">';

				// Будуар
				$message .= $path->room_html('1.100.1.13', $userdata['user_room']);

				// Зал воинов
				$message .= $path->room_html('1.100.1.10', $userdata['user_room']);

				// Зал воинов 2
				$message .= $path->room_html('1.100.1.11', $userdata['user_room']);

				// Зал воинов 3
				$message .= $path->room_html('1.100.1.12', $userdata['user_room']);

				// Этаж 2
				$message .= $path->room_html('1.100.1.6.5', $userdata['user_room']);

				// Залы
				$message .= $path->room_html('1.100.1.8.6', $userdata['user_room']);

				// Центральная Площадь
				$message .= $path->room_html('1.100', $userdata['user_room']);

				// Бойцовский Клуб
				$message .= $path->room_html('1.100.1.9', $userdata['user_room']);
				break;
			//
			// ---------------
		}

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Вывод HTML-кода комнаты
	//
	function room_html($room, $user_room, $type = '')
	{
		global $path;

		$info[$room] = $path->room_info($room, $user_room);
		$type = ( $room == $user_room ) ? 'inside' : '';

		if( !$type )
		{
			switch( $room )
			{
				case '1.100':			$type = ( $user_room == '1.100.1.9' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.1':		$type = ( $user_room == '1.100.1.8.1' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.6.5':		$type = ( $user_room == '1.100.1.9' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.1':		$type = ( $user_room == '1.100.1.1' || $user_room == '1.100.1.8.2' || $user_room == '1.100.1.8.6' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.2':		$type = ( $user_room == '1.100.1.8.1' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.3':		$type = ( $user_room == '1.100.1.8.4' || $user_room == '1.100.1.8.6' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.4':		$type = ( $user_room == '1.100.1.8.3' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.5':		$type = ( $user_room == '1.100.1.8.6' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.8.6':		$type = ( $user_room == '1.100.1.9' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.9':		$type = ( $user_room == '1.100.1.8.6' || $user_room == '1.100.1.10' || $user_room == '1.100.1.11' || $user_room == '1.100.1.12' || $user_room == '1.100.1.13' ) ? 'available' : 'unavailable'; break;
				case '1.100.1.10':
				case '1.100.1.11':
				case '1.100.1.12':
				case '1.100.1.13':		$type = ( $user_room == '1.100.1.9' ) ? 'available' : 'unavailable'; break;
			}
		}

		$message = ( $type == 'available' ) ? '<div style="position: absolute; left: ' . $info[$room]['left'] . 'px; top: ' . $info[$room]['top'] . 'px; width: ' . $info[$room]['width'] . 'px; height: ' . $info[$room]['height'] . 'px; z-index: ' . $info[$room]['z-index'] . '; filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="http://static.ivacuum.ru/i/images/subimages/' . $info[$room]['image'] . '.gif" width="' . $info[$room]['width'] . '" height="' . $info[$room]['height'] . '" alt="" class="aFilter" onmouseover="imover(this)" onmouseout="imout(this); hideshow();" onclick="' . $info[$room]['onclick'] . '" onmousemove="fastshow2(\'<strong>' . $info[$room]['name'] . '</strong>\');"></div>' : ( ( $type == 'unavailable' ) ? '<div style="position: absolute; left: ' . $info[$room]['left'] . 'px; top: ' . $info[$room]['top'] . 'px; width: ' . $info[$room]['width'] . 'px; height: ' . $info[$room]['height'] . 'px; z-index: ' . $info[$room]['z-index'] . '; filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="http://static.ivacuum.ru/i/images/subimages/' . $info[$room]['image'] . '.gif" width="' . $info[$room]['width'] . '" height="' . $info[$room]['height'] . '" alt="Вход через ' . $info[$room]['enter'] . '" class="aFilter" onmouseover="imover(this)" onmouseout="imout(this); hideshow();" onclick="alert(\'Вход через ' . $info[$room]['enter'] . '\')"></div>' : ( ( $type == 'inside' ) ? '<div style="position: absolute; left: ' . $info[$room]['left'] . 'px; top: ' . $info[$room]['top'] . 'px; width: ' . $info[$room]['width'] . 'px; height: ' . $info[$room]['height'] . 'px; z-index: ' . $info[$room]['z-index'] . '; filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="http://static.ivacuum.ru/i/images/subimages/' . $info[$room]['image'] . '.gif" width="' . $info[$room]['width'] . '" height="' . $info[$room]['height'] . '" alt="" onmouseout="hideshow();" onclick=""></div>' : ''));

		$message .= ( $type == 'inside' ) ? '<div style="position: absolute; left: ' . ( $info[$room]['left'] + 56 ) . 'px; top: ' . ( $info[$room]['top'] + 30 ) . 'px; width: 16px; height: 18px; z-index: 90; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=100, Style=0);"><img src="http://static.ivacuum.ru/i/images/subimages/fl1.gif" width="16" height="18" alt="" onmouseout="hideshow();" onclick=""></div>' : '';

		return $message;
	}
	//
	// ---------------

	// ---------------
	// Информация о комнатах
	//
	function room_info($room, $user_room)
	{
		switch( $room )
		{
			// Центральная Площадь
			case '1.100':
				$info = array(
					'left'		=> '196',
					'top'		=> '148',
					'width'		=> '103',
					'height'	=> '47',
					'z-index'	=> '90',
					'image'		=> 'map_klub7',
					'onclick'	=> 'solo(\'o6\')',
					'name'		=> 'Центральная Площадь',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Комната для новичков
			case '1.100.1.1':
				$info = array(
					'left'		=> '78',
					'top'		=> '24',
					'width'		=> '76',
					'height'	=> '18',
					'z-index'	=> '90',
					'image'		=> 'map_zalu6',
					'onclick'	=> 'solo(\'o6\')',
					'name'		=> 'Комната для новичков',
					'enter'		=> 'Зал Паладинов'
				);
				break;

			// Этаж 2
			case '1.100.1.6.5':
				$info = array(
					'left'		=> '216',
					'top'		=> '41',
					'width'		=> '58',
					'height'	=> '49',
					'z-index'	=> '90',
					'image'		=> 'map_klub2',
					'onclick'	=> 'solo(\'o4\')',
					'name'		=> 'Этаж 2',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Зал Паладинов
			case '1.100.1.8.1':
				$info = array(
					'left'		=> '52',
					'top'		=> '47',
					'width'		=> '122',
					'height'	=> '31',
					'z-index'	=> '90',
					'image'		=> 'map_zalu4',
					'onclick'	=> 'solo(\'o0\')',
					'name'		=> 'Зал Паладинов',
					'enter'		=> 'Залы'
				);
				break;

			// Совет белого братства
			case '1.100.1.8.2':
				$info = array(
					'left'		=> '17',
					'top'		=> '122',
					'width'		=> '79',
					'height'	=> '32',
					'z-index'	=> '90',
					'image'		=> 'map_zalu3',
					'onclick'	=> 'solo(\'o3\')',
					'name'		=> 'Совет Белого Братства',
					'enter'		=> 'Зал Паладинов'
				);
				break;

			// Зал Тьмы
			case '1.100.1.8.3':
				$info = array(
					'left'		=> '202',
					'top'		=> '164',
					'width'		=> '122',
					'height'	=> '31',
					'z-index'	=> '90',
					'image'		=> 'map_zalu1',
					'onclick'	=> 'solo(\'o2\')',
					'name'		=> 'Зал Тьмы',
					'enter'		=> 'Залы'
				);
				break;

			// Царство Тьмы
			case '1.100.1.8.4':
				$info = array(
					'left'		=> '88',
					'top'		=> '186',
					'width'		=> '59',
					'height'	=> '29',
					'z-index'	=> '90',
					'image'		=> 'map_zalu5',
					'onclick'	=> 'solo(\'o4\')',
					'name'		=> 'Царство Тьмы',
					'enter'		=> 'Зал Тьмы'
				);
				break;

			// Зал стихий
			case '1.100.1.8.5':
				$info = array(
					'left'		=> '263',
					'top'		=> '46',
					'width'		=> '122',
					'height'	=> '31',
					'z-index'	=> '90',
					'image'		=> 'map_zalu2',
					'onclick'	=> 'solo(\'o1\')',
					'name'		=> 'Зал стихий',
					'enter'		=> 'Залы'
				);
				break;

			// Залы
			case '1.100.1.8.6':
				$info = array(
					'left'		=> '64',
					'top'		=> '114',
					'width'		=> '56',
					'height'	=> '13',
					'z-index'	=> '90',
					'image'		=> 'map_klub1',
					'onclick'	=> 'solo(\'o5\')',
					'name'		=> 'Залы',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Бойцовский Клуб
			case '1.100.1.9':
				if( $user_room == '1.100.1.8.1' || $user_room == '1.100.1.8.6' )
				{
					$info = array(
						'left'		=> '393',
						'top'		=> '170',
						'width'		=> '100',
						'height'	=> '35',
						'z-index'	=> '90',
						'image'		=> 'map_zalu7',
						'onclick'	=> 'solo(\'o5\')',
						'name'		=> 'Бойцовский Клуб',
						'enter'		=> 'Залы'
					);
				}
				else
				{
					$info = array(
						'left'		=> '184',
						'top'		=> '94',
						'width'		=> '120',
						'height'	=> '35',
						'z-index'	=> '90',
						'image'		=> 'map_bk',
						'onclick'	=> 'solo(\'o7\')',
						'name'		=> 'Бойцовский Клуб',
						'enter'		=> ''
					);
				}
				break;

			// Зал воинов
			case '1.100.1.10':
				$info = array(
					'left'		=> '59',
					'top'		=> '169',
					'width'		=> '123',
					'height'	=> '31',
					'z-index'	=> '90',
					'image'		=> 'map_klub4',
					'onclick'	=> 'solo(\'o1\')',
					'name'		=> 'Зал воинов',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Зал воинов 2
			case '1.100.1.11':
				$info = array(
					'left'		=> '312',
					'top'		=> '168',
					'width'		=> '123',
					'height'	=> '31',
					'z-index'	=> '90',
					'image'		=> 'map_klub3',
					'onclick'	=> 'solo(\'o2\')',
					'name'		=> 'Зал воинов 2',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Зал воинов 3
			case '1.100.1.12':
				$info = array(
					'left'		=> '312',
					'top'		=> '48',
					'width'		=> '123',
					'height'	=> '30',
					'z-index'	=> '90',
					'image'		=> 'map_klub5',
					'onclick'	=> 'solo(\'o3\')',
					'name'		=> 'Зал воинов 3',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;

			// Будуар
			case '1.100.1.13':
				$info = array(
					'left'		=> '52',
					'top'		=> '47',
					'width'		=> '123',
					'height'	=> '30',
					'z-index'	=> '90',
					'image'		=> 'map_klub6',
					'onclick'	=> 'solo(\'o0\')',
					'name'		=> 'Будуар',
					'enter'		=> 'Бойцовский Клуб'
				);
				break;
		}

		return $info;
	}
	//
	// ---------------
}

$path = new path();

?>