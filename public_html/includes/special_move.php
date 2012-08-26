<?php

class special_move
{
	// ---------------
	// Возвращение описания приёма
	//
	function description($special_name, $user_level = 6, $br = false)
	{
		$description = array();

		$description['block_absolute'] = 'Следующие повреждения от ударов в течение одного' . ( ( $br ) ? '<br>' : ' ' ) . 'обмена ударами или от магии сводятся к 1.';
		$description['block_activeshield'] = 'Следующий удар по вам нанесет лишь половину повреждений';
		$description['block_addchange'] = 'Дополнительная смена противника';
		$description['block_circleshield'] = 'Урон наносимый вашей команде уменьшен на 1 до конца боя';
		$description['block_fullshield'] = 'Следующий удар противника нанесет не более 1 повреждения';
		$description['counter_bladedance'] = 'Вы увернетесь от следующего направленного' . ( ( $br ) ? '<br>' : ' ' ) . 'в вас удара и нанесете контрудар';
		$description['counter_winddance'] = 'Вы увернетесь от следующего' . ( ( $br ) ? '<br>' : ' ' ) . 'направленного в вас удара';
		$description['hit_luck'] = 'Следующий удар наносит на ' . ( $user_level * 4 ) . ' ед. урона больше';
		$description['hit_natisk'] = 'Урон наносимый вашей командой увеличен на 1 до конца боя';
		$description['hit_overhit'] = 'Мгновенно наносит противнику ' . ( $user_level * 5 ) . ' ед. урона';
		$description['hit_resolve'] = 'Просмотр всех активных приемов на текущей цели.';
		$description['hit_strong'] = 'Следующий удар наносит на ' . ( $user_level * 2 ) . ' ед. урона больше';
		$description['hit_willpower'] = 'Если ваши хиты красные, вы восстанавливаете по 30HP';
		$description['krit_blindluck'] = 'Следующий удар будет критическим';
		$description['krit_crush'] = 'Противник мгновенно получает от вас безответный неблокируемый удар.';
		$description['krit_wildluck'] = 'Следующий критический удар наносит' . ( ( $br ) ? '<br>' : ' ' ) . 'максимальные повреждения';
		$description['multi_agressiveshield'] = 'Следующий удар противника нанесет не более 1 повреждения,' . ( ( $br ) ? '<br>' : ' ' ) . 'противник получает ' . ( $user_level * 2 ) . ' ед. урона';
		$description['multi_blockchanges'] = 'Количество смен противника у противника устанавливается на 1.';
		$description['multi_cowardshift'] = 'Следующий удар противника наносится по нему, вместо вас.';
		$description['multi_doom'] = 'От следующего вашего удара невозможно увернуться,' . ( ( $br ) ? '<br>' : ' ' ) . 'его невозможно парировать или заблокировать щитом,' . ( ( $br ) ? '<br>' : ' ' ) . 'но возможно просто заблокировать.';
		$description['multi_followme'] = 'Следующей целью текущего бойца становитесь вы.' . ( ( $br ) ? '<br>' : ' ' ) . 'Боец не в праве менять противника, до тех пор,' . ( ( $br ) ? '<br>' : ' ' ) . 'пока не разменяется ударом с вами.';
		$description['multi_hiddendodge'] = 'Вы уворачиваетесь от следующего удара во вам и наносите контрудар.';
		$description['multi_hiddenpower'] = 'Следующий ваш удар - критический.';
		$description['multi_resolvetactic'] = 'Отменяет все активные приемы на противнике.';
		$description['multi_skiparmor'] = 'Следующий ваш удар игнорирует броню противника.';
		$description['multi_speedup'] = 'Украсть все активные приемы на противнике.';
		$description['parry_prediction'] = 'Следующий удар противника парируется';
		$description['parry_secondlife'] = 'Следующий удар противника - парируется,' . ( ( $br ) ? '<br>' : ' ' ) . 'за каждый уровень противника, чей удар' . ( ( $br ) ? '<br>' : ' ' ) . 'вы парировали, вы получаете 5 ХП';

		return $description[$special_name];
	}
	//
	// ---------------

	// ---------------
	// Возвращение HTML-кода для выбранного спец-приёма
	//
	function html($special_name, $userdata, $page = false)
	{
		global $special_move, $special_selected, $userdata;

		// Определяем переменные
		$active = true;
		$html = '';

		if( $page == 'battle' )
		{
			// На странице боя...
			$requirements = $special_move->requirements($special_name, 'array');

			$active = ( $userdata['user_count_hit'] >= $requirements[0] && $userdata['user_count_critical_hit'] >= $requirements[1] && $userdata['user_count_counterblow'] >= $requirements[2] && $userdata['user_count_block'] >= $requirements[3] && $userdata['user_count_parry'] >= $requirements[4] ) ? true : false;
			$html .= ( $active ) ? '<a href="battle.php?special=' . $special_name . '">' : '';
		}
		elseif( $page == 'skills' )
		{
			// На странице «Умения»...
			$active = ( !in_array($special_name, $special_selected) ) ? true : false;
			$html .= ( $active ) ? '<a href="main.php?set_abil=' . $special_name . '">' : '';
		}

		// Сама картинка с описанием
		$html .= '<img style="' . ( ( $active ) ? 'cursor: hand' : 'filter: gray(), Alpha(Opacity=\'70\');' ) . '" width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/' . $special_name . '.gif" onmouseout="hideshow();" onmousemove=\'fastshow("<b>' . $special_move->name($special_name) . '</b><br>' . $special_move->requirements($special_name) . '<br><br>' . $special_move->description($special_name, $userdata['user_level'], true) . '")\'>';

		if( $page == 'battle' )
		{
			$html .= ( $active ) ? '</a>' : '';
		}
		elseif( $page == 'skills' )
		{
			$html .= ( $active ) ? '</a>' : '';
		}

		return $html;
	}
	//
	// ---------------

