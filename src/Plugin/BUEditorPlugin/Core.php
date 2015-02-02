<?php

/**
 * @file
 * Contains \Drupal\bueditor\Plugin\BUEditorPlugin\Core.
 */

namespace Drupal\bueditor\Plugin\BUEditorPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;
use Drupal\Component\Utility\String;
use Drupal\bueditor\BUEditorPluginBase;
use Drupal\bueditor\Entity\BUEditorEditor;
use Drupal\bueditor\BUEditorToolbarWrapper;

/**
 * Defines BUEditor Core plugin.
 *
 * @BUEditorPlugin(
 *   id = "core",
 *   label = "BUEditor Core",
 *   weight = -10
 * )
 */
class Core extends BUEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    // Buttons in core library
    $buttons = array(
      '-' => $this->t('Separator'),
      '/' => $this->t('New line'),
      'bold' => $this->t('Bold'),
      'italic' => $this->t('Italic'),
      'underline' => $this->t('Underline'),
      'strike' => $this->t('Strikethrough'),
      'quote' => $this->t('Quote'),
      'code' => $this->t('Code'),
      'ul' => $this->t('Bulleted list'),
      'ol' => $this->t('Numbered list'),
      'link' => $this->t('Link'),
      'image' => $this->t('Image'),
      'undo' => $this->t('Undo'),
      'redo' => $this->t('Redo'),
    );
    for ($i = 1; $i < 7; $i++) {
      $buttons['h' . $i] = $this->t('Heading !n', array('!n' => $i));
    }
    // Module buttons.
    $buttons['xpreview'] = $this->t('Preview');
    return $buttons;
  }

  /**
   * {@inheritdoc}
   */
  public function alterEditorJS(array &$data, BUEditorEditor $bueditor_editor, Editor $editor = NULL) {
    // Add translation library for multilingual sites.
    $lang = \Drupal::service('language_manager')->getCurrentLanguage()->getId();
    if ($lang !== 'en' && \Drupal::service('module_handler')->moduleExists('locale')) {
      $data['libraries'][] = 'bueditor/drupal.bueditor.translation';
    }
    // Add custom button definitions and libraries.
    $toolbar = BUEditorToolbarWrapper::set($data['settings']['toolbar']);
    if ($custom_items = $toolbar->match('custom_')) {
      foreach (entity_load_multiple('bueditor_button', $custom_items) as $bid => $button) {
        $data['settings']['customButtons'][$bid] = $button->jsProperties();
        foreach ($button->get('libraries') as $library) {
          $data['libraries'][] = $library;
        }
      }
    }
    // Check ajax preview button.
    if ($toolbar->has('xpreview')) {
      // Check access and add the library
      if (\Drupal::currentUser()->hasPermission('access ajax preview')) {
        $data['libraries'][] = 'bueditor/drupal.bueditor.xpreview';
      }
      else {
        $toolbar->remove('xpreview');
      }
    }
    // Add plugin settings
    $settings = array_filter($bueditor_editor->getPluginSettings('core'));
    $settings['cname'] = 'bue--' . $bueditor_editor->id() . (isset($settings['cname']) ? ' ' . $settings['cname'] : '');
    $data['settings'] += $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function alterToolbarWidget(array &$widget) {
    // Add custom button definitions.
    foreach (entity_load_multiple('bueditor_button') as $bid => $button) {
      $item = $button->jsProperties();
      // Define template buttons as normal buttons with a special class name.
      if (!empty($item['template']) && empty($item['code'])) {
        $item['cname'] = 'template-button ficon-template' . (!empty($item['cname']) ? ' ' . $item['cname'] : '');
        $item['text'] = '<span class="template-button-text">' . (empty($item['text']) ? String::checkPlain($item['label']) : $item['text']) . '</span>';
        $item['label'] = '[' . $this->t('Template') . ']' . $item['label'];
        $item['multiple'] = TRUE;
      }
      // Remove unneeded properties.
      unset($item['template'], $item['code']);
      $widget['items'][$bid] = $item;
    }
    // Make xpreview definition available to toolbar widget
    $widget['libraries'][] = 'bueditor/drupal.bueditor.xpreview';
    // Add a tooltip for xpreview.
    $widget['items']['xpreview']['tooltip'] = $this->t('Requires ajax preview permission.');
  }

  /**
   * {@inheritdoc}
   */
  public function alterEditorForm(array &$form, FormStateInterface $form_state, BUEditorEditor $bueditor_editor) {
    $form['plugins']['core'] = array(
      '#type' => 'details',
      '#title' => $this->t('Core settings'),
    );
    $form['plugins']['core'] += $this->getForm($form_state, $bueditor_editor);
  }

  /**
   * {@inheritdoc}
   */
  public function validateEditorForm(array &$form, FormStateInterface $form_state, BUEditorEditor $bueditor_editor) {
    // Check class name
    $cname = $form_state->getValue(array('plugins', 'core', 'cname'));
    if (!empty($cname) && preg_match('/[^a-zA-Z0-9\-_ ]/', $cname)) {
      $form_state->setError($form['plugins']['core']['cname'], $this->t('Class name is invalid.'));
    }
    // Warn about XPreview permission if it is newly activated.
    if (!$form_state->getErrors()) {
      if (!$bueditor_editor->hasToolbarItem('xpreview') && in_array('xpreview', $form_state->getValue('toolbar'))) {
        $msg = $this->t('Ajax preview button has been enabled.') . ' ' . $this->t('Please check the <a href="@url">required permissions</a>.', array('@url' => \Drupal::url('user.admin_permissions')));
        drupal_set_message($msg);
      }
    }
  }

  /**
   * Returns core settings form.
   */
  public function getForm(FormStateInterface $form_state, BUEditorEditor $bueditor_editor) {
    // Class name
    $form['cname'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Class name'),
      '#default_value' => $bueditor_editor->getPluginSettings('core', 'cname'),
      '#description' => $this->t('Additional class name for the editor element.'),
    );
    // Indentation
    $form['indent'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable indentation'),
      '#default_value' => $bueditor_editor->getPluginSettings('core', 'indent'),
      '#description' => $this->t('Enable 2 spaces indent by <kbd>TAB</kbd>, unindent by <kbd>Shift+TAB</kbd>, and auto-indent by <kbd>ENTER</kbd>. Once enabled it can be turned on/off dynamically by <kbd>Ctrl+Alt+TAB</kbd>.'),
    );
    // Autocomplete HTML tags
    $form['acTags'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Autocomplete HTML tags'),
      '#default_value' => $bueditor_editor->getPluginSettings('core', 'acTags'),
      '#description' => $this->t('Automatically insert html closing tags.'),
    );
    return $form;
  }

}
