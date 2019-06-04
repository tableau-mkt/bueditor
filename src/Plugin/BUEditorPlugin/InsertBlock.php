<?php

namespace Drupal\bueditor\Plugin\BUEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\bueditor\BUEditorPluginBase;
use Drupal\bueditor\Entity\BUEditorEditor;
use Drupal\bueditor\BUEditorToolbarWrapper;

/**
 * Defines BUEditor Embedded Views plugin.
 *
 * @BUEditorPlugin(
 *   id = "insertblock",
 *   label = "Insert Block"
 * )
 */
class InsertBlock extends BUEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'insertblock' => $this->t('Insert Block'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterEditorJS(array &$js, BUEditorEditor $bueditor_editor, Editor $editor = NULL) {
    $toolbar = BUEditorToolbarWrapper::set($js['settings']['toolbar']);
    // Check drupal views button.
    if ($toolbar->has('insertblock')) {
        $js['libraries'][] = 'bueditor/drupal.bueditor.insertblock';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterToolbarWidget(array &$widget) {
    // Make  drupalviews definition available to toolbar widget
    $widget['libraries'][] = 'bueditor/drupal.bueditor.insertblock';
  }

}
