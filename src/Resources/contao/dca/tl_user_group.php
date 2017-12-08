<?php

/**
 * Contao Open Source CMS
 *
 *
 * @package Hvz
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('formp;', 'formp;{hvz_legend},hvzs,hvzp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['hvzs'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['hvzs'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_hvz_category.title',
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['hvzp'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['hvzp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);
