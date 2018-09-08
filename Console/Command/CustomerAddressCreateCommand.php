<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 15:31
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating or updating a customer account.
 */
class CustomerAddressCreateCommand extends Command
{
    /** data keys */
    const KEY_EMAIL = 'customer-email';
    const KEY_ADDRESS_ID = 'address-id';
    const KEY_FIRSTNAME = 'address-firstname';
    const KEY_LASTNAME = 'address-lastname';

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
        $this->setName('customer:address:create')
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
            new InputOption(self::KEY_ADDRESS_ID, null, InputOption::VALUE_REQUIRED, '(Required for editing) Address id'),
            new InputOption(self::KEY_FIRSTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer first name'),
            new InputOption(self::KEY_LASTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer last name'),

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
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL .  '<error>', $errors) . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $email = $input->getOption(self::KEY_EMAIL);
        $addressId = $input->getOption(self::KEY_ADDRESS_ID);

        $successMessage = 'Customer account has been created.';

        try {
            $customer = $this->customerRepository->get($email);

            $successMessage = 'Customer address has been updated.';
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('<error>There isn\'t a customer for the given email.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }


        if ($addressId) {
            $address = $this->addressRepository->getById($addressId);
        } else {
            $address = $this->addressFactory->create();
            $address->setCustomerId($customer->getId());
        }

        try {
            $this->setAddressData($address, $input);
            $this->addressRepository->save($address);
            $output->writeln("<info>$successMessage<info>");
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

    private function setAddressData($address, InputInterface $input)
    {
        foreach ($input->getOptions() as $key => $value) {
            if (substr($key, 0,8) != 'customer' || substr($key, 0,8) != 'address' ||$value == '') continue;
            $key = substr($key, 9);
            $address->{"set$key"}($value);
        }
    }

    /**
     * Validate input fields
     * @param InputInterface $input
     * @return array
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
//        $data = new \Magento\Framework\DataObject();
//        $data->setEmail($input->getOption(self::KEY_EMAIL))
//            ->setPassword($input->getOption(self::KEY_PASSWORD));
//
//        $validator = new \Magento\Framework\Validator\DataObject;
//        $this->validationRules->addAddressInfoRules($validator);
//
//        if (!$validator->isValid($data)) {
//            $errors = array_merge($errors, $validator->getMessages());
//        }

        return $errors;
    }
}