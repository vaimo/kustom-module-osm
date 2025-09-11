<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Onsitemessaging\Block;

use Klarna\AdminSettings\Model\Configurations\Osm;
use Klarna\Base\Model\Api\MagentoToKlarnaLocaleMapper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * @internal
 */
class Footer extends AbstractPosition
{

    /**
     * Check to see if display on footer is enabled
     *
     * @return bool
     */
    public function showInFooter(): bool
    {
        $store = $this->_storeManager->getStore();
        $result = $this->osmConfiguration->isEnabledOnFooter($store);
        if (!$result) {
            return false;
        }

        return $this->isEnabledAndValidCountry($store);
    }
}
