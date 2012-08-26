<?php
/***************************************************************************
 *								 vip.php								   *
 *						  ----------------------						   *
 *   begin				: Wednesday, August 10, 2005					   *
 *   copyright			: © 2005 V@cuum									   *
 *   email				: knifevacuum@rambler.ru						   *
 *																		   *
 *   $Id: vip.php, v 1.00 2005/11/12 17:59:00 V@cuum Exp $				   *
 *																		   *
 *																		   *
 ***************************************************************************/

define('IN_COMBATS', true);

$root_path = './';
$site_root_path = './../';
include($root_path . 'common.php');
include($root_path . 'includes/user_vip.php');

$userdata = session_pagestart($user_ip);

//
// Чужакам вход воспрещен
//
if( !$userdata['session_logged_in'] || $userdata['user_blocked'] )
{
	redirect($root_path . 'return.php');
}
elseif( $userdata['user_access_level'] != ADMIN && !$userdata['user_vip'] )
{
	redirect($root_path . 'main.php');
}
// ----------

//
// Определяем переменные
//
$message	= '';
$redirect	= request_var('redirect', '');
// ----------

if( $redirect != $userdata['user_redirect'] )
{
	$message = $user->redirect($redirect);

	$userdata['user_redirect'] = $redirect;
}

site_header();

$template->set_filenames(array(
	'body' => 'vip_body.html')
);

$template->assign_vars(array(
	'DRWFL'		=> $user->drwfl($userdata),
	'MESSAGE'	=> ( $message ) ? '<font color="red"><b>' . $message . '</b></font>' : $message,
	'REDIRECT'	=> $userdata['user_redirect'])
);

$template->pparse('body');

site_bottom();

?>