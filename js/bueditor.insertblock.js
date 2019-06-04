(function ($, Drupal, BUE) {
  'use strict';

  /**
   * @file
   * Defines Views embed button for BUEditor.
   */

  /**
   * Register buttons.
   */
  BUE.registerButtons('bueditor.insertblock', function() {
    return {
      drupalViews: {
        id: 'insertblock',
        label: Drupal.t('Block'),
        text: 'Block',
        code: BUE.block
      }
    };
  });

  /**
   * Previews editor content asynchronously.
   */
  var bueBlock = BUE.block = function(E) {
    E.tokenDialog('block', [
      {name: 'block', title: BUE.t('Block id'), required: true}
    ], BUE.t('Insert Block Token'));
  };
})(jQuery, Drupal, BUE);
