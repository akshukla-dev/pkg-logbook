<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('LogmoniterModelWatchdogs', __DIR__.'/watchdogs.php');

/**
 * Logmpniter Component Moniter Model.
 *
 * @since  1.5
 */
class LogmoniterModelMoniter extends LogmoniterModelWatchdogs
{
    /**
     * Model context string.
     *
     * @var string
     */
    public $_context = 'com_logmoniter.moniter';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  the field to order on
     * @param string $direction the direction to order on
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState();

        $app = JFactory::getApplication();

        // Add archive properties
        $params = $this->state->params;

        // Filter on month, year
        $this->setState('filter.month', $app->input->getInt('month'));
        $this->setState('filter.year', $app->input->getInt('year'));

        //Filter by work center, blueprint,  lmi, tinterval
        $this->setState('filter.wcenter', $app->input->getInt('wcenter'));
        $this->setState('filter.inset', $app->input->getInt('inset'));
        $this->setState('filter.bprint', $app->input->getInt('bprint'));
        $this->setState('filter.tinterval', $app->input->getInt('tinterval'));

        // Optional filter text
        $this->setState('list.filter', $app->input->getString('filter-search'));

        // Get list limit
        $itemid = $app->input->get('Itemid', 0, 'int');
        $limit = $app->getUserStateFromRequest('com_logmoniter.moniter.list'.$itemid.'.limit', 'limit', $params->get('display_num'), 'uint');
        $this->setState('list.limit', $limit);

        // Set the moniter ordering
        $watchdogOrderby = $params->get('orderby_sec', 'rdate');
        $watchdogOrderDate = $params->get('order_date');

        // No category ordering
        $secondary = LogmoniterHelperQuery::orderbySecondary($watchdogOrderby, $watchdogOrderDate);

        $this->setState('list.ordering', $secondary.', w.created DESC');
        $this->setState('list.direction', '');
    }

    /**
     * Get the master query for retrieving a list of watchdogs subject to the model state.
     *
     * @return JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $params = $this->state->params;
        $watchdogOrderDate = $params->get('order_date');

        // Create a new query object.
        $query = parent::getListQuery();

        // Add routing for moniter
        // Sqlsrv changes
        $case_when = ' CASE WHEN ';
        $case_when .= $query->charLength('w.alias', '!=', '0');
        $case_when .= ' THEN ';
        $l_id = $query->castAsChar('w.id');
        $case_when .= $query->concatenate(array($l_id, 'w.alias'), ':');
        $case_when .= ' ELSE ';
        $case_when .= $l_id.' END as slug';

        $query->select($case_when);

        $case_when = ' CASE WHEN ';
        $case_when .= $query->charLength('c.alias', '!=', '0');
        $case_when .= ' THEN ';
        $c_id = $query->castAsChar('c.id');
        $case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
        $case_when .= ' ELSE ';
        $case_when .= $c_id.' END as catslug';
        $query->select($case_when);

        // Filter on month, year
        // First, get the date field
        $queryDate = LogmoniterHelperQuery::getQueryDate($watchdogOrderDate);

        if ($month = $this->getState('filter.month')) {
            $query->where($query->month($queryDate).' = '.$month);
        }

        if ($year = $this->getState('filter.year')) {
            $query->where($query->year($queryDate).' = '.$year);
        }

        // Filter on work-center, lmi, blueprint & time Interval

        if ($wcenter = $this->getState('filter.wcenter')) {
            $query->where('w.wcid = '.$wcenter);
        }

        if ($inset = $this->getState('filter.inset')) {
            $query->where('w.isid = '.$inset);
        }

        if ($bprint = $this->getState('filter.bprint')) {
            $query->where('w.bpid = '.$bprint);
        }

        if ($tinterval = $this->getState('filter.tinterval')) {
            $query->where('w.tiid = '.$tinterval);
        }

        return $query;
    }

    /**
     * Method to get the archived watchdog list.
     *
     * @return array
     */
    public function getData()
    {
        $app = JFactory::getApplication();

        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            // Get the page/component configuration
            $params = $app->getParams();

            // Get the pagination request variables
            $limit = $app->input->get('limit', $params->get('display_num', 20), 'uint');
            $limitstart = $app->input->get('limitstart', 0, 'uint');

            $query = $this->_buildQuery();

            $this->_data = $this->_getList($query, $limitstart, $limit);
        }

        return $this->_data;
    }

    /**
     * JModelLegacy override to add alternating value for $odd.
     *
     * @param string $query      the query
     * @param int    $limitstart offset
     * @param int    $limit      the number of records
     *
     * @return array an array of results
     *
     * @since   12.2
     *
     * @throws RuntimeException
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $result = parent::_getList($query, $limitstart, $limit);

        $odd = 1;

        foreach ($result as $k => $row) {
            $result[$k]->odd = $odd;
            $odd = 1 - $odd;
        }

        return $result;
    }

    /**
     * Gets the archived watchdogs years.
     *
     * @return array
     *
     * @since    3.6.0
     */
    public function getYears()
    {
        $db = $this->getDbo();
        $nullDate = $db->quote($db->getNullDate());
        $nowDate = $db->quote(JFactory::getDate()->toSql());

        $query = $db->getQuery(true);
        $years = $query->year($db->qn('created'));
        $query->select('DISTINCT ('.$years.')')
            ->from($db->qn('#__logbook_watchdogs'))
            ->where($db->qn('state').'= 1')
            ->where('(publish_up = '.$nullDate.' OR publish_up <= '.$nowDate.')')
            ->where('(publish_down = '.$nullDate.' OR publish_down >= '.$nowDate.')')
            ->order('1 ASC');

        $db->setQuery($query);

        return $db->loadColumn();
    }
}
