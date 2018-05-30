<?php

// Anpassung der Palette
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = str_replace
(
    'gender',
    //'gender,gutschein,zusatzinfo',
    'gender,gutschein,zusatzinfo,umstid',
    $GLOBALS['TL_DCA']['tl_member']['palettes']['default']
);

// Hinzufügen der Feld-Konfiguration
/*
$GLOBALS['TL_DCA']['tl_member']['fields']['anrede'] = array
(
	'label'                   => array('Anrede',''),
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('Herr','Frau'),
    'eval'      			  => array('feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
    'sql'       			  => "varchar(8) NOT NULL default ''"
);
*/

$GLOBALS['TL_DCA']['tl_member']['fields']['street']['label'] = array('Strasse und Hausnummer','');


$GLOBALS['TL_DCA']['tl_member']['fields']['zusatzinfo'] = array
(
	'label'                   => array('Zusatzinfo','Dies wird immer angezeigt'),
	'exclude'                 => true,
	'inputType'               => 'text',
    'eval'      			  => array('feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
    'sql'       			  => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['gutschein'] = array
(
	'label'                   => array('Gutschein',''),
	'exclude'                 => true,
	'inputType'               => 'text',
    'eval'      			  => array('feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
    'sql'       			  => "varchar(20) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_member']['fields']['umstid'] = array
(
    'label'                   => array('USt-IdNr.',''),
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'      			  => array('feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
    'sql'       			  => "varchar(20) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['gender']  = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['gender'],
			'label'                   => array('Anrede',),

			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('Herr', 'Frau'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('includeBlankOption'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		);

//$GLOBALS['TL_LANG']['tl_member']['gender'] = array('Geschlecht', 'Bitte wählen Sie das Geschlecht.');		