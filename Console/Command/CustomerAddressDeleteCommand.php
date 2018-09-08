<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 21:20
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for deleting a customer address.
 */
class CustomerAddressDeleteCommand extends Command
{
    /** data keys */
    const KEY_ADDRESS_ID = 'address-id';

    /** @var \Magento\Customer\Api\AddressRepositoryInterface  */
    protected $addressRepository;

    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->addressRepository = $addressRepository;

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
        $this->setName('customer:address:delete')
            ->setDescription('Delete a customer address')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Get input options
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(self::KEY_ADDRESS_ID, null, InputOption::VALUE_REQUIRED, '(Required) Address id'),
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
            $customerAddressId = $input->getOption(self::KEY_ADDRESS_ID);

            if(!$customerAddressId) {
                $output->writeln('<error>Customer Address id is a required field.</error>');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            $this->addressRepository->deleteById($customerAddressId);

            $output->writeln('Customer address has been deleted.');

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('<error>There isn\'t a customer address for the given id</error>');

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