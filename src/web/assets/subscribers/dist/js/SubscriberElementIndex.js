if (typeof Craft.SproutLists === typeof undefined) {
  Craft.SproutLists = {};
}
Craft.SproutLists.SubscriberIndex = Craft.BaseElementIndex.extend(
  {
    getDefaultSourceKey: function() {
      // Did they request a specific section in the URL?
      if (this.settings.context == 'index' && typeof listHandle != typeof undefined) {
        for (var i = 0; i < this.$sources.length; i++) {
          var $source = $(this.$sources[i]);

          if ($source.data('handle') == listHandle) {
            return $source.data('key');
          }
        }
      }

      return this.base();
    }
  });

// Register it!
Craft.registerElementIndexClass('barrelstrength\\sproutlists\\elements\\Subscriber', Craft.SproutLists.SubscriberIndex);
