<?php

namespace Chuckki\ContaoHvzBundle;

use Contao\Model;
/**
 * Reads and writes Hvzs.
 *
 * @property int    $ortid
 * @property int    $plz
 * @property string    $plzS
 * @property string    $lk
 */
class HvzPlzModel extends Model
{
   protected static $strTable = 'tl_plz';
}
