<?php

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
namespace Contao;


class ModuleHvzReplaceInsertTag extends Frontend
{
	public function replaceCuInsertTags($strTag)
	{
		$this->import('Session');

		$arrTag = explode('::', $strTag);

		if (!is_array($arrTag) || !isset($arrTag[1]) || !strlen($arrTag[1]))
			return false;

		switch( $arrTag[0] )
		{
			case 'session':
				$inser = \System::getContainer()->get('session')->get($arrTag[1]);
				if(is_array($inser)){
					$inser = print_r($inser,true);
				}
				if(isset($arrTag[2])){
					$inser = \System::getContainer()->get('session')->get($arrTag[1]);
					$inser = $inser[$arrTag[2]];
				}
				return $inser;

		}

		return false;
	}
}

