<?php

namespace Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;

use Adriano\Teste\Model\ProductPriceChangeRequest as Model;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'catalog_product_price_change_requests_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
