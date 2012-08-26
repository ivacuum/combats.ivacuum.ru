<?php

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

include($root_path . 'includes/special_move.php');
include($root_path . 'includes/user_skills.php');

$current_otdel = 5;
$spc_name = substr($set_special, 0, -1);
$special_selected = explode(',', $userdata['user_special_selected']);

//
// Увеличение способностей
//
if( $upr == 'strength' || $upr == 'agility' || $upr == 'perception' || $upr == 'vitality' || $upr == 'intellect' || $upr == 'wisdom' || $upr == 'spirituality' || $upr == 'freedom' || $upr == 'freedom_of_spirit' || $upr == 'holiness' )
{
	if( $userdata['user_free_upr'] > 0 )
	{
		$user->obtain_status($userdata, '', 'set_skills');
		$userdata = $user->stat_up($upr);
		$user->obtain_status($userdata);

		// Описание повышенного стата
		switch( $upr )
		{
			case 'strength':			$stat = 'Сила'; break;
			case 'agility':				$stat = 'Ловкость'; break;
			case 'perception':			$stat = 'Интуиция'; break;
			case 'vitality':			$stat = 'Выносливость'; break;
			case 'intellect':			$stat = 'Интеллект'; break;
			case 'wisdom':				$stat = 'Мудрость'; break;
			case 'spirituality':		$stat = 'Духовность'; break;
			case 'freedom':				$stat = 'Воля'; break;
			case 'freedom_of_spirit':	$stat = 'Свобода духа'; break;
			case 'holiness':			$stat = 'Божественность'; break;
		}

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], 'admin', 'skillup', $user->drwfl($userdata) . ' повысил способность "' . $stat . '"');

		$message = 'Увеличение способности "<b>' . $stat . '</b>" произведено удачно';
	}
}
// ----------

//
// Увеличение умений
//
if( $upr == 'knifes' || $upr == 'axes' || $upr == 'clubs' || $upr == 'swords' || $upr == 'staffs' || $upr == 'magic_air' || $upr == 'magic_earth' || $upr == 'magic_fire' || $upr == 'magic_water' || $upr == 'magic_light' || $upr == 'magic_grey' || $upr == 'magic_dark' )
{
	if( $userdata['user_free_skills'] > 0 )
	{
		if( $userdata['user_' . $upr] < 5 )
		{
			$userdata = $user->skill_up($upr);

			// Описание повышенного умения
			switch( $upr )
			{
				case 'knifes':			$skill = 'кастетами, ножами'; break;
				case 'axes':			$skill = 'топорами, секирами'; break;
				case 'clubs':			$skill = 'дубинами, булавами'; break;
				case 'swords':			$skill = 'мечами'; break;
				case 'staffs':			$skill = 'магическими посохами'; break;
				case 'magic_air':		$skill = 'стихией воздуха'; break;
				case 'magic_earth':		$skill = 'стихией земли'; break;
				case 'magic_fire':		$skill = 'стихией огня'; break;
				case 'magic_water':		$skill = 'стихией воды'; break;
				case 'magic_light':		$skill = 'магией света'; break;
				case 'magic_grey':		$skill = 'серой магией'; break;
				case 'magic_dark':		$skill = 'магией тьмы'; break;
			}

			// Запись в личное дело
			$user->add_admin_log_message($userdata['user_id'], 'admin', 'skillup', $user->drwfl($userdata) . ' повысил "Мастерство владения ' . $skill . '"');

			$current_otdel = ( substr($upr, 0, 5) == 'magic' ) ? 2 : 1;
			$message = 'Увеличение способности "<b>Мастерство владения ' . $skill . '</b>" произведено удачно';
		}
		else
		{
			$message = 'Достигнут максимум мастерства';
		}
	}
}
// ----------

