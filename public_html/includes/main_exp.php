<?php
if( !defined('IN_COMBATS') )
{
	die('Попытка взлома');
}

//
// Таблица опыта
//
switch( $userdata['user_stats'] )
{
	case 15: $nextexp = 20; break;
	case 16: $nextexp = 45; break;
	case 17: $nextexp = 75; break;
	case 18: $nextexp = 110; break;

	case 22: $nextexp = 160; break;
	case 23: $nextexp = 215; break;
	case 24: $nextexp = 280; break;
	case 25: $nextexp = 350; break;
	case 26: $nextexp = 410; break;

	case 30: $nextexp = 530; break;
	case 31: $nextexp = 670; break;
	case 32: $nextexp = 830; break;
	case 33: $nextexp = 950; break;
	case 34: $nextexp = 1100; break;
	case 35: $nextexp = 1300; break;

	case 39: $nextexp = 1450; break;
	case 40: $nextexp = 1650; break;
	case 41: $nextexp = 1850; break;
	case 42: $nextexp = 2050; break;
	case 43: $nextexp = 2200; break;
	case 44: $nextexp = 2500; break;

	case 50: $nextexp = 2900; break;
	case 51: $nextexp = 3350; break;
	case 52: $nextexp = 3800; break;
	case 53: $nextexp = 4200; break;
	case 54: $nextexp = 4600; break;
	case 55: $nextexp = 5000; break;

	case 59: $nextexp = 6000; break;
	case 60: $nextexp = 7000; break;
	case 61: $nextexp = 8000; break;
	case 62: $nextexp = 9000; break;
	case 63: $nextexp = 10000; break;
	case 64: $nextexp = 11000; break;
	case 65: $nextexp = 12000; break;
	case 66: $nextexp = 12500; break;

	case 70: $nextexp = 14000; break;
	case 71: $nextexp = 15500; break;
	case 72: $nextexp = 17000; break;
	case 73: $nextexp = 19000; break;
	case 74: $nextexp = 21000; break;
	case 75: $nextexp = 23000; break;
	case 76: $nextexp = 27000; break;
	case 77: $nextexp = 30000; break;

	case 83: $nextexp = 60000; break;
	case 84: $nextexp = 75000; break;
	case 85: $nextexp = 150000; break;
	case 86: $nextexp = 175000; break;
	case 87: $nextexp = 200000; break;
	case 88: $nextexp = 225000; break;
	case 89: $nextexp = 250000; break;
	case 90: $nextexp = 260000; break;
	case 91: $nextexp = 280000; break;
	case 92: $nextexp = 300000; break;

	case 98: $nextexp = 1500000; break;
	case 99: $nextexp = 1750000; break;
	case 100: $nextexp = 2000000; break;
	case 101: $nextexp = 2175000; break;
	case 102: $nextexp = 2300000; break;
	case 103: $nextexp = 2400000; break;
	case 104: $nextexp = 2500000; break;
	case 105: $nextexp = 2600000; break;
	case 106: $nextexp = 2800000; break;
	case 107: $nextexp = 3000000; break;

	case 116: $nextexp = 6000000; break;
	case 117: $nextexp = 6500000; break;
	case 118: $nextexp = 7500000; break;
	case 119: $nextexp = 8500000; break;
	case 120: $nextexp = 9000000; break;
	case 121: $nextexp = 9250000; break;
	case 122: $nextexp = 9500000; break;
	case 123: $nextexp = 9750000; break;
	case 124: $nextexp = 9900000; break;
	case 125: $nextexp = 10000000; break;

	case 137: $nextexp = 13000000; break;
	case 139: $nextexp = 14000000; break;
	case 141: $nextexp = 15000000; break;
	case 143: $nextexp = 16000000; break;
	case 145: $nextexp = 17000000; break;
	case 147: $nextexp = 17500000; break;
	case 149: $nextexp = 18000000; break;
	case 151: $nextexp = 19000000; break;
	case 153: $nextexp = 19500000; break;
	case 155: $nextexp = 20000000; break;
	case 157: $nextexp = 30000000; break;

	default:		$nextexp = ''; break;
}

