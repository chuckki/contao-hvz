<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

/**
 * Provide methods regarding FAQs.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleFaqReader extends \Contao\ModuleFaqReader
{
    /**
     * Generate the module.
     */
    protected function compile(): void
    {

        $objFaq = \FaqModel::findPublishedByParentAndIdOrAlias(\Input::get('items'), $this->faq_categories);
        if (null !== $objFaq) {
            $updateFamus = (int) ($objFaq->isFamus) + 1;
            $this->import('Database');
            $objUpdate = $this->Database->prepare('UPDATE tl_faq set isFamus = ? where id = ?')
                ->execute($updateFamus, $objFaq->id);
        }
        $buffer = parent::compile();
    }
}
