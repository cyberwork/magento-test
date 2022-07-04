<?php

namespace Adriano\Teste\Observer;

use Adriano\Teste\Helper\Data;
use Adriano\Teste\Model\ProductPriceChangeRequestFactory;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;
use Magento\Backend\Model\Auth\SessionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductRequestChangePrice implements ObserverInterface
{
    /**
     * @var ProductPriceChangeRequestFactory
     */
    protected $productPriceChangeRequestFactory;

    /**
     * @var ProductPriceChangeRequest
     */
    protected ProductPriceChangeRequest $resource;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezoneInterface;

    /**
     * @var SessionFactory
     */
    protected SessionFactory $userSessionFactory;

    protected Data $helperModule;

    /**
     * @param ProductPriceChangeRequestFactory $productPriceChangeRequestFactory
     * @param ProductPriceChangeRequest $resource
     * @param SessionFactory $userSessionFactory
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        ProductPriceChangeRequestFactory $productPriceChangeRequestFactory,
        ProductPriceChangeRequest $resource,
        SessionFactory $userSessionFactory,
        TimezoneInterface $timezoneInterface,
        Data $helperModule
    ) {
        $this->productPriceChangeRequestFactory = $productPriceChangeRequestFactory;
        $this->resource = $resource;
        $this->userSessionFactory = $userSessionFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->helperModule = $helperModule;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        try {
            $product = $observer->getProduct();
            $productId = $product->getId();
            $productOrigPrice = $product->getOrigData('price');
            $productNewPrice = $product->getData('price');
            if ($productOrigPrice !== $productNewPrice) {
                $productPriceChangeRequestModel = $this->productPriceChangeRequestFactory->create()->setData([
                    'product_id' => $productId,
                    'requested_user_id' => $this->getUserId(),
                    'product_sku' => $product->getData('sku'),
                    'attributte' => 'price',
                    'old_price' => $productOrigPrice,
                    'new_price' => $productNewPrice,
                    'approved' => false,
                    'requested_at' => $this->timezoneInterface->date()->format('m/d/y H:i:s')
                ]);
                $this->resource->save($productPriceChangeRequestModel);
                $product->setData('price', $productOrigPrice);

                if ($this->helperModule->getFieldConfig('sendemail')) {
                    $this->helperModule->sendMail(
                        $this->helperModule->getFieldConfig('email'),
                        $this->helperModule->getFieldConfig('email_template'),
                        [
                            'product_name' => $product->getData('name'),
                            'product_sku' => $product->getData('sku'),
                            'product_old_price' => $productOrigPrice,
                            'product_new_price' => $productNewPrice
                        ]
                    );
                }
            }
        } catch (\Execption $e) {
            $this->logger->info('Error : ' . $e->getMessage());
        }
    }

    /**
     * @param Int $id
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    protected function getProductById(Int $id): ProductInterface
    {
        return $this->productRepository->getById($id);
    }

    /**
     * @return Int
     */
    protected function getUserId(): Int
    {
        $userSession = $this->userSessionFactory->create();
        return $userSession->getUser()->getId();
    }
}
