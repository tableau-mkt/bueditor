<?php

/**
 * @file
 * Contains \Drupal\bueditor\Plugin\BUEditorPlugin\XPreview.
 */

namespace Drupal\bueditor\Plugin\BUEditorPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;
use Drupal\bueditor\BUEditorPluginBase;
use Drupal\bueditor\Entity\BUEditorEditor;
use Drupal\bueditor\BUEditorToolbarWrapper;

/**
 * Defines BUEditor Ajax Preview plugin.
 *
 * @BUEditorPlugin(
 *   id = "xpreview",
 *   label = "Ajax Preview"
 * )
 */
class XPreview extends BUEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return array(
      'xpreview' => $this->t('Preview'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function alterEditorJS(array &$data, BUEditorEditor $bueditor_editor, Editor $editor = NULL) {
    $toolbar = BUEditorToolbarWrapper::set($data['settings']['toolbar']);
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
  }

  /**
   * {@inheritdoc}
   */
  public function alterToolbarWidget(array &$widget) {
    // Make xpreview definition available to toolbar widget
    $widget['libraries'][] = 'bueditor/drupal.bueditor.xpreview';
    // Add a tooltip
    $widget['items']['xpreview']['tooltip'] = $this->t('Requires ajax preview permission.');
  }

  /**
   * {@inheritdoc}
   */
  public function validateEditorForm(array &$form, FormStateInterface $form_state, BUEditorEditor $bueditor_editor) {
    // Warn about XPreview permission if it is newly activated.
    if (!$form_state->getErrors()) {
      if (!$bueditor_editor->hasToolbarItem('xpreview') && in_array('xpreview', $form_state->getValue(array('settings', 'toolbar')))) {
        $msg = $this->t('Ajax preview button has been enabled.') . ' ' . $this->t('Please check the <a href="@url">required permissions</a>.', array('@url' => \Drupal::url('user.admin_permissions')));
        drupal_set_message($msg);
      }
    }
  }

}
