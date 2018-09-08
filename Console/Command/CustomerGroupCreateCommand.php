<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 10:39
 */

namespace Rapicart\CustomerCommands\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating a customer group.
 */
class CustomerGroupCreateCommand extends Command
{
    /** data keys */
    const KEY_CUSTOMER_GROUP_CODE = 'customer-group-code';

    /** @var \Rapicart\CustomerCommands\Model\CustomerValidationRules  */
    protected $validationRules;

    /** @var \Magento\Customer\Api\GroupRepositoryInterface  */
    protected $groupRepository;

    /** @var \Magento\Customer\Api\Data\GroupInterfaceFactory  */
    protected $groupFactory;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder  */
    protected $searchCriteriaBuilder;

    /**
     * CustomerGroupCreateCommand constructor.
     * @param \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\GroupInterfaceFactory $groupFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Rapicart\CustomerCommands\Model\CustomerValidationRules $validationRules,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\GroupInterfaceFactory $groupFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct();
        $this->validationRules = $validationRules;
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
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
        $this->setName('customer:group:create')
            ->setDescription('Create or update a customer group')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Get input options
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(self::KEY_CUSTOMER_GROUP_CODE, null, InputOption::VALUE_REQUIRED, '(Required) Customer group name'),
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
        $successMessage = 'Customer group has been created.';

        try {
            $errors = $this->validate($input);
            if ($errors) {
                $output->writeln('<error>' . implode('</error>' . PHP_EOL .  '<error>', $errors) . '</error>');
                // we must have an exit code higher than zero to indicate something was wrong
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            $customerGroupCode = $input->getOption(self::KEY_CUSTOMER_GROUP_CODE);
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'customer_group_code',
                $customerGroupCode,
                'like'
            )->create();

            $groupSearchResults = $this->groupRepository->getList($searchCriteria);

            if($groupSearchResults->getTotalCount()) {
                $output->writeln("<error>Already exists a group for the given group name.</error>");

                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }

            $customerGroup = $this->groupFactory->create();

            $customerGroup->setCode($customerGroupCode);
            $customerGroup->setTaxClassId(3); //Default tax class id for customers

            $this->groupRepository->save($customerGroup);

            $output->writeln("<info>$successMessage</info>");
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
     * Validate input fields
     * @param InputInterface $input
     * @return array
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $data = new \Magento\Framework\DataObject();
        $data->setGroupCode($input->getOption(self::KEY_CUSTOMER_GROUP_CODE));

        $validator = new \Magento\Framework\Validator\DataObject;
        $this->validationRules->addCustomerGroupRules($validator);

        if (!$validator->isValid($data)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}