<?php
/**
* System configuration
*/
$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api'] = array
(
'label'                   => array('Apiurl','https://backend.domain.de'),
'inputType'               => 'text',
'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['hvz_api_auth'] = array
(
'label'                   => array('Api-Auth','asdf2342734ggj9238432g97soawenfoiasdflawjef'),
'inputType'               => 'text',
'eval'                    => array('tl_class'=>'w50')
);


$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{hvz_api:hide},hvz_api,hvz_api_auth';