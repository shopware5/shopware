<?php

return array(
    'module' => 'frontend',
    'controller' => 'register',
    'action' => 'saveRegister',
    'sTarget' => 'account',
    'sTargetAction' => 'index',
    'register' =>
        array(
            'personal' =>
                array(
                    'customer_type' => 'private',
                    'salutation' => 'mr',
                    'firstname' => 'Rainer',
                    'lastname' => 'Zufall',
                    'accountmode' => '0',
                    'email' => 'RainerZufall@random.com',
                    'password' => 'randomPassword',
                ),
            'billing' =>
                array(
                    'street' => 'Zur Hölle',
                    'zipcode' => '25836',
                    'city' => 'Nordpol',
                    'country' => '2',
                ),
            'shipping' =>
                array(
                    'company' => '',
                    'department' => '',
                    'firstname' => '',
                    'lastname' => '',
                    'street' => '',
                    'zipcode' => '',
                    'city' => '',
                ),
        ),
    'first_name_confirmation' => '',
    'Submit' => '',
    'captchaName' => 'honeypot',
);
