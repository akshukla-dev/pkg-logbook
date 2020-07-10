<?php
/**
 * @copyright   Copyright (C)2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Locations Controller.
 *
 * @since  0.0.1
 */
class LogbookControllerLocations extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return object the model
     *
     * @since   1.6
     */
    public function getModel($name = 'Location', $prefix = 'LogbookModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
