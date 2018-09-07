<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 04/09/18
 * Time: 17:59
 */

namespace Rapicart\CustomerCommands\Model;

use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\StringLength;

/**
 * Class for adding validation rules to a customer account
 */
class CustomerValidationRules
{
    /** Minimum length of customer password */
    const MIN_PASSWORD_LENGTH = 7;

    /** Maximum length of customer group code */
    const MAX_CUSTOMER_GROUP_LENGTH = 32;

    /**
     * Adds validation rules for customer email
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addEmailRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $emailValidity = new EmailAddress();
        $emailValidity->setMessage(__('Please enter a valid email.'), \Zend_Validate_EmailAddress::INVALID);

        /** @var $validator \Magento\Framework\Validator\DataObject */
        $validator->addRule(
            $emailValidity,
            'email'
        );

        return $validator;
    }

    /**
     * Adds validation rules for user password
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addPasswordRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $passwordNotEmpty = new NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is a required field.'), NotEmpty::IS_EMPTY);
        $minPassLength = self::MIN_PASSWORD_LENGTH;
        $passwordLength = new StringLength(['min' => $minPassLength, 'encoding' => 'UTF-8']);
        $passwordLength->setMessage(
            __('Your password must be at least %1 characters.', $minPassLength),
            \Zend_Validate_StringLength::TOO_SHORT
        );
        $passwordChars = new Regex('/[a-z].*\d|\d.*[a-z]/iu');
        $passwordChars->setMessage(
            __('Your password must include both numeric and alphabetic characters.'),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $validator->addRule(
            $passwordNotEmpty,
            'password'
        )->addRule(
            $passwordLength,
            'password'
        )->addRule(
            $passwordChars,
            'password'
        );

        return $validator;
    }

    /**
     * Adds validation rules for customer group
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addCustomerGroupRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $nameNotEmpty = new NotEmpty();
        $nameNotEmpty->setMessage(__('Group name is a required field.'), NotEmpty::IS_EMPTY);
        $maxGroupNameLength = self::MAX_CUSTOMER_GROUP_LENGTH;
        $groupNameLength = new StringLength(['max' => $maxGroupNameLength, 'encoding' => 'UTF-8']);
        $groupNameLength->setMessage(
            __('Maximum length must be less than %1 characters.', $maxGroupNameLength),
            \Zend_Validate_StringLength::TOO_LONG
        );

        $validator->addRule(
            $nameNotEmpty,
            'group_code'
        )->addRule(
            $groupNameLength,
            'group_code'
        );

        return $validator;
    }
}