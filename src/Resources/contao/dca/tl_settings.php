<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api'] = [
'label' => ['Apiurl', 'https://backend.domain.de'],
'inputType' => 'text',
'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api_auth'] = [
'label' => ['Api-Auth', 'asdf2342734ggj9238432g97soawenfoiasdflawjef'],
'inputType' => 'text',
'eval' => ['tl_class' => 'w50'],
];


$GLOBALS['TL_DCA']['tl_settings']['fields']['edit_order'] = [
'label' => ['Bestellung bearbeiten Seite', 'Wählen Sie eine Seite, auf der das Payment-Widget eingebunden ist'],
'inputType' => 'pageTree',
'eval' => ['tl_class' => 'w50'],
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['finish_order'] = [
'label' => ['Bestellung abgeschlossen', 'Wählen Sie eine Seite, auf der die Bestellung als beendet gilt'],
'inputType' => 'pageTree',
'eval' => ['tl_class' => 'w50'],
];



$GLOBALS['TL_DCA']['tl_settings']['fields']['paypal_payment'] = [
'label' => ['Paypal-Modul', 'Wählen Sie eine Seite, auf der das Paypal-Modul eingebunden ist'],
'inputType' => 'pageTree',
'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['klarna_payment'] = [
'label' => ['Klarna-Modul', 'Wählen Sie eine Seite, auf der das Klarna-Modul eingebunden ist'],
'inputType' => 'pageTree',
'eval' => ['tl_class' => 'w50'],
];


$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{hvz_api:hide},hvz_api,hvz_api_auth;';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= '{hvz_payment:hide},edit_order,finish_order,paypal_payment,klarna_payment';
