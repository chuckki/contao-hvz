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
            'icon'   => 'bundles/chuckkicontaohvz/icon.gif'
		)
	)
));


// Load icon in Contao 4.2 backend
if ('BE' == TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/chuckkicontaohvz/backend.css|static';
}


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, array
(
	'hvz' => array
	(
		'hvzlist'           => 'Chuckki\ContaoHvzBundle\ModuleHvzList',
		'hvzteaser'         => 'Chuckki\ContaoHvzBundle\ModuleHvzTeaser',
		'hvzlistdropdown'   => 'Chuckki\ContaoHvzBundle\ModuleHvzListDropDown',
		'hvzreader'         => 'Chuckki\ContaoHvzBundle\ModuleHvzReader',
		'hvzresult'         => 'Chuckki\ContaoHvzBundle\ModuleHvzResult'
	)
));


$GLOBALS['FE_MOD']['faq']['faqreader'] = 'Chuckki\ContaoHvzBundle\ModuleFaqReader';

/**
 * Register hooks
 */

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Chuckki\ContaoHvzBundle\ModuleHvzReplaceInsertTag', 'replaceCuInsertTags');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('Chuckki\ContaoHvzBundle\ModuleHvz', 'getSearchablePages');

//if( count($GLOBALS['TL_HOOKS']['processFormData']) == 0){
    $GLOBALS['TL_HOOKS']['processFormData'][] = array('Chuckki\ContaoHvzBundle\ModuleHvz', 'saveFormData');
//}else{
//    array_unshift($GLOBALS['TL_HOOKS']['processFormData'], array('ModuleHvz', 'saveFormData'));
//}


$GLOBALS['TL_CRON']['monthly'][]   = array('Chuckki\ContaoHvzBundle\ModuleHvz', 'mergeFamus');



/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'hvzs';
$GLOBALS['TL_PERMISSIONS'][] = 'hvzp';
