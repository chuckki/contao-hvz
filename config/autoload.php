<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package HVZ
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(
	array
	(
		// Models
		'Contao\HvzCategoryModel'          => 'system/modules/hvz/models/HvzCategoryModel.php',
		'Contao\HvzModel'                  => 'system/modules/hvz/models/HvzModel.php',

		// Modules
		'Contao\ModuleHvz'                 => 'system/modules/hvz/modules/ModuleHvz.php',
		'Contao\ModuleHvzList'             => 'system/modules/hvz/modules/ModuleHvzList.php',
		'Contao\ModuleHvzTeaser'           => 'system/modules/hvz/modules/ModuleHvzTeaser.php',
		'Contao\ModuleHvzListDropDown'     => 'system/modules/hvz/modules/ModuleHvzListDropDown.php',
		'Contao\ModuleHvzReader'           => 'system/modules/hvz/modules/ModuleHvzReader.php',
		'Contao\ModuleHvzResult'           => 'system/modules/hvz/modules/ModuleHvzResult.php',
		'Contao\ModuleHvzReplaceInsertTag' => 'system/modules/hvz/modules/ModuleHvzReplaceInsertTag.php',
	)
);

/**
 * Register the templates
 */
TemplateLoader::addFiles(
	array
	(
		'mod_hvzlist'         => 'system/modules/hvz/templates/modules',
		'mod_hvzteaser'       => 'system/modules/hvz/templates/modules',
		'mod_hvzlistdropdown' => 'system/modules/hvz/templates/modules',
		'mod_hvzreader'       => 'system/modules/hvz/templates/modules',
		'mod_hvzresult'       => 'system/modules/hvz/templates/modules',
		'j_addJStoLayout' 	  => 'system/modules/hvz/templates',
	)
);
