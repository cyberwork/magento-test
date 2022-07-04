<?php

namespace Adriano\Teste\Model;

use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class ProductPriceChangeRequest extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'catalog_product_price_change_requests_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
