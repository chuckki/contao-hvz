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
$GLOBALS['TL_DCA']['tl_hvz_orders'] = array
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
		'type' => array
		(
			'sql'                     => "smallint(5) unsigned NOT NULL"
		),
		'hvz_type' => array
		(
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		),
		'hvz_type_name' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'hvz_preis' => array
		(
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		),
		'hvzTagesPreis' => array
		(
			'sql'                     => "smallint(6) unsigned NOT NULL default '0'"
		),
		'hvz_ge_vorhanden' => array
		(
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'hvz_ort' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'hvz_plz' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'hvz_strasse_nr' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'hvz_vom' => array
		(
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'hvz_bis' => array
		(
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'hvz_vom_time' => array
		(
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'hvz_vom_bis' => array
		(
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'hvz_anzahl_tage' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'user_id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'hvz_meter' => array
		(
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'hvz_fahrzeugart' => array
		(
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'hvz_zusatzinfos' => array
		(
			'sql'                     => "text NOT NULL"
		),
		'hvz_gutscheincode' => array
		(
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'hvz_rabatt' => array
		(
			'sql'                     => "int(11) unsigned NOT NULL default '0'"
		),
        'hvz_grund' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
		're_anrede' => array
		(
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		're_firma' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
        're_umstid' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
		're_name' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_vorname' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_strasse_nr' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_ort_plz' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_email' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_telefon' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		're_agb_akzeptiert' => array
		(
			'sql'                     => "char(1) NOT NULL default ''"
		),
		're_ip' => array
		(
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'ts' => array
		(
			'sql'                     => "timestamp NOT NULL default CURRENT_TIMESTAMP"
		),
		'orderNumber' => array
		(
			'sql'                     => "varchar(255) NOT NULL default '0'"
		)
	)
);
