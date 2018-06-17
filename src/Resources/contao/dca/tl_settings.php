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


$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{hvz_api:hide},hvz_api';