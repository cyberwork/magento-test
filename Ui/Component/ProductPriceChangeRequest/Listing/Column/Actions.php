<?php

namespace Adriano\Teste\Ui\Component\ProductPriceChangeRequest\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Model\UrlInterface;

class Actions extends Column
{

    /** @var UrlInterface */
    protected $_urlBuilder;

    /**
     * @var string
     */
    protected $_viewUrl;


    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        $viewUrl = '',
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_viewUrl    = $viewUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['request_id'])) {
                    $item[$name]['view']   = [
                        'href'  => $this->_urlBuilder->getUrl($this->_viewUrl, ['id' => $item['request_id']]),
                        //'target' => '_blank',
                        'label' => __('Aprovar')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
