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
    /**
     * Minimum length of customer password
     */
    const MIN_PASSWORD_LENGTH = 7;

    /**
     * Adds validation rule for customer email
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
     * Adds validation rule for user password
     * @param \Magento\Framework\Validator\DataObject $validator
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addPasswordRules(\Magento\Framework\Validator\DataObject $validator)
    {
        $passwordNotEmpty = new NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is required field.'), NotEmpty::IS_EMPTY);
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
}