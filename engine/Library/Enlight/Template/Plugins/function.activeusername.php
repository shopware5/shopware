<?php

function smarty_function_activeUserName($params, $smarty)
{
	$userData = Shopware()->Modules()->Admin()->sGetUserData();
	if (!empty($userData['billingaddress']) && !empty($userData['billingaddress']['userID'])) {
		return $userData['billingaddress']['firstname'].' '.$userData['billingaddress']['lastname'];
	}
	return null;
}
