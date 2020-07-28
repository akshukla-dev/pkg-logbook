<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Watchdogs list controller class.
 *
 * @since  1.6
 */
class LogmoniterControllerWatchdogs extends JControllerAdmin
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
    public function getModel($name = 'Watchdog', $prefix = 'LogmoniterModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
