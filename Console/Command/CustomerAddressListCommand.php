<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 19:52
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for listing customer addresses.
 */
class CustomerAddressListCommand extends Command
{
    /** data keys */
    const KEY_EMAIL = 'customer-email';

    /** @var \Rapicart\CustomerCommands\Model\AddressValidationRules  */
    protected $validationRules;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface  */
    protected $addressRepository;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory  */
    protected $addressFactory;

    public function __construct(
        \Rapicart\CustomerCommands\Model\AddressValidationRules $validationRules,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        //\Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->validationRules = $validationRules;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        //$this->encryptor = $encryptor;

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
        $this->setName('customer:address:list')
            ->setDescription('Create or update a customer address account')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Get input options
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(self::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, '(Required) Customer email'),
//            new InputOption(self::KEY_FIRSTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer first name'),
//            new InputOption(self::KEY_LASTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer last name'),

//            new InputOption(self::KEY_PASSWORD, null, InputOption::VALUE_REQUIRED, '(Required) Customer password')
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
        $email = $input->getOption(self::KEY_EMAIL);

        try {
            $customer = $this->customerRepository->get($email);

            $table = $this->getHelperSet()->get('table');
            $table->setHeaders(['Id', 'First Name', 'Last Name', 'Street', 'City', 'Region', 'Country', 'ZipCode', 'Phone']);

            foreach ($customer->getAddresses() as $address) {
                $table->addRow([
                    $address->getId(),
                    $address->getFirstname(),
                    $address->getLastname(),
                    implode(",",$address->getStreet()),
                    $address->getCity(),
                    $address->getRegion()->getRegionCode(),
                    $address->getCountryId(),
                    $address->getPostcode(),
                    $address->getTelephone()
                ]);
            }

            $table->render($output);

//            foreach ($customer->getAddresses() as $address) {
//                $output->writeln($address->getFirstname());
//
//                //var_dump($address->getCustomAttribute('celular'));
//                foreach ($address->getCustomAttributes() as $attribute) {
//                    var_dump($attribute->getAttributeCode());
//                }
//            }
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    "<error>An error has occurred: %s</error>",
                    $e->getMessage()
                )
            );

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}