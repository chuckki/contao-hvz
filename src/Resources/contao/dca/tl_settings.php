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

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{hvz_api:hide},hvz_api,hvz_api_auth';
