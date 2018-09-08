<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 15:57
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for listing customer account details.
 */
class CustomerInfoCommand extends Command
{
    /** data keys */
    const KEY_EMAIL = 'customer-email';
    const KEY_WEBSITE_ID = 'customer-website-id';

    /** @var \Rapicart\CustomerCommands\Model\CustomerValidationRules  */
    protected $validationRules;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface  */
    protected $customerRepository;

    /**
     * CustomerInfoCommand constructor.
     * @param \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->validationRules = $validationRules;
        $this->customerRepository = $customerRepository;

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
        $this->setName('customer:info')
            ->setDescription('Shows customer account details')
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
            new InputOption(self::KEY_WEBSITE_ID, null, InputOption::VALUE_OPTIONAL, '(Optional) Customer website id')
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
        $websiteId = $input->getOption(self::KEY_WEBSITE_ID);

        try {
            $customer = $this->customerRepository->get($email, $websiteId);

            $table = $this->getHelperSet()->get('table');
            $table->setHeaders(['Id', 'First Name', 'Last name', 'Email', 'Group Id', 'Website Id', 'Created At']);

            $table->addRow([
                $customer->getId(),
                $customer->getFirstname(),
                $customer->getLastname(),
                $customer->getEmail(),
                $customer->getGroupId(),
                $customer->getWebsiteId(),
                $customer->getCreatedAt()
            ]);

            $table->render($output);

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $output->writeln('There isn\'t a customer for the given email.');
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

    /**
     * Validate input fields
     * @param InputInterface $input
     * @return array
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $data = new \Magento\Framework\DataObject();
        $data->setEmail($input->getOption(self::KEY_EMAIL));

        $validator = new \Magento\Framework\Validator\DataObject;
        $this->validationRules->addEmailRules($validator);

        if (!$validator->isValid($data)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}