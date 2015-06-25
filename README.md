{# Check if a user is subscribed already #}
{% if craft.sproutSubscribe.isSubscribed(currentUser.id, entry.id) %}
  
  {# Unsubscribe a user from a specific ID #}
  <form method="post" accept-charset="utf-8">
  	<input type="hidden" name="action" value="sproutSubscribe/lists/unsubscribe">
  	<input type="hidden" name="elementId" value="{{ entry.id }}">
    <input type="submit" value="Remove from My Favorites">
  </form>

{% else %}
  
  {# Subscribe a user to a specific ID #}
  <form method="post" accept-charset="utf-8">
  	<input type="hidden" name="action" value="sproutSubscribe/lists/subscribe">
  	<input type="text" name="elementId" value="{{ entry.id }}">
    <input type="submit" value="Add to My Favorites">
  </form>

{% endif %}


{# Get all IDs that a user is subscribed to #}
{% set ids = craft.sproutSubscribe.subscriptionIds(currentUser.id) %}

{# Display all entries that match one of the IDs a user is subscribed to #}
{% if ids|length %}
  {% for favoritedEntry in craft.entries({ id : ids }) %}
    {{ favoritedEntry.title }}<br/>
  {% endfor %}
{% endif %}


----

total elements everyone has subscribed to (unique element IDs)
{% total = craft.sproutSubscribe.subscriptionCount($key) %}

total elements a user, or array of users is subscribed to
{% total = craft.sproutSubscribe.subscriptionCount($key, $userIds) %}

ids of the elements that have been subscribed to
{% ids = craft.sproutSubscribe.subscriptionIds($key) %}

ids of the elements a user, or array of users is subscribed to
{% ids = craft.sproutSubscribe.subscriptionIds($key, $userIds) %}

----

total users that have subscribed to everything (unique user IDs)
{% total = craft.sproutSubscribe.subscriberCount($key) %}

total users an element, or array of elements has as subscribers
{% total = craft.sproutSubscribe.subscriberCount($key, $elementIds) %}

ids of the users that have subscribed to an element
{% ids = craft.sproutSubscribe.subscriberIds($key) %}

ids of the users that have subscribed to an element, or array of elements
{% ids = craft.sproutSubscribe.subscriberIds($key, $elementIds) %}