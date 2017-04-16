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
$GLOBALS['TL_DCA']['tl_hvz_request'] = array
(

	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
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
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'anfrage' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'msg' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'hits' => array
		(
			'sql'                     => "int(10) NOT NULL default '0'"
		),
		're_ip' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'agent' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'isbot' => array
		(
			'sql'                     => "int(10) NOT NULL default '0'"
		),
		'user_id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'ts' => array
		(
			'sql'                     => "timestamp NOT NULL default CURRENT_TIMESTAMP"
		)
	)
);