if( $clear_abil )
{
	// Удаление выбранного спец-приёма
	$user->special_clear($clear_abil);

	$special_selected = explode(',', $userdata['user_special_selected']);

	$current_otdel = 4;
}
elseif( $spc_name == 'decrease_transfers_price' || $spc_name == 'decrease_injury' || $spc_name == 'homeworld_time' || $spc_name == 'increase_experience' || $spc_name == 'increase_friends' || $spc_name == 'increase_hobby' || $spc_name == 'max_mass' || $spc_name == 'transfers' || $spc_name == 'hpspeed' || $spc_name == 'manaspeed' )
{
	if( $userdata['user_free_spc'] > 0 )
	{
		// Понижаем скорость восстановления
		$userdata['user_hpspeed'] /= ( $config['fast_game'] && $userdata['user_bot'] ) ? 250 : ( ( $userdata['user_bot'] ) ? 10 : 1);
		$userdata['user_hpspeed'] /= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : ( ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1);

		$user->obtain_status($userdata, '', 'set_skills');
		$userdata = $user->spc_up($set_special);
		$user->obtain_status($userdata);

		// Увеличиваем скорость восстановления
		$userdata['user_hpspeed'] *= ( $config['fast_game'] && $userdata['user_bot'] ) ? 250 : ( ( $userdata['user_bot'] ) ? 10 : 1);
		$userdata['user_hpspeed'] *= ( $userdata['user_level'] == 0 || $userdata['user_level'] == 1 ) ? 3 : ( ( $userdata['user_level'] == 2 || $userdata['user_level'] == 3 ) ? 2 : 1);

		// Запись в личное дело
		$user->add_admin_log_message($userdata['user_id'], 'admin', 'spcup', $user->drwfl($userdata) . ' выбрал особенность "' . $user->spc_name($spc_name) . ( ( substr($set_special, -1) > 1 ) ? '-' . substr($set_special, -1) : '' ) . '"');

		$current_otdel = 3;
		$message = 'Вы выбрали особенность "<b>' . $user->spc_name($spc_name) . ( ( substr($set_special, -1) > 1 ) ? '-' . substr($set_special, -1) : '' ) . '</b>"';
	}
}
elseif( $set_abil == 'block_absolute' || $set_abil == 'block_activeshield' || $set_abil == 'block_addchange' || $set_abil == 'block_circleshield' || $set_abil == 'block_fullshield' || $set_abil == 'counter_bladedance' || $set_abil == 'counter_winddance' || $set_abil == 'hit_luck' || $set_abil == 'hit_natisk' || $set_abil == 'hit_overhit' || $set_abil == 'hit_resolve' || $set_abil == 'hit_strong' || $set_abil == 'hit_willpower' || $set_abil == 'krit_blindluck' || $set_abil == 'krit_crush' || $set_abil == 'krit_wildluck' || $set_abil == 'multi_agressiveshield' || $set_abil == 'multi_blockchanges' || $set_abil == 'multi_cowardshift' || $set_abil == 'multi_doom' || $set_abil == 'multi_followme' || $set_abil == 'multi_hiddendodge' || $set_abil == 'multi_hiddenpower' || $set_abil == 'multi_resolvetactic' || $set_abil == 'multi_skiparmor' || $set_abil == 'multi_speedup' || $set_abil == 'parry_prediction' || $set_abil == 'parry_secondlife' )
{
	$message = $user->special_select($set_abil);

	$special_selected = explode(',', $userdata['user_special_selected']);

	$current_otdel = 4;
}

//
// Способности
//
$stats_name = array('Сила', 'Ловкость', 'Интуиция', 'Выносливость');
$stats_href = array('strength', 'agility', 'perception', 'vitality');
$stats_but_names = array('Силу', 'Ловкость', 'Интуицию', 'Выносливость');

if( $userdata['user_level'] >= 4 )
{
	$stats_name[] = 'Интеллект';
	$stats_href[] = 'intellect';
	$stats_but_names[] = 'Интеллект';
}
if( $userdata['user_level'] >= 7 )
{
	$stats_name[] = 'Мудрость';
	$stats_href[] = 'wisdom';
	$stats_but_names[] = 'Мудрость';
}
if( $userdata['user_level'] >= 10 )
{
	$stats_name[] = 'Духовность';
	$stats_href[] = 'spirituality';
	$stats_but_names[] = 'Духовность';
}
if( $userdata['user_level'] >= 13 )
{
	$stats_name[] = 'Воля';
	$stats_href[] = 'freedom';
	$stats_but_names[] = 'Волю';
}
if( $userdata['user_level'] >= 16 )
{
	$stats_name[] = 'Свобода духа';
	$stats_href[] = 'freedom_of_spirit';
	$stats_but_names[] = 'Свободу духа';
}
if( $userdata['user_level'] >= 19 )
{
	$stats_name[] = 'Божественность';
	$stats_href[] = 'holiness';
	$stats_but_names[] = 'Божественность';
}
// ----------

//
// Умения
//
$skills_name = array('кастетами, ножами', 'топорами, секирами', 'дубинами, булавами', 'мечами', 'магическими посохами');
$skills_href = array('knifes', 'axes', 'clubs', 'swords', 'staffs');
// ----------

//
// Магические умения
//
$magic_skills_name = array('стихией Огня', 'стихией Воды', 'стихией Воздуха', 'стихией Земли', 'магией Света', 'серой магией', 'магией Тьмы');
$magic_skills_href = array('magic_fire', 'magic_water', 'magic_air', 'magic_earth', 'magic_light', 'magic_grey', 'magic_dark');
// ----------

include($root_path . 'includes/page_header.php');

$template->set_filenames(array(
	'body'	=> 'main_skills.html')
);

