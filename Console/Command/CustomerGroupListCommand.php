<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 14:07
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for listing the existing customer groups.
 */
class CustomerGroupListCommand extends Command
{
    /** @var \Magento\Customer\Api\GroupRepositoryInterface  */
    protected $groupRepository;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder  */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();

        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        try {
            $appState->setAreaCode('adminhtml');
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            ;
        }
    }

    /**
     * Initialization of the command
     * @return void
     */
    protected function configure()
    {
        $this->setName('customer:group:list')
            ->setDescription('Displays the list of customer groups');
    }

    /**
     * Execute command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();

            $groupSearchResults = $this->groupRepository->getList($searchCriteria);

            if($groupSearchResults->getTotalCount()) {
                $table = $this->getHelperSet()->get('table');
                $table->setHeaders(['Id', 'Group Name', 'Tax Class']);

                foreach ($groupSearchResults->getItems() as $customerGroup) {
                    $table->addRow([
                        $customerGroup->getId(),
                        $customerGroup->getCode(),
                        $customerGroup->getTaxClassName()
                    ]);
                }

                $table->render($output);
            } else {
                $output->writeln('No groups has been found.');
            }

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}