<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die; //No direct access to this file.

/**
 * Logs list controller class.
 *
 * @since  1.6
 */
class LogbookControllerLogs extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config The array of possible config values. Optional.
     *
     * @return JModelLegacy
     *
     * @since   1.6
     */
    public function getModel($name = 'Log', $prefix = 'LogbookModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
