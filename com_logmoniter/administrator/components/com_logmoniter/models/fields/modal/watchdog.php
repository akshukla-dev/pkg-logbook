<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_logmoniter
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
/**
 * Supports a modal watchdog picker.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldModal_Watchdog extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Modal_Watchdog';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$allowNew    = ((string) $this->element['new'] == 'true');
		$allowEdit   = ((string) $this->element['edit'] == 'true');
		$allowClear  = ((string) $this->element['clear'] != 'false');
		$allowSelect = ((string) $this->element['select'] != 'false');

		// Load language
		JFactory::getLanguage()->load('com_logmoniter', JPATH_ADMINISTRATOR);

		// The active watchdog id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Watchdog_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				JFactory::getDocument()->addScriptDeclaration("
				function jSelectWatchdog_" . $this->id . "(id, title, catid, object, url, language) {
					window.processModalSelect('Watchdog', '" . $this->id . "', id, title, catid, object, url, language);
				}
				");
				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkWatchdogs = 'index.php?option=com_logmoniter&amp;view=watchdogs&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$linkWatchdog  = 'index.php?option=com_logmoniter&amp;view=watchdog&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$modalTitle   = JText::_('COM_LOGMONITER_CHANGE_WATCHDOG');

		if (isset($this->element['language']))
		{
			$linkWatchdogs .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkWatchdog  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle   .= ' &#8212; ' . $this->element['label'];
		}

		$urlSelect = $linkWatchdogs . '&amp;function=jSelectWatchdog_' . $this->id;
		$urlEdit   = $linkWatchdog . '&amp;task=watchdog.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkWatchdog . '&amp;task=watchdog.add';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__logbook_watchdogs'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);
			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}
		$title = empty($title) ? JText::_('COM_WEBLINKS_SELECT_A_WEBLINK') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current watchdog display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select watchdog button
		if ($allowSelect)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalSelect' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_WEBLINKS_CHANGE_WEBLINK') . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
				. '</a>';
		}
		// New watchdog button
		if ($allowNew)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalNew' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_WEBLINKS_NEW_WEBLINK') . '">'
				. '<span class="icon-new" aria-hidden="true"></span> ' . JText::_('JACTION_CREATE')
				. '</a>';
		}
		// Edit watchdog button
		if ($allowEdit)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalEdit' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_WEBLINKS_EDIT_WEBLINK') . '">'
				. '<span class="icon-edit" aria-hidden="true"></span> ' . JText::_('JACTION_EDIT')
				. '</a>';
		}
		// Clear watchdog button
		if ($allowClear)
		{
			$html .= '<a'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' href="#"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
				. '</a>';
		}
		$html .= '</span>';

		// Select watchdog modal
		if ($allowSelect)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
				)
			);
		}

		// New watchdog modal
		if ($allowNew)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => JText::_('COM_WEBLINKS_NEW_WEBLINK'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'watchdog\', \'cancel\', \'watchdog-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'watchdog\', \'save\', \'watchdog-form\'); return false;">'
							. JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'watchdog\', \'apply\', \'watchdog-form\'); return false;">'
							. JText::_('JAPPLY') . '</a>',
				)
			);
		}

		// Edit watchdog modal
		if ($allowEdit)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => JText::_('COM_WEBLINKS_EDIT_WEBLINK'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'watchdog\', \'cancel\', \'watchdog-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'watchdog\', \'save\', \'watchdog-form\'); return false;">'
							. JText::_('JSAVE') . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'watchdog\', \'apply\', \'watchdog-form\'); return false;">'
							. JText::_('JAPPLY') . '</a>',
				)
			);
		}
		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';
		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('COM_WEBLINKS_SELECT_A_WEBLINK', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';
		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
