<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

class ModuleHvzReplaceInsertTag extends \Frontend
{
    public function replaceCuInsertTags($strTag)
    {
        $this->import('Session');

        $arrTag = explode('::', $strTag);

        if (!\is_array($arrTag) || !isset($arrTag[1]) || !\strlen($arrTag[1])) {
            return false;
        }

        switch ($arrTag[0]) {
            case 'session':
                $inser = \System::getContainer()->get('session')->get($arrTag[1]);
                if (\is_array($inser)) {
                    $inser = print_r($inser, true);
                }
                if (isset($arrTag[2])) {
                    $inser = \System::getContainer()->get('session')->get($arrTag[1]);
                    $inser = $inser[$arrTag[2]];
                }

                return $inser;
        }

        return false;
    }
}