//
// Шаблон
//								UPR	SKILLS	SPC	MONEY	VIT	LEVEL
// $user->update_up(	'5',	'1',		'1',	'1000',	'3',	'1');

//
// Получение способностей, умений и особенностей
// за апы и уровни
//

// ----------
// 0 уровень
//
if( $userdata['user_exp'] >= 20 && $userdata['user_stats'] == 15 )
{
	$user->update_up(1, 0, 0, 0, 0, 0);
}
elseif( $userdata['user_exp'] >= 45 && $userdata['user_stats'] == 16 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 75 && $userdata['user_stats'] == 17 )
{
	$user->update_up(1, 0, 0, 2, 0, 0);
}
//
// ----------

// ----------
// 1 уровень
//
elseif( $userdata['user_exp'] >= 110 && $userdata['user_stats'] == 18 )
{
	$user->update_up(3, 1, 1, 4, 1, 1);
}
elseif( $userdata['user_exp'] >= 160 && $userdata['user_stats'] == 22 )
{
	$user->update_up(1, 0, 0, 0, 0, 0);
}
elseif( $userdata['user_exp'] >= 215 && $userdata['user_stats'] == 23 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 280 && $userdata['user_stats'] == 24 )
{
	$user->update_up(1, 0, 0, 2, 0, 0);
}
elseif( $userdata['user_exp'] >= 350 && $userdata['user_stats'] == 25 )
{
	$user->update_up(1, 0, 0, 4, 0, 0);
}
//
// ----------

// ----------
// 2 уровень
//
elseif( $userdata['user_exp'] >= 410 && $userdata['user_stats'] == 26 )
{
	$user->update_up(3, 1, 1, 8, 1, 1);
}
elseif( $userdata['user_exp'] >= 530 && $userdata['user_stats'] == 30 )
{
	$user->update_up(1, 0, 0, 0, 0, 0);
}
elseif( $userdata['user_exp'] >= 670 && $userdata['user_stats'] == 31 )
{
	$user->update_up(1, 0, 0, 2, 0, 0);
}
elseif( $userdata['user_exp'] >= 830 && $userdata['user_stats'] == 32 )
{
	$user->update_up(1, 0, 0, 4, 0, 0);
}
elseif( $userdata['user_exp'] >= 950 && $userdata['user_stats'] == 33 )
{
	$user->update_up(1, 0, 0, 8, 0, 0);
}
elseif( $userdata['user_exp'] >= 1100 && $userdata['user_stats'] == 34 )
{
	$user->update_up(1, 0, 0, 12, 0, 0);
}
//
// ----------

// ----------
// 3 уровень
//
elseif( $userdata['user_exp'] >= 1300 && $userdata['user_stats'] == 35 )
{
	$user->update_up(3, 1, 1, 16, 1, 1);
}
elseif( $userdata['user_exp'] >= 1450 && $userdata['user_stats'] == 39 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 1650 && $userdata['user_stats'] == 40 )
{
	$user->update_up(1, 0, 0, 5, 0, 0);
}
elseif( $userdata['user_exp'] >= 1850 && $userdata['user_stats'] == 41 )
{
	$user->update_up(1, 0, 0, 10, 0, 0);
}
elseif( $userdata['user_exp'] >= 2050 && $userdata['user_stats'] == 42 )
{
	$user->update_up(1, 0, 0, 15, 0, 0);
}
elseif( $userdata['user_exp'] >= 2200 && $userdata['user_stats'] == 43 )
{
	$user->update_up(1, 0, 0, 20, 0, 0);
}
//
// ----------

// ----------
// 4 уровень
//
elseif( $userdata['user_exp'] >= 2500 && $userdata['user_stats'] == 44 )
{
	$user->update_up(5, 1, 1, 25, 1, 1);
}
elseif( $userdata['user_exp'] >= 2900 && $userdata['user_stats'] == 50 )
{
	$user->update_up(1, 0, 0, 3, 0, 0);
}
elseif( $userdata['user_exp'] >= 3350 && $userdata['user_stats'] == 51 )
{
	$user->update_up(1, 0, 0, 10, 0, 0);
}
elseif( $userdata['user_exp'] >= 3800 && $userdata['user_stats'] == 52 )
{
	$user->update_up(1, 0, 0, 15, 0, 0);
}
elseif( $userdata['user_exp'] >= 4200 && $userdata['user_stats'] == 53 )
{
	$user->update_up(1, 0, 0, 20, 0, 0);
}
elseif( $userdata['user_exp'] >= 4600 && $userdata['user_stats'] == 54 )
{
	$user->update_up(1, 0, 0, 25, 0, 0);
}
//
// ----------

