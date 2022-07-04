<?php

namespace Adriano\Teste\Helper;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilderAlias;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class Data extends AbstractHelper
{
    /**
     * @var TransportBuilderAlias
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    const TRANSACTION_EMAIL_NAME = 'trans_email/ident_general/name';

    const TRANSACTION_EMAIL = 'trans_email/ident_general/email';

    /**
     *
     */
    const XML_PATH_FIELD = 'adrianoteste/general/';

    /**
     * @param Context $context
     * @param TransportBuilderAlias $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     */
    public function __construct(
        Context                      $context,
        TransportBuilderAlias        $transportBuilder,
        StoreManagerInterface        $storeManager,
        StateInterface               $state,
        LoggerInterface              $logger,
        ResourceConnection           $resource,
        AttributeRepositoryInterface $attributeRepository,
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->logger = $logger;
        $this->resource = $resource;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context);
    }

    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail(String $toEmail, String $templateId, array $templateVars)
    {
        $this->inlineTranslation->suspend();

        // set from email
        $senderEmail = $this->scopeConfig->getValue(
            self::TRANSACTION_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $senderName = $this->scopeConfig->getValue(
            self::TRANSACTION_EMAIL_NAME,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => Area::AREA_ADMINHTML,
                'store' => $this->getStoreId()
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope([
                'name' => $senderName,
                'email' => $senderEmail
            ])
            ->addTo($toEmail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $code
     * @param $storeId
     * @return mixed
     */
    public function getFieldConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_FIELD . $code, $storeId);
    }

    /*
     * get Current store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /*
     * get Current store Info
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Update Sql Query
     */
    public function runProductPriceUpdateQuery(int $productId, float $productNewPrice)
    {
        $connection = $this->resource->getConnection();
        $data = [
            "value" => $productNewPrice,
        ];
        $where = [
            'entity_id = ?' => (int)$productId,
            'attribute_id = ?' => $this->getAttributeId('price'),
        ];

        $tableName = $connection->getTableName("catalog_product_entity_decimal");
        $connection->update($tableName, $data, $where);
    }

    /**
     * @param $attribute
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributeId($attribute): int
    {
        return $this->attributeRepository->get(Product::ENTITY, $attribute)->getAttributeId();
    }
}
