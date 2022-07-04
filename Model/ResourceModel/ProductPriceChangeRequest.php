<?php

namespace Adriano\Teste\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductPriceChangeRequest extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'catalog_product_price_change_requests_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('catalog_product_price_change_requests', 'request_id');
        $this->_useIsObjectNew = true;
    }
}
