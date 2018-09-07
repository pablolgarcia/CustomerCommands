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
 * Command for updating password of a customer account.
 */
class UpdatePasswordCommand extends Command
{
    /** data keys */
    const KEY_EMAIL = 'customer-email';
    const KEY_PASSWORD = 'customer-password';

    /** @var \Rapicart\CustomerCommands\Model\CustomerValidationRules  */
    protected $validationRules;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var \Magento\Framework\Encryption\Encryptor  */
    protected $encryptor;

    /**
     * UpdatePasswordCommand constructor.
     * @param \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->validationRules = $validationRules;
        $this->customerRepository = $customerRepository;
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
        $this->setName('customer:password:update')
            ->setDescription('Update password for a given customer')
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

        try {
            $customer = $this->customerRepository->get($email);
            $this->customerRepository->save($customer, $this->encryptor->getHash($password, true));
            $output->writeln('Password updated.');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('<error>Email doesn\'t exist.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
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