// ----------
// 5 уровень
//
elseif( $userdata['user_exp'] >= 5000 && $userdata['user_stats'] == 55 )
{
	$user->update_up(3, 1, 1, 40, 1, 1);
}
elseif( $userdata['user_exp'] >= 6000 && $userdata['user_stats'] == 59 )
{
	$user->update_up(1, 0, 0, 6, 0, 0);
}
elseif( $userdata['user_exp'] >= 7000 && $userdata['user_stats'] == 60 )
{
	$user->update_up(1, 0, 0, 20, 0, 0);
}
elseif( $userdata['user_exp'] >= 8000 && $userdata['user_stats'] == 61 )
{
	$user->update_up(1, 0, 0, 30, 0, 0);
}
elseif( $userdata['user_exp'] >= 9000 && $userdata['user_stats'] == 62 )
{
	$user->update_up(1, 0, 0, 40, 0, 0);
}
elseif( $userdata['user_exp'] >= 10000 && $userdata['user_stats'] == 63 )
{
	$user->update_up(1, 0, 0, 40, 0, 0);
}
elseif( $userdata['user_exp'] >= 11000 && $userdata['user_stats'] == 64 )
{
	$user->update_up(1, 0, 0, 40, 0, 0);
}
elseif( $userdata['user_exp'] >= 12000 && $userdata['user_stats'] == 65 )
{
	$user->update_up(1, 0, 0, 50, 0, 0);
}
//
// ----------

// ----------
// 6 уровень
//
elseif( $userdata['user_exp'] >= 12500 && $userdata['user_stats'] == 66 )
{
	$user->update_up(3, 1, 1, 80, 1, 1);
}
elseif( $userdata['user_exp'] >= 14000 && $userdata['user_stats'] == 70 )
{
	$user->update_up(1, 0, 0, 9, 0, 0);
}
elseif( $userdata['user_exp'] >= 15500 && $userdata['user_stats'] == 71 )
{
	$user->update_up(1, 0, 0, 25, 0, 0);
}
elseif( $userdata['user_exp'] >= 17000 && $userdata['user_stats'] == 72 )
{
	$user->update_up(1, 0, 0, 45, 0, 0);
}
elseif( $userdata['user_exp'] >= 19000 && $userdata['user_stats'] == 73 )
{
	$user->update_up(1, 0, 0, 45, 0, 0);
}
elseif( $userdata['user_exp'] >= 21000 && $userdata['user_stats'] == 74 )
{
	$user->update_up(1, 0, 0, 45, 0, 0);
}
elseif( $userdata['user_exp'] >= 23000 && $userdata['user_stats'] == 75 )
{
	$user->update_up(1, 0, 0, 55, 0, 0);
}
elseif( $userdata['user_exp'] >= 27000 && $userdata['user_stats'] == 76 )
{
	$user->update_up(1, 0, 0, 45, 0, 0);
}
//
// ----------

