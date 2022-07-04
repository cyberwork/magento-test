<?php
namespace Adriano\Teste\Controller\Adminhtml\Request;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Response\RedirectInterface;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest\CollectionFactory;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;

class MassDelete extends Action
{

    protected $filter;

    protected $collectionFactory;

    private $categoryRepository;

    /**
     * @var ProductPriceChangeRequest
     */
    protected ProductPriceChangeRequest $resource;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductPriceChangeRequest $resource,
        RedirectInterface            $redirector
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->redirector = $redirector;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $recordsDeleted = 0;
        foreach ($collection->getItems() as $row) {
            $this->resource->delete($row);
            $recordsDeleted++;
        }

        if ($recordsDeleted) {
            $this->messageManager->addSuccessMessage(
                __('%1 registro(s) excluídos.', $recordsDeleted)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($this->redirector->getRefererUrl());
    }

    public function execute2()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $categoryDeleted = 0;
        foreach ($collection->getItems() as $category) {
            $this->categoryRepository->delete($category);
            $categoryDeleted++;
        }

        if ($categoryDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $categoryDeleted)
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('dev_grid/index/index');
    }
}
