<?php

/**
 * Contao Open Source CMS
 *
 *
 * @package Hvz
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Add back end modules
 */
array_insert($GLOBALS['BE_MOD'], 0, array
(
	'Hvz' => array(
		'hvz' => array
		(
			'tables' => array('tl_hvz_category', 'tl_hvz'),
			'icon'   => 'system/modules/hvz/assets/icon.gif'
		)
	)
));


// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/chuckkirabatt/backend.css';
}


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, array
(
	'hvz' => array
	(
		'hvzlist'   => 'ModuleHvzList',
		'hvzteaser'   => 'ModuleHvzTeaser',
		'hvzlistdropdown'   => 'ModuleHvzListDropDown',
		'hvzreader' => 'ModuleHvzReader',
		'hvzresult'   => 'ModuleHvzResult'
	)
));


/**
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('ModuleHvz', 'getSearchablePages');

if( count($GLOBALS['TL_HOOKS']['processFormData']) == 0){
    $GLOBALS['TL_HOOKS']['processFormData'][] = array('ModuleHvz', 'saveFormData');
}else{
    array_unshift($GLOBALS['TL_HOOKS']['processFormData'], array('ModuleHvz', 'saveFormData'));
}

$GLOBALS['TL_CRON']['monthly'][]   = array('ModuleHvz', 'mergeFamus');

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('ModuleHvzReplaceInsertTag', 'replaceCuInsertTags');


/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'hvzs';
$GLOBALS['TL_PERMISSIONS'][] = 'hvzp';
