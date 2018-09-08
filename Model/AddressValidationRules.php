<?php
/**
 * Created by PhpStorm.
 * Company: Rapicart
 * Web: https://www.rapicart.com
 * User: Pablo Garcia
 * Email: pablo.garcia@rapicart.com
 * Date: 07/09/18
 * Time: 19:37
 */

namespace Rapicart\CustomerCommands\Model;

use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\StringLength;

/**
 * Class for adding validation rules to a customer address
 */
class AddressValidationRules
{
    public function addAddressInfoRules(\Magento\Framework\Validator\DataObject $validator)
    {
        return $validator;
    }
}