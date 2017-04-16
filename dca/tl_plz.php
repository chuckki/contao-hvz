<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_plz
 */
$GLOBALS['TL_DCA']['tl_plz'] = array
(

	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'plzS' => 'index',
				'plz' => 'index',
				'ortid' => 'index'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'ortid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'plz' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'plzS' => array
		(
			'sql'                     => "varchar(10) NOT NULL"
		)
	)
);