$template->assign_vars(array(
	'BONUSES'							=> $user->obtain_bonuses(),
	'CURRENT_OTDEL'						=> $current_otdel,
	'DRWFL'								=> $user->drwfl($userdata),
	'FREE_SKILLS'						=> ( $userdata['user_free_skills'] > 0 ) ? '&nbsp;Свободных умений: ' . $userdata['user_free_skills'] . '<br />' : '',
	'FREE_SPC'							=> ( $userdata['user_free_spc'] > 0 ) ? '&nbsp;Свободных особенностей: ' . $userdata['user_free_spc'] . '<br />' : '',
	'FREE_UPR'							=> ( $userdata['user_free_upr'] > 0 ) ? '&nbsp;Возможных увеличений: ' . $userdata['user_free_upr'] . '<br />' : '',
	'MESSAGE'							=> ( $message ) ? '&nbsp; &nbsp;<font color="red">' . $message . '</font><br />' : '',
	'SELECTED_SPECIALS'					=> ( $userdata['user_spc'] ) ? '<b>Выбранные особенности:</b><br />' . $user->spc_selected() : '',
	
	'SPC_DECREASE_TRANSFERS_PRICE'		=> $user->spc_show('decrease_transfers_price'),
	'SPC_DECREASE_INJURY'				=> $user->spc_show('decrease_injury'),
	'SPC_HOMEWORLD_TIME'				=> $user->spc_show('homeworld_time'),
	'SPC_INCREASE_EXPERIENCE'			=> $user->spc_show('increase_experience'),
	'SPC_INCREASE_FRIENDS'				=> $user->spc_show('increase_friends'),
	'SPC_INCREASE_HOBBY'				=> $user->spc_show('increase_hobby'),
	'SPC_MAX_MASS'						=> $user->spc_show('max_mass'),
	'SPC_TRANSFERS'						=> $user->spc_show('transfers'),
	'SPC_HPSPEED'						=> $user->spc_show('hpspeed'),
	'SPC_MANASPEED'						=> $user->spc_show('manaspeed'),

	'SPECIAL_SELECTED_1'				=> ( $special_selected[0] ) ? '<a href="main.php?clear_abil=1">' . $special_move->html($special_selected[0], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_2'				=> ( isset($special_selected[1]) ) ? '<a href="main.php?clear_abil=2">' . $special_move->html($special_selected[1], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_3'				=> ( isset($special_selected[2]) ) ? '<a href="main.php?clear_abil=3">' . $special_move->html($special_selected[2], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_4'				=> ( isset($special_selected[3]) ) ? '<a href="main.php?clear_abil=4">' . $special_move->html($special_selected[3], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_5'				=> ( isset($special_selected[4]) ) ? '<a href="main.php?clear_abil=5">' . $special_move->html($special_selected[4], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_6'				=> ( isset($special_selected[5]) ) ? '<a href="main.php?clear_abil=6">' . $special_move->html($special_selected[5], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_7'				=> ( isset($special_selected[6]) ) ? '<a href="main.php?clear_abil=7">' . $special_move->html($special_selected[6], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_8'				=> ( isset($special_selected[7]) ) ? '<a href="main.php?clear_abil=8">' . $special_move->html($special_selected[7], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_9'				=> ( isset($special_selected[8]) ) ? '<a href="main.php?clear_abil=9">' . $special_move->html($special_selected[8], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',
	'SPECIAL_SELECTED_10'				=> ( isset($special_selected[9]) ) ? '<a href="main.php?clear_abil=10">' . $special_move->html($special_selected[9], $userdata) . '</a>' : '<img width="40" height="25" src="http://static.ivacuum.ru/i/misc/icons/clear.gif">',

	'HIT_STRONG'						=> $special_move->html('hit_strong', $userdata, 'skills'),
	'KRIT_WILDLUCK'						=> $special_move->html('krit_wildluck', $userdata, 'skills'),
	'COUNTER_WINDDANCE'					=> $special_move->html('counter_winddance', $userdata, 'skills'),
	'BLOCK_ACTIVESHIELD'				=> $special_move->html('block_activeshield', $userdata, 'skills'),
	'PARRY_PREDICTION'					=> $special_move->html('parry_prediction', $userdata, 'skills'),
	'HIT_LUCK'							=> $special_move->html('hit_luck', $userdata, 'skills'),
	'KRIT_BLINDLUCK'					=> $special_move->html('krit_blindluck', $userdata, 'skills'),
	'COUNTER_BLADEDANCE'				=> $special_move->html('counter_bladedance', $userdata, 'skills'),
	'BLOCK_FULLSHIELD'					=> $special_move->html('block_fullshield', $userdata, 'skills'),
	'PARRY_SECONDLIFE'					=> $special_move->html('parry_secondlife', $userdata, 'skills'),
	'HIT_RESOLVE'						=> $special_move->html('hit_resolve', $userdata, 'skills'),
	'BLOCK_ADDCHANGE'					=> $special_move->html('block_addchange', $userdata, 'skills'),
	'MULTI_BLOCKCHANGES'				=> $special_move->html('multi_blockchanges', $userdata, 'skills'),
	'MULTI_RESOLVETACTIC'				=> $special_move->html('multi_resolvetactic', $userdata, 'skills'),
	'MULTI_SPEEDUP'						=> $special_move->html('multi_speedup', $userdata, 'skills'),
	'MULTI_FOLLOWME'					=> $special_move->html('multi_followme', $userdata, 'skills'),
	'HIT_OVERHIT'						=> $special_move->html('hit_overhit', $userdata, 'skills'),
	'MULTI_DOOM'						=> $special_move->html('multi_doom', $userdata, 'skills'),
	'HIT_WILLPOWER'						=> $special_move->html('hit_willpower', $userdata, 'skills'),
	'MULTI_AGRESSIVESHIELD'				=> $special_move->html('multi_agressiveshield', $userdata, 'skills'),
	'MULTI_SKIPARMOR'					=> $special_move->html('multi_skiparmor', $userdata, 'skills'),
	'BLOCK_ABSOLUTE'					=> $special_move->html('block_absolute', $userdata, 'skills'),
	'MULTI_HIDDENDODGE'					=> $special_move->html('multi_hiddendodge', $userdata, 'skills'),
	'MULTI_HIDDENPOWER'					=> $special_move->html('multi_hiddenpower', $userdata, 'skills'),
	'KRIT_CRUSH'						=> $special_move->html('krit_crush', $userdata, 'skills'),
	'MULTI_COWARDSHIFT'					=> $special_move->html('multi_cowardshift', $userdata, 'skills'),
	'HIT_NATISK'						=> ( $userdata['user_level'] >= 8 && $userdata['user_vitality'] >= 25 ) ? $special_move->html('hit_natisk', $userdata, 'skills') : '',
	'BLOCK_CIRCLESHIELD'				=> ( $userdata['user_level'] >= 8 && $userdata['user_vitality'] >= 25 ) ? $special_move->html('block_circleshield', $userdata, 'skills') : '')
);

//
// Характеристики персонажа
//
for( $i = 0; $i < count($stats_href); $i++ )
{
	$button = ( $userdata['user_free_upr'] > 0 ) ? ' <a href="main.php?upr=' . $stats_href[$i] . '" onclick="return confirm(\'Вы действительно хотите увеличить ' . $stats_but_names[$i] . '?\')"><img src="http://static.ivacuum.ru/i/up.gif" width="11" height="11" alt="увеличить"></a>' : '';

	$template->assign_block_vars('stats', array(
		'BUTTON'	=> $button,
		'NAME'		=> $stats_name[$i],
		'VALUE'		=> $userdata['user_' . $stats_href[$i]])
	);
}
// ----------

//
// Холодное оружие
//
for( $i = 0; $i < count($skills_href); $i++ )
{
	$button = ( $userdata['user_free_skills'] > 0 ) ? ' <a href="main.php?upr=' . $skills_href[$i] . '" onclick="return confirm(\'Вы действительно хотите увеличить &quot;Мастерство владения ' . $skills_name[$i] . '&quot;?\')"><img src="http://static.ivacuum.ru/i/up.gif" width="11" height="11" alt="увеличить"></a>' : '';

	$template->assign_block_vars('weapon_mastery', array(
		'BUTTON'	=> $button,
		'NAME'		=> $skills_name[$i],
		'VALUE'		=> $userdata['user_' . $skills_href[$i]])
	);
}
// ----------

//
// Магия
//
if( $userdata['user_level'] >= 4 )
{
	for( $i = 0; $i < count($magic_skills_href); $i++ )
	{
		$button = ( $userdata['user_free_skills'] > 0 ) ? ' <a href="main.php?upr=' . $magic_skills_href[$i] . '" onclick="return confirm(\'Вы действительно хотите увеличить &quot;Мастерство владения ' . $magic_skills_name[$i] . '&quot;?\')"><img src="http://static.ivacuum.ru/i/up.gif" width="11" height="11" alt="увеличить"></a>' : '';

		$template->assign_block_vars('magic_mastery', array(
			'BUTTON'	=> $button,
			'NAME'		=> $magic_skills_name[$i],
			'VALUE'		=> $userdata['user_' . $magic_skills_href[$i]])
		);
	}
}
// ----------

// Проверка уровня
if( $userdata['user_level'] >= 4 )
{
	$template->assign_block_vars('high_level', array());
}

$template->pparse('body');

include($root_path . 'includes/page_bottom.php');

?>