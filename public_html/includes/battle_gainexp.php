<?php

if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

//
// Заранее определяем переменные
//
$userdata['items_cost'] = 0;
$pl2_userdata['items_cost'] = 0;
$userdata['gain_exp'] = 0;
$pl2_userdata['gain_exp'] = 0;
// ----------

//
// Базовый опыт
//
$experience[0] = 5;
$experience[1] = 10;
$experience[2] = 20;
$experience[3] = 30;
$experience[4] = 60;
$experience[5] = 120;
$experience[6] = 180;
$experience[7] = 300;
$experience[8] = 600;
$experience[9] = 1200;
$experience[10] = 2400;

if( $userdata['user_level'] == $pl2_userdata['user_level'] )
{
	$userdata['gain_exp'] = $experience[$userdata['user_level']];
	$pl2_userdata['gain_exp'] = $experience[$pl2_userdata['user_level']];
}
elseif( $userdata['user_level'] > $pl2_userdata['user_level'] || $userdata['user_level'] < $pl2_userdata['user_level'] )
{
	$userdata['gain_exp'] = $experience[$pl2_userdata['user_level']];
	$pl2_userdata['gain_exp'] = $experience[$userdata['user_level']];
}
// ----------

//
// Определяем количество получаемого опыта
//
if( $userdata['items_cost'] > 2 && $pl2_userdata['items_cost'] < 2 )
{
	//
	// Первый одет, а второй нет
	//
	$userdata['gain_exp'] += ( $userdata['gain_exp'] / 2 ) - round($userdata['user_hit_hp'] / 10) - round($userdata['items_cost'] / 10);
	$pl2_userdata['gain_exp'] += ( $pl2_userdata['gain_exp'] * 2) + round($pl2_userdata['user_hit_hp'] / 10) + round($userdata['items_cost'] / 10);
	// ----------
}
elseif( $userdata['items_cost'] < 2 && $pl2_userdata['items_cost'] > 2 )
{
	//
	// Второй одет, а первый нет
	//
	$userdata['gain_exp'] += ( $userdata['gain_exp'] * 2 ) + round($userdata['user_hit_hp'] / 10) - round($pl2_userdata['items_cost'] / 10);
	$pl2_userdata['gain_exp'] += ( $pl2_userdata['gain_exp'] / 2 ) - round($pl2_userdata['user_hit_hp'] / 10) - round($userdata['items_cost'] / 10);
	// ----------
}
elseif( $userdata['items_cost'] > 2 && $pl2_userdata['items_cost'] > 2 )
{
	//
	// Оба одеты
	//
	$userdata['gain_exp'] += round(($team[$enemy_team_id]['items_cost'] - $team[$userdata['user_battle_team']]['items_cost']) / 10) + round($userdata['user_hit_hp'] / 10);
	$pl2_userdata['gain_exp'] += round(($team[$userdata['user_battle_team']]['items_cost'] - $team[$enemy_team_id]['items_cost']) / 10) + round($pl2_userdata['user_hit_hp'] / 10);
	// ----------
}

//
// Добавка к опыту
//
if( $userdata['user_items_cost'] > 700 )
{
	$userdata['gain_exp'] *= 2;
}
elseif( $userdata['user_items_cost'] > 350 )
{
	$userdata['gain_exp'] *= 1.5;
}

if( $pl2_userdata['user_items_cost'] > 700 )
{
	$pl2_userdata['gain_exp'] *= 2;
}
elseif( $pl2_userdata['user_items_cost'] > 350 )
{
	$pl2_userdata['gain_exp'] *= 1.5;
}
// ----------

//
// Добавка от склонности
//
if( ( $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 && $pl2_userdata['user_align'] >= 3 && $pl2_userdata['user_align'] < 4 ) || ( $pl2_userdata['user_align'] >= 3 && $pl2_userdata['user_align'] < 4 && $userdata['user_align'] >= 1 && $userdata['user_align'] < 2 ) )
{
	$userdata['gain_exp'] *= 1.5;
	$pl2_userdata['gain_exp'] *= 1.5;
}
// ----------

if( $userdata['user_level'] >= 2 )
{
	$userdata['min_gain_exp'] = round($userdata['gain_exp'] / 1.5);
	$userdata['max_gain_exp'] = round($userdata['gain_exp'] * 1.5);

	$userdata['gain_exp'] = mt_rand($userdata['min_gain_exp'], $userdata['max_gain_exp']);
}
else
{
	$userdata['gain_exp'] = round($userdata['gain_exp']);
}

if( $pl2_userdata['user_level'] >= 2 )
{
	$pl2_userdata['min_gain_exp'] = round($pl2_userdata['gain_exp'] / 1.5);
	$pl2_userdata['max_gain_exp'] = round($pl2_userdata['gain_exp'] * 1.5);

	$pl2_userdata['gain_exp'] = mt_rand($pl2_userdata['min_gain_exp'], $pl2_userdata['max_gain_exp']);
}
else
{
	$pl2_userdata['gain_exp'] = round($pl2_userdata['gain_exp']);
}

$userdata['gain_exp'] = ( $userdata['gain_exp'] == '' || !$userdata['gain_exp'] ) ? 0 : $userdata['gain_exp'];

?>