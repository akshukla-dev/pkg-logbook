
<?php
/**
 * @package Logbook
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */


defined('_JEXEC') or die; // No direct access
 

class LogbookViewFolders extends JViewLegacy
{
  protected $items;
  protected $state;
  protected $pagination;
  protected $filterForm;
  protected $activeFilters;

  public function display($tpl = null)
  {
    $this->items = $this->get('Items');
    $this->state = $this->get('State');
    $this->pagination = $this->get('Pagination');
    $this->filterForm = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      JFactory::getApplication()->enqueueMessage($errors, 'error');
      return false;
    }

    //Check if the LMI Record Manager plugin is installed (or if it is enabled). If it doesn't we display an
    //information note.
    if(!JPluginHelper::isEnabled('content', 'logbook')) {
      JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_PLUGIN_NOT_INSTALLED'), 'warning');
    }

    $user = JFactory::getUser();

    //Display the toolbar and the sidebar.
    $this->addToolBar();
    $this->sidebar = JHtmlSidebar::render();

    //Display the template.
    parent::display($tpl);
  }


  protected function addToolBar() 
  {
    JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_FOLDERS'), 'folder-2');

    require_once JPATH_COMPONENT.'/helpers/logbook.php';
    $canDo = LogbookHelper::getActions();

    if($canDo->get('core.edit.state')) { 
      JToolBarHelper::custom('folders.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
      JToolBarHelper::divider();
    }

    if($canDo->get('core.delete')) {
      JToolBarHelper::deleteList(JText::_('COM_LOGBOOK_DELETE_CONFIRMATION'), 'folders.delete', 'JTOOLBAR_DELETE');
      JToolBarHelper::divider();
    }

    if($canDo->get('core.admin')) {
      JToolBarHelper::preferences('com_logbook', 550);
    }
  }
}