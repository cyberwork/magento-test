<?php

namespace Adriano\Teste\Controller\Adminhtml\Request;

use Adriano\Teste\Helper\Data;
use Adriano\Teste\Model\ProductPriceChangeRequestFactory;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 *
 */
class Approve extends Action
{
    /**
     * @var ProductPriceChangeRequestFactory
     */
    protected ProductPriceChangeRequestFactory $productPriceChangeRequestFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var Data
     */
    protected Data $helperModule;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    /**
     * @var ProductPriceChangeRequest
     */
    protected ProductPriceChangeRequest $resource;

    /**
     * @param Context $context
     * @param ProductPriceChangeRequest $resource
     * @param ProductPriceChangeRequestFactory $productPriceChangeRequestFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helperModule
     * @param RedirectInterface $redirector
     */
    public function __construct(
        Context                      $context,
        ProductPriceChangeRequest    $resource,
        ProductPriceChangeRequestFactory    $productPriceChangeRequestFactory,
        ProductRepositoryInterface   $productRepository,
        Data                         $helperModule,
        RedirectInterface            $redirector
    ) {
        $this->resource = $resource;
        $this->productRepository = $productRepository;

        $this->helperModule = $helperModule;
        $this->redirector = $redirector;
        $this->productPriceChangeRequestFactory = $productPriceChangeRequestFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            if ($id = $this->_request->getParam("id")) {
                $productPriceChangeRequestModel = $this->productPriceChangeRequestFactory->create();
                $this->resource->load($productPriceChangeRequestModel, $id);

                $productNewPrice = $productPriceChangeRequestModel->getData('new_price');
                $productSku = $productPriceChangeRequestModel->getData('product_sku');

                $product = $this->productRepository->get($productSku);

                $this->helperModule->runProductPriceUpdateQuery($product->getId(), $productNewPrice);
                $this->messageManager->addSuccessMessage(__("Data Saved Successfully."));

                $this->resource->delete($productPriceChangeRequestModel);
                return $this->resultFactory
                    ->create(ResultFactory::TYPE_REDIRECT)
                    ->setPath($this->redirector->getRefererUrl());
            } else {
                $this->messageManager->addErrorMessage(__("Data not found."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("We can\'t submit your request, Please try again."));
        }
    }
}
