<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla formrule library
jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleLogYears extends JFormRule
{
    /**
     * The regular expression.
     *
     * @var string
     *
     * @since	2.5
     */
    //Any number except zero or numbers beginning with zero.
    protected $regex = '^[1-9][0-9]{0,}$';
}
