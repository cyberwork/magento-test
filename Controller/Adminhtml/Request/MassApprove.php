<?php
namespace Adriano\Teste\Controller\Adminhtml\Request;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Response\RedirectInterface;
use Adriano\Teste\Helper\Data;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest\CollectionFactory;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;
use Magento\Catalog\Api\ProductRepositoryInterface;

class MassApprove extends Action
{

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var ProductPriceChangeRequest
     */
    protected ProductPriceChangeRequest $resource;

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
    protected RedirectInterface $redirector;

    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProductPriceChangeRequest $resource
     * @param RedirectInterface $redirector
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helperModule
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductPriceChangeRequest $resource,
        RedirectInterface            $redirector,
        ProductRepositoryInterface   $productRepository,
        Data                         $helperModule
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->redirector = $redirector;
        $this->productRepository = $productRepository;
        $this->helperModule = $helperModule;
        parent::__construct($context);
    }

    /**
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $recordsApproved = 0;
        foreach ($collection->getItems() as $row) {
            $productNewPrice = $row->getData('new_price');
            $productSku = $row->getData('product_sku');

            $product = $this->productRepository->get($productSku);

            $this->helperModule->runProductPriceUpdateQuery($product->getId(), $productNewPrice);
            $this->resource->delete($row);
            $recordsApproved++;
        }

        if ($recordsApproved) {
            $this->messageManager->addSuccessMessage(
                __('%1 requisição(ões) aprovadas(s).', $recordsApproved)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($this->redirector->getRefererUrl());
    }
}
