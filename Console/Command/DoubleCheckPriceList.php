<?php

namespace Adriano\Teste\Console\Command;

use Adriano\Teste\Model\ResourceModel\ProductPriceChangeRequest\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Setup\Lists;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DoubleCheckPriceList extends Command
{
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
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

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
    ) {
        $this->lists = $lists;
        $this->tableHelperFactory = $tableHelperFactory ?: ObjectManager::getInstance()->create(TableFactory::class);
        $this->productPriceChangeRequestCollectionFactory = $productPriceChangeRequestCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        parent::__construct();
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('doublecheckprice:list');
        $this->setDescription('Comandos para listar e aprovar requisições de alteração de preço de produtos.');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tableHelper = $this->tableHelperFactory->create(['output' => $output]);
        $tableHelper->setHeaders(['ID', 'Nome', 'SKU', 'Data', 'Atributo', 'Valor Anterior', 'Valor requisitado']);

        $productPriceChangeRequestCollection = $this->productPriceChangeRequestCollectionFactory->create();
        foreach ($productPriceChangeRequestCollection as $row) {
            $collection = $this->userCollectionFactory->create();
            $collection->addFieldToFilter('main_table.user_id', $row->getData('requested_user_id'));
            $userData = $collection->getFirstItem();
            $tableHelper->addRow([
                $row->getData('request_id'),
                $userData->getData('firstname') . ' ' . $userData->getData('lastname'),
                $row->getData('product_sku'),
                $row->getData('requested_at'),
                $row->getData('attributte'),
                $row->getData('old_price'),
                $row->getData('new_price'),
            ]);
        }

        $tableHelper->render();
        return Cli::RETURN_SUCCESS;
    }
}
