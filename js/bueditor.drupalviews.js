(function ($, Drupal, BUE) {
  'use strict';

  /**
   * @file
   * Defines Views embed button for BUEditor.
   */

  /**
   * Register buttons.
   */
  BUE.registerButtons('bueditor.drupalviews', function() {
    return {
      drupalViews: {
        id: 'drupalviews',
        label: Drupal.t('Views'),
        text: 'Views',
        code: BUE.views
      }
    };
  });

  /**
   * Previews editor content asynchronously.
   */
  var bueViews = BUE.views = function(E) {
    E.tokenDialog('view', [
      {name: 'view', title: BUE.t('View id'), required: true},
      {name: 'display', title: BUE.t('Display id'), required: true},
      {name: 'args', title: BUE.t('Args'), required: true}
    ], BUE.t('View Embed Token'));
  };
})(jQuery, Drupal, BUE);
