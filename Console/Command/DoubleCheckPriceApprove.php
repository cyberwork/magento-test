<?php

namespace Adriano\Teste\Console\Command;

use Adriano\Teste\Helper\Data;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest;
use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Lists;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DoubleCheckPriceApprove extends Command
{
    private const ID = 'id';

    /**
     *
     * @var Lists
     */
    private $lists;

    /**
     * @var TableFactory
     */
    private $tableHelperFactory;

    /**
     * @var CollectionFactory
     */
    private $productPriceChangeRequestCollectionFactory;

    /**
     * @var ProductPriceChangeRequest
     */
    protected ProductPriceChangeRequest $resource;

    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Data
     */
    private Data $helperModule;

    /**
     * @param Lists $lists
     * @param TableFactory|null $tableHelperFactory
     * @param CollectionFactory $productPriceChangeRequestCollectionFactory
     * @param UserCollectionFactory $userCollectionFactory
     */
    public function __construct(
        Lists $lists,
        TableFactory $tableHelperFactory = null,
        CollectionFactory $productPriceChangeRequestCollectionFactory,
        UserCollectionFactory $userCollectionFactory,
        ProductRepositoryInterface   $productRepository,
        ProductPriceChangeRequest $resource,
        Data                         $helperModule
    ) {
        $this->lists = $lists;
        $this->tableHelperFactory = $tableHelperFactory ?: ObjectManager::getInstance()->create(TableFactory::class);
        $this->productPriceChangeRequestCollectionFactory = $productPriceChangeRequestCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->helperModule = $helperModule;
        parent::__construct();
    }

    /**
     * Initialization of the command.
     */
    protected function configure(): void
    {
        $this->setName('doublecheckprice:approve');
        $this->setDescription('Comando para aprovar requisições de alteração de preço de produtos.');
        $this->addOption(
            self::ID,
            null,
            InputOption::VALUE_REQUIRED,
            'ID da requisição'
        );
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($id = $input->getOption(self::ID)) {
            try {
                $productPriceChangeRequestCollection = $this->productPriceChangeRequestCollectionFactory->create();
                $productPriceChangeRequestCollection->addFieldToFilter('main_table.request_id', $id);
                $requestData = $productPriceChangeRequestCollection->getFirstItem();
                if (count($requestData->getData()) == 0) {
                    $output->writeln(sprintf(
                        '<error>Nenhuma requisição corresponde ao ID: %s</error>',
                        $id
                    ));
                    return Cli::RETURN_FAILURE;
                }
                $productNewPrice = $requestData->getData('new_price');
                $productSku = $requestData->getData('product_sku');

                //$output->writeln('<info>SKU is `' .  . '`</info>');
                $product = $this->productRepository->get($productSku);

                $this->helperModule->runProductPriceUpdateQuery($product->getId(), $productNewPrice);

                //$this->resource->delete($requestData);

                $output->writeln(sprintf(
                    '<info>O Produto: %s teve seu preço alterado para: %f</info>',
                    $productSku,
                    $productNewPrice
                ));

                //$output->writeln('<info>Provided name is `' . $requestData->getData('new_price') . '`</info>');
            } catch (LocalizedException $e) {
                $output->writeln(sprintf(
                    '<error>%s</error>',
                    $e->getMessage()
                ));
            }
        }

        return Cli::RETURN_SUCCESS;
    }
}
