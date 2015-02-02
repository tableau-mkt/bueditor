<?php

/**
 * @file
 * Contains \Drupal\bueditor\Entity\BUEditorEditor.
 */

namespace Drupal\bueditor\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the BUEditor Editor entity.
 *
 * @ConfigEntityType(
 *   id = "bueditor_editor",
 *   label = @Translation("BUEditor Editor"),
 *   handlers = {
 *     "list_builder" = "Drupal\bueditor\BUEditorEditorListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bueditor\Form\BUEditorEditorForm",
 *       "edit" = "Drupal\bueditor\Form\BUEditorEditorForm",
 *       "delete" = "Drupal\bueditor\Form\BUEditorEditorDeleteForm",
 *       "duplicate" = "Drupal\bueditor\Form\BUEditorEditorForm"
 *     }
 *   },
 *   admin_permission = "administer bueditor",
 *   config_prefix = "editor",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/bueditor/{bueditor_editor}",
 *     "delete-form" = "/admin/config/content/bueditor/{bueditor_editor}/delete",
 *     "duplicate-form" = "/admin/config/content/bueditor/{bueditor_editor}/duplicate"
 *   }
 * )
 */
class BUEditorEditor extends ConfigEntityBase {

  /**
   * Editor ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Label.
   *
   * @var string
   */
  protected $label;

  /**
   * Description.
   *
   * @var string
   */
  protected $description;

  /**
   * Toolbar.
   *
   * @var array
   */
  protected $toolbar = array();

  /**
   * Plugins.
   *
   * @var array
   */
  protected $plugins = array();

  /**
   * Javascript data including settings and libraries.
   *
   * @var array
   */
  protected $jsData;

  /**
   * Returns a specific setting or all settings of a plugin.
   */
  public function getPluginSettings($plugin = NULL, $key = NULL, $default = NULL) {
    if (!isset($plugin)) {
      return $this->plugins;
    }
    $settings = isset($this->plugins[$plugin]) ? $this->plugins[$plugin] : array();
    if (isset($key)) {
      return isset($settings[$key]) ? $settings[$key] : $default;
    }
    return $settings;
  }

  /**
   * Returns the toolbar array.
   */
  public function getToolbar() {
    return $this->toolbar;
  }

  /**
   * Checks if an item exists in the toolbar.
   */
  public function hasToolbarItem($id) {
    return in_array($id, $this->toolbar, TRUE);
  }

  /**
   * Returns JS libraries.
   */
  public function getLibraries(Editor $editor = NULL) {
    $data = $this->getJSData($editor);
    return $data['libraries'];
  }

  /**
   * Returns JS settings.
   */
  public function getJSSettings(Editor $editor = NULL) {
    $data = $this->getJSData($editor);
    return $data['settings'];
  }

  /**
   * Returns JS data including settings and libraries.
   */
  public function getJSData(Editor $editor = NULL) {
    if (!isset($this->jsData)) {
      $this->jsData = array(
        'libraries' => array('bueditor/drupal.bueditor'),
        'settings' => array('toolbar' => $this->getToolbar()),
      );
      \Drupal::service('plugin.manager.bueditor.plugin')->alterEditorJS($this->jsData, $this, $editor);
    }
    return $this->jsData;
  }

}
