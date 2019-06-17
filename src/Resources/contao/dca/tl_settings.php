<?php
/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api'] = [
    'label'     => ['Apiurl', 'https://backend.domain.de'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api_auth'] = [
    'label'     => ['Api-Auth', 'asdf2342734ggj9238432g97soawenfoiasdflawjef'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['edit_order']   = [
    'label'     => ['Bestellung bearbeiten Seite', 'Wählen Sie eine Seite, auf der das Payment-Widget eingebunden ist'],
    'inputType' => 'pageTree',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['finish_order'] = [
    'label'     => ['Bestellung abgeschlossen', 'Wählen Sie eine Seite, auf der die Bestellung als beendet gilt'],
    'inputType' => 'pageTree',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['paypal_payment'] = [
    'label'     => ['Paypal-Modul', 'Wählen Sie eine Seite, auf der das Paypal-Modul eingebunden ist'],
    'inputType' => 'pageTree',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['klarna_payment'] = [
    'label'     => ['Klarna-Modul', 'Wählen Sie eine Seite, auf der das Klarna-Modul eingebunden ist'],
    'inputType' => 'pageTree',
    'eval'      => ['tl_class' => 'w50'],
];
// Klarna Settings
$GLOBALS['TL_DCA']['tl_settings']['fields']['klarna_user'] = [
    'label'     => ['Klarna User', ''],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['klarna_pw'] = [
    'label'     => ['Klarna Password', ''],
    'inputType' => 'text',
    'eval'      => [
        'hideInput' => true,
        'tl_class'  => 'w50'
    ],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['klarna_env'] = [
    'label'     => ['Klarna Playground', 'Wählen Sie Klarna Playground für den Testmodus'],
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
];
// Paypal Settings
$GLOBALS['TL_DCA']['tl_settings']['fields']['paypal_id'] = [
    'label'     => ['Paypal Client ID', ''],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['paypal_secret'] = [
    'label'     => ['Paypal Secret', ''],
    'inputType' => 'text',
    'eval'      => [
        'hideInput' => true,
        'tl_class'  => 'w50'
    ],
];


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['notifications'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_form']['nc_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('NotificationCenter\tl_form', 'getNotificationChoices'),
    'eval'                      => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'clr'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{hvz_api:hide},hvz_api,hvz_api_auth;';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= '{klarna_settings:hide},klarna_user,klarna_pw,klarna_env;';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= '{paypal_settings:hide},paypal_id,paypal_secret, notifications;';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= '{hvz_payment:hide},edit_order,finish_order,paypal_payment,klarna_payment';
