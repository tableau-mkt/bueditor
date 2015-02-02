<?php

/**
 * @file
 * Contains \Drupal\bueditor\Form\BUEditorEditorForm.
 */

namespace Drupal\bueditor\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\String;

/**
 * Base form for BUEditor Editor entities.
 */
class BUEditorEditorForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $bueditor_editor = $this->getEntity();
    // Check duplication
    if ($this->getOperation() === 'duplicate') {
      $bueditor_editor = $bueditor_editor->createDuplicate();
      $bueditor_editor->set('label', $this->t('Duplicate of !label', array('!label' => $bueditor_editor->label())));
      $this->setEntity($bueditor_editor);
    }
    // Label
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $bueditor_editor->label(),
      '#maxlength' => 64,
      '#required' => TRUE,
    );
    // Id
    $form['id'] = array(
      '#type' => 'machine_name',
      '#machine_name' => array(
        'exists' => array(get_class($bueditor_editor), 'load'),
        'source' => array('label'),
      ),
      '#default_value' => $bueditor_editor->id(),
      '#maxlength' => 32,
      '#required' => TRUE,
    );
    // Description
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $bueditor_editor->get('description'),
    );
    // Toolbar
    $widget = $this->getToolbarWidget();
    $widget_libraries = $widget['libraries'];
    unset($widget['libraries']);
    $form['toolbar_config'] = array(
      '#type' => 'details',
      '#title' => $this->t('Toolbar configuration'),
      '#open' => TRUE,
      '#attached' => array(
        'library' => $widget_libraries,
        'drupalSettings' => array('bueditor' => array('twSettings' => $widget)),
      ),
    );
    $form['toolbar_config']['toolbar'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Active toolbar'),
      '#default_value' => implode(', ', $bueditor_editor->getToolbar()),
      '#attributes' => array(
        'class' => array('bueditor-toolbar-input'),
      ),
      '#maxlength' => NULL,
    );
    // Add demo
    if (!$bueditor_editor->isNew()) {
      $formats = array();
      foreach (filter_formats(\Drupal::currentUser()) as $format) {
        $formats[] = '<option value="' . String::checkPlain($format->id()) . '">' . String::checkPlain($format->label()) . '</option>';
      }
      $form['demo']['#markup'] = '<div class="form-item form-type-textarea bueditor-demo"><label>' . $this->t('Demo') . '</label><textarea class="form-textarea" cols="40" rows="5"></textarea><div class="form-item form-type-select filter-wrapper"><span class="label">' . $this->t('Text format') . '</span> <select class="filter-list form-select">' . implode('', $formats) . '</select></div></div>';
      $form['demo']['#weight'] = 1000;
      $form['demo']['#attached']['library'] = $bueditor_editor->getLibraries();
      $form['demo']['#attached']['drupalSettings']['bueditor']['demoSettings'] = $bueditor_editor->getJSSettings();
    }
    $form['#attached']['library'][] = 'bueditor/drupal.bueditor.admin';
    // Allow plugins to add their elements
    $form['plugins'] = array('#tree' => TRUE);
    \Drupal::service('plugin.manager.bueditor.plugin')->alterEditorForm($form, $form_state, $bueditor_editor);
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);
    $toolbar = $form_state->getValue('toolbar');
    // Convert toolbar to array.
    if (is_string($toolbar)) {
      $form_state->setValue('toolbar', array_values(array_filter(array_map('trim', explode(',', $toolbar)))));
    }
    \Drupal::service('plugin.manager.bueditor.plugin')->validateEditorForm($form, $form_state, $this->getEntity());
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $bueditor_editor = $this->getEntity();
    $status = $bueditor_editor->save();
    if ($status == SAVED_NEW) {
      drupal_set_message($this->t('Editor %name has been added.', array('%name' => $bueditor_editor->label())));
    }
    elseif ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The changes have been saved.'));
    }
    $form_state->setRedirect('entity.bueditor_editor.edit_form', array('bueditor_editor' => $bueditor_editor->id()));
  }

  /**
   * Returns toolbar widget data.
   *
   * @return array
   */
  public static function getToolbarWidget() {
    $pm = \Drupal::service('plugin.manager.bueditor.plugin');
    $widget = array('items' => $pm->getButtons(), 'libraries' => array('bueditor/drupal.bueditor.admin'));
    $pm->alterToolbarWidget($widget);
    return $widget;
  }

}