// ----------
// 7 уровень
//
elseif( $userdata['user_exp'] >= 30000 && $userdata['user_stats'] == 77 )
{
	$user->update_up(5, 1, 1, 90, 1, 1);
}
elseif( $userdata['user_exp'] >= 60000 && $userdata['user_stats'] == 83 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 75000 && $userdata['user_stats'] == 84 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 150000 && $userdata['user_stats'] == 85 )
{
	$user->update_up(1, 0, 0, 150, 0, 0);
}
elseif( $userdata['user_exp'] >= 175000 && $userdata['user_stats'] == 86 )
{
	$user->update_up(1, 0, 0, 50, 0, 0);
}
elseif( $userdata['user_exp'] >= 200000 && $userdata['user_stats'] == 87 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 225000 && $userdata['user_stats'] == 88 )
{
	$user->update_up(1, 0, 0, 50, 0, 0);
}
elseif( $userdata['user_exp'] >= 250000 && $userdata['user_stats'] == 89 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 260000 && $userdata['user_stats'] == 90 )
{
	$user->update_up(1, 0, 0, 50, 0, 0);
}
elseif( $userdata['user_exp'] >= 280000 && $userdata['user_stats'] == 91 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
//
// ----------

// ----------
// 8 уровень
//
elseif( $userdata['user_exp'] >= 300000 && $userdata['user_stats'] == 92 )
{
	$user->update_up(5, 1, 1, 700, 1, 1);
}
elseif( $userdata['user_exp'] >= 1500000 && $userdata['user_stats'] == 98 )
{
	$user->update_up(1, 0, 0, 500, 0, 0);
}
elseif( $userdata['user_exp'] >= 1750000 && $userdata['user_stats'] == 99 )
{
	$user->update_up(1, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 2000000 && $userdata['user_stats'] == 100 )
{
	$user->update_up(1, 0, 0, 300, 0, 0);
}
elseif( $userdata['user_exp'] >= 2175000 && $userdata['user_stats'] == 101 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 2300000 && $userdata['user_stats'] == 102 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 2400000 && $userdata['user_stats'] == 103 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 2500000 && $userdata['user_stats'] == 104 )
{
	$user->update_up(1, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 2600000 && $userdata['user_stats'] == 105 )
{
	$user->update_up(1, 0, 0, 100, 0, 0);
}
elseif( $userdata['user_exp'] >= 2800000 && $userdata['user_stats'] == 106 )
{
	$user->update_up(1, 0, 0, 200, 0, 0);
}
// ----------

// ----------
// 9 уровень
//
elseif( $userdata['user_exp'] >= 3000000 && $userdata['user_stats'] == 107 )
{
	$user->update_up(7, 1, 1, 1000, 2, 1);
}
elseif( $userdata['user_exp'] >= 6000000 && $userdata['user_stats'] == 116 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 6500000 && $userdata['user_stats'] == 117 )
{
	$user->update_up(1, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 7500000 && $userdata['user_stats'] == 118 )
{
	$user->update_up(1, 0, 0, 1, 0, 0);
}
elseif( $userdata['user_exp'] >= 8500000 && $userdata['user_stats'] == 119 )
{
	$user->update_up(1, 0, 0, 250, 0, 0);
}
elseif( $userdata['user_exp'] >= 9000000 && $userdata['user_stats'] == 120 )
{
	$user->update_up(1, 0, 0, 400, 0, 0);
}
elseif( $userdata['user_exp'] >= 9250000 && $userdata['user_stats'] == 121 )
{
	$user->update_up(1, 0, 0, 50, 0, 0);
}
elseif( $userdata['user_exp'] >= 9500000 && $userdata['user_stats'] == 122 )
{
	$user->update_up(1, 0, 0, 400, 0, 0);
}
elseif( $userdata['user_exp'] >= 9750000 && $userdata['user_stats'] == 123 )
{
	$user->update_up(1, 0, 0, 350, 0, 0);
}
elseif( $userdata['user_exp'] >= 9900000 && $userdata['user_stats'] == 124 )
{
	$user->update_up(1, 0, 0, 500, 0, 0);
}
//
// ----------

// ----------
// 10 уровень
//
elseif( $userdata['user_exp'] >= 10000000 && $userdata['user_stats'] == 125 )
{
	$user->update_up(9, 1, 1, 2000, 3, 1);
}
elseif( $userdata['user_exp'] >= 13000000 && $userdata['user_stats'] == 137 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 14000000 && $userdata['user_stats'] == 139 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 15000000 && $userdata['user_stats'] == 141 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 16000000 && $userdata['user_stats'] == 143 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 17000000 && $userdata['user_stats'] == 145 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 17500000 && $userdata['user_stats'] == 147 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 18000000 && $userdata['user_stats'] == 149 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 19000000 && $userdata['user_stats'] == 151 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 19500000 && $userdata['user_stats'] == 153 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
elseif( $userdata['user_exp'] >= 20000000 && $userdata['user_stats'] == 155 )
{
	$user->update_up(2, 0, 0, 200, 0, 0);
}
//
// ----------

?>