<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 14:35
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for deleting a customer groups.
 */
class CustomerGroupDeleteCommand extends Command
{
    /** data keys */
    const KEY_CUSTOMER_GROUP_ID = 'customer-group-id';

    /** @var \Magento\Customer\Api\GroupRepositoryInterface  */
    protected $groupRepository;


    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->groupRepository = $groupRepository;

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
        $this->setName('customer:group:delete')
            ->setDescription('Delete a customer group')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Get input options
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(self::KEY_CUSTOMER_GROUP_ID, null, InputOption::VALUE_REQUIRED, '(Required) Customer group name'),
        ];
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
            $customerGroupId = $input->getOption(self::KEY_CUSTOMER_GROUP_ID);

            if(!$customerGroupId) {
                $output->writeln('<error>Customer group id is a required field.</error>');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            $this->groupRepository->deleteById($customerGroupId);

            $output->writeln('Customer group has been deleted.');

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('<error>There isn\'t a customer group for the given id</error>');

        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    "<error>An error has occurred: %s</error>",
                    $e->getMessage()
                )
            );

        }
        return \Magento\Framework\Console\Cli::RETURN_FAILURE;
    }
}