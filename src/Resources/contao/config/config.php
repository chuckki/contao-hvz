<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

/**
 * Add back end modules.
 */
array_insert($GLOBALS['BE_MOD'], 0, [
    'Hvz' => [
        'hvz' => [
            'tables' => ['tl_hvz_category', 'tl_hvz'],
            'icon' => 'bundles/chuckkicontaohvz/icon.gif',
        ],
    ],
]);

// Load icon in Contao 4.2 backend
if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = 'bundles/chuckkicontaohvz/backend.css|static';
}

$GLOBALS['TL_MODELS']['tl_hvz'] = \Chuckki\ContaoHvzBundle\HvzModel::class;
$GLOBALS['TL_MODELS']['tl_hvz_orders'] = \Chuckki\ContaoHvzBundle\HvzOrderModel::class;
$GLOBALS['TL_MODELS']['tl_hvz_category'] = \Chuckki\ContaoHvzBundle\HvzCategoryModel::class;

/*
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, [
    'hvz' => [
        'hvzlist' => 'Chuckki\ContaoHvzBundle\ModuleHvzList',
        'hvzteaser' => 'Chuckki\ContaoHvzBundle\ModuleHvzTeaser',
        'hvzlistdropdown' => 'Chuckki\ContaoHvzBundle\ModuleHvzListDropDown',
        'hvzreader' => 'Chuckki\ContaoHvzBundle\ModuleHvzReader',
        'hvzresult' => 'Chuckki\ContaoHvzBundle\ModuleHvzResult',
        'hvzpaypal' => 'Chuckki\ContaoHvzBundle\ModuleHvzPaypal',
    ],
]);

$GLOBALS['FE_MOD']['faq']['faqreader'] = 'Chuckki\ContaoHvzBundle\ModuleFaqReader';

/*
 * Register hooks
 */

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['Chuckki\ContaoHvzBundle\ModuleHvzReplaceInsertTag', 'replaceCuInsertTags'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['Chuckki\ContaoHvzBundle\ModuleHvz', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['processFormData'][] = ['Chuckki\ContaoHvzBundle\ModuleHvz', 'saveFormData'];

$GLOBALS['TL_CRON']['monthly'][] = ['Chuckki\ContaoHvzBundle\ModuleHvz', 'mergeFamus'];

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'hvzs';
$GLOBALS['TL_PERMISSIONS'][] = 'hvzp';
