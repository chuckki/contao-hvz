<?php

/**
 * Table tl_hvz
 */
$GLOBALS['TL_DCA']['tl_faq']['fields']['isFamus'] =array
		(
			'label'                   => array('isFamus','isFamus'),
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "int(10) NOT NULL default '0'"
		);
