<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 04/09/18
 * Time: 15:03
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating or updating a customer account.
 */
class CustomerCreateCommand extends Command
{
    /** data keys */
    const KEY_FIRSTNAME = 'customer-firstname';
    const KEY_LASTNAME = 'customer-lastname';
    const KEY_EMAIL = 'customer-email';
    const KEY_PASSWORD = 'customer-password';

    /** @var \Rapicart\CustomerCommands\Model\CustomerValidationRules  */
    protected $validationRules;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory  */
    protected $customerFactory;

    /** @var \Magento\Framework\Encryption\Encryptor  */
    protected $encryptor;

    /**
     * CustomerCreateCommand constructor.
     * @param \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->validationRules = $validationRules;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->encryptor = $encryptor;

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
        $this->setName('customer:create')
            ->setDescription('Create or update a customer account')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Get input options
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(self::KEY_FIRSTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer first name'),
            new InputOption(self::KEY_LASTNAME, null, InputOption::VALUE_REQUIRED, '(Required) Customer last name'),
            new InputOption(self::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, '(Required) Customer email'),
            new InputOption(self::KEY_PASSWORD, null, InputOption::VALUE_REQUIRED, '(Required) Customer password')
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
        $password = $input->getOption(self::KEY_PASSWORD);

        $successMessage = 'Customer account has been created.';

        try {
            $customer = $this->customerRepository->get($email);
            $successMessage = 'Customer account has been updated.';
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customer = $this->customerFactory->create();
        }

        try {
            $this->setCustomerData($customer, $input);
            $this->customerRepository->save($customer, $this->encryptor->getHash($password, true));
            $output->writeln($successMessage);
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

    /**
     * Set input data to customer entity
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param InputInterface $input
     */
    private function setCustomerData($customer, InputInterface $input)
    {
        foreach ($input->getOptions() as $key => $value) {
            if ($key == 'customer-password' || substr($key, 0,8) != 'customer' || $value == '') continue;
            $key = substr($key, 9);
            $customer->{"set$key"}($value);
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
        $data = new \Magento\Framework\DataObject();
        $data->setEmail($input->getOption(self::KEY_EMAIL))
            ->setPassword($input->getOption(self::KEY_PASSWORD));

        $validator = new \Magento\Framework\Validator\DataObject;
        $this->validationRules->addEmailRules($validator);
        $this->validationRules->addPasswordRules($validator);

        if (!$validator->isValid($data)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}