	// ---------------
	// Проверка наличия спец-приёма
	//
	function is_exist($special_name)
	{
		if( $special_name == 'hit_strong' || $special_name == 'krit_wildluck' || $special_name == 'counter_winddance' || $special_name == 'block_activeshield' || $special_name == 'parry_prediction' || $special_name == 'hit_luck' || $special_name == 'krit_blindluck' || $special_name == 'counter_bladedance' || $special_name == 'block_fullshield' || $special_name == 'parry_secondlife' || $special_name == 'multi_doom' || $special_name == 'multi_skiparmor' || $special_name == 'block_absolute' || $special_name == 'multi_hiddendodge' || $special_name == 'multi_hiddenpower' || $special_name == 'krit_crush' || $special_name == 'multi_cowardshift' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	//
	// ---------------

	// ---------------
	// Возвращение названия приёма
	//
	function name($special_name)
	{
		$name['block_absolute'] = 'Абсолютная защита';
		$name['block_activeshield'] = 'Активная защита';
		$name['block_addchange'] = 'Выбор противника';
		$name['block_circleshield'] = 'Круговая защита';
		$name['block_fullshield'] = 'Полная защита';
		$name['counter_bladedance'] = 'Танец лезвий';
		$name['counter_winddance'] = 'Танец ветра';
		$name['hit_luck'] = 'Удачный удар';
		$name['hit_natisk'] = 'Натиск';
		$name['hit_overhit'] = 'Подлый удар';
		$name['hit_resolve'] = 'Разведка боем';
		$name['hit_strong'] = 'Сильный удар';
		$name['hit_willpower'] = 'Воля к победе';
		$name['krit_blindluck'] = 'Слепая удача';
		$name['krit_crush'] = 'Сокрушающий удар';
		$name['krit_wildluck'] = 'Дикая удача';
		$name['multi_agressiveshield'] = 'Агрессивная защита';
		$name['multi_blockchanges'] = 'Ограничить маневр';
		$name['multi_cowardshift'] = 'Коварный уход';
		$name['multi_doom'] = 'Обреченность';
		$name['multi_followme'] = 'Преследование';
		$name['multi_hiddendodge'] = 'Скрытая ловкость';
		$name['multi_hiddenpower'] = 'Скрытая сила';
		$name['multi_resolvetactic'] = 'Разгадать тактику';
		$name['multi_skiparmor'] = 'Точный удар';
		$name['multi_speedup'] = 'Ставка на опережение';
		$name['parry_prediction'] = 'Предвидение';
		$name['parry_secondlife'] = 'Второе дыхание';

		return $name[$special_name];
	}
	//
	// ---------------

	// ---------------
	// Возвращение требований приёма
	//
	function requirements($special_name, $type = 'text')
	{
		if( $type == 'array' )
		{
			$requirements['block_absolute'] = array(0, 0, 0, 7, 0);
			$requirements['block_activeshield'] = array(0, 0, 0, 3, 0);
			$requirements['block_addchange'] = array(0, 0, 0, 1, 0);
			$requirements['block_circleshield'] = array(0, 0, 0, 99, 0);
			$requirements['block_fullshield'] = array(0, 0, 0, 5, 0);
			$requirements['counter_bladedance'] = array(0, 0, 5, 0, 0);
			$requirements['counter_winddance'] = array(0, 0, 3, 0, 0);
			$requirements['hit_luck'] = array(5, 0, 0, 0, 0);
			$requirements['hit_natisk'] = array(99, 0, 0, 0, 0);
			$requirements['hit_overhit'] = array(7, 0, 0, 0, 0);
			$requirements['hit_resolve'] = array(1, 0, 0, 0, 0);
			$requirements['hit_strong'] = array(3, 0, 0, 0, 0);
			$requirements['hit_willpower'] = array(5, 0, 0, 0, 0);
			$requirements['krit_blindluck'] = array(0, 5, 0, 0, 0);
			$requirements['krit_crush'] = array(0, 10, 0, 0, 0);
			$requirements['krit_wildluck'] = array(0, 3, 0, 0, 0);
			$requirements['multi_agressiveshield'] = array(2, 0, 0, 5, 0);
			$requirements['multi_blockchanges'] = array(1, 0, 0, 2, 0);
			$requirements['multi_cowardshift'] = array(0, 0, 5, 2, 0);
			$requirements['multi_doom'] = array(4, 2, 0, 0, 0);
			$requirements['multi_followme'] = array(3, 0, 0, 2, 0);
			$requirements['multi_hiddendodge'] = array(0, 3, 0, 4, 0);
			$requirements['multi_hiddenpower'] = array(0, 0, 3, 4, 0);
			$requirements['multi_resolvetactic'] = array(1, 0, 0, 4, 0);
			$requirements['multi_skiparmor'] = array(5, 0, 0, 0, 2);
			$requirements['multi_speedup'] = array(1, 1, 1, 1, 1);
			$requirements['parry_prediction'] = array(0, 0, 0, 0, 3);
			$requirements['parry_secondlife'] = array(0, 0, 0, 0, 5);
		}
		elseif( $type == 'text' )
		{
			$hit_img = '<img width=8 height=8 src=\"http://static.ivacuum.ru/i/misc/micro/hit.gif\">';
			$krit_img = '<img width=7 height=8 src=\"http://static.ivacuum.ru/i/misc/micro/krit.gif\">';
			$counter_img = '<img width=8 height=8 src=\"http://static.ivacuum.ru/i/misc/micro/counter.gif\">';
			$block_img = '<img width=8 height=8 src=\"http://static.ivacuum.ru/i/misc/micro/block.gif\">';
			$parry_img = '<img width=8 height=8 src=\"http://static.ivacuum.ru/i/misc/micro/parry.gif\">';

			$requirements['block_absolute'] = $block_img . ' 7&nbsp;&nbsp;';
			$requirements['block_activeshield'] = $block_img . ' 3&nbsp;&nbsp;';
			$requirements['block_addchange'] = $block_img . ' 1&nbsp;&nbsp;';
			$requirements['block_circleshield'] = $block_img . ' ?&nbsp;&nbsp;';
			$requirements['block_fullshield'] = $block_img . ' 5&nbsp;&nbsp;';
			$requirements['counter_bladedance'] = $counter_img . ' 5&nbsp;&nbsp;';
			$requirements['counter_winddance'] = $counter_img . ' 3&nbsp;&nbsp;';
			$requirements['hit_luck'] = $hit_img . ' 5&nbsp;&nbsp;';
			$requirements['hit_natisk'] = $hit_img . ' ?&nbsp;&nbsp;';
			$requirements['hit_overhit'] = $hit_img . ' 7&nbsp;&nbsp;';
			$requirements['hit_resolve'] = $hit_img . ' 1&nbsp;&nbsp;';
			$requirements['hit_strong'] = $hit_img . ' 3&nbsp;&nbsp;';
			$requirements['hit_willpower'] = $hit_img . ' 5&nbsp;&nbsp;';
			$requirements['krit_blindluck'] = $krit_img . ' 5&nbsp;&nbsp;';
			$requirements['krit_crush'] = $krit_img . ' 10&nbsp;&nbsp;';
			$requirements['krit_wildluck'] = $krit_img . ' 3&nbsp;&nbsp;';
			$requirements['multi_agressiveshield'] = $hit_img . ' 2&nbsp;&nbsp;' . $block_img . ' 5&nbsp;&nbsp;';
			$requirements['multi_blockchanges'] = $hit_img . ' 1&nbsp;&nbsp;' . $block_img . ' 2&nbsp;&nbsp;';
			$requirements['multi_cowardshift'] = $counter_img . ' 5&nbsp;&nbsp;' . $block_img . ' 2&nbsp;&nbsp;';
			$requirements['multi_doom'] = $hit_img . ' 4&nbsp;&nbsp;' . $krit_img . ' 2&nbsp;&nbsp;';
			$requirements['multi_followme'] = $hit_img . ' 3&nbsp;&nbsp;' . $block_img . ' 2&nbsp;&nbsp;';
			$requirements['multi_hiddendodge'] = $krit_img . ' 3&nbsp;&nbsp;' . $block_img . ' 4&nbsp;&nbsp;';
			$requirements['multi_hiddenpower'] = $counter_img . ' 3&nbsp;&nbsp;' . $block_img . ' 4&nbsp;&nbsp;';
			$requirements['multi_resolvetactic'] = $hit_img . ' 1&nbsp;&nbsp;' . $block_img . ' 4&nbsp;&nbsp;';
			$requirements['multi_skiparmor'] = $hit_img . ' 5&nbsp;&nbsp;' . $parry_img . ' 2&nbsp;&nbsp;';
			$requirements['multi_speedup'] = $hit_img . ' 1&nbsp;&nbsp;' . $krit_img . ' 1&nbsp;&nbsp;' . $counter_img . ' 1&nbsp;&nbsp;' . $block_img . ' 1&nbsp;&nbsp;' . $parry_img . ' 1&nbsp;&nbsp;';
			$requirements['parry_prediction'] = $parry_img . ' 3&nbsp;&nbsp;';
			$requirements['parry_secondlife'] = $parry_img . ' 5&nbsp;&nbsp;';
		}

		return $requirements[$special_name];
	}
	//
	// ---------------
}

$special_move = new special_move();

?>