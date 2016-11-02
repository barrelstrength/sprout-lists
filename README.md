# Sprout List

## List and Unsubscribe Forms

{% set params = {
	list: 'listHandle',
	userId: currentUser.id,
	elementId: entry.id,
} %}

{# Check if a user is subscribed already #}
{% if craft.sproutLists.isSubscribed(params) %}

  {# Unsubscribe a user from a specific ID #}
  <form method="post" accept-charset="utf-8">
  	<input type="hidden" name="action" value="sproutLists/lists/unsubscribe">
  	<input type="hidden" name="elementId" value="{{ entry.id }}">
  	<input type="hidden" name="userId" value="{{ currentUser.id }}">
  	<input type="hidden" name="list" value="listHandle">
    <input type="submit" value="Remove from My Favorites">
  </form>

{% else %}

  {# Subscribe a user to a specific ID #}
  <form method="post" accept-charset="utf-8">
  	<input type="hidden" name="action" value="sproutLists/lists/subscribe">
  	<input type="hidden" name="elementId" value="{{ entry.id }}">
  	<input type="hidden" name="userId" value="{{ currentUser.id }}">
  	<input type="hidden" name="list" value="listHandle">
    <input type="submit" value="Add to My Favorites">
  </form>

{% endif %}

----

## Displaying Counts

### Subscription Counts

{# Total Subscriptions for a List #}
{{ craft.sproutLists.subscriptionCount({ list: 'listHandle' }) }}

{# Total Subscriptions for a specific User on a List #}
{{ craft.sproutLists.subscriptionCount({
	list: 'listHandle',
	userId: currentUser.id
}) }}

### Subscriber Counts

{# Total Subscribers for a List #}
{{ craft.sproutLists.subscriberCount({ list: 'listHandle' }) }}

{# Total Subscribers for a specific Element on a List #}
{{ craft.sproutLists.subscriberCount({
	list: 'listHandle',
	elementId: entry.id
}) }}

----

## Retrieving IDs of Subscriptions

{# All Subscriptions on a List #}
{% set subscriptions = craft.sproutLists.subscriptions({
	list: 'listHandle'
}) %}

{# All Subscriptions for a specific User on a List #}
{% set subscriptions = craft.sproutLists.subscriptions({
	list: 'listHandle',
	userId: currentUser.id
}) %}

### Looping through the results using the `subscriptionIds` filter

{% for entry in craft.entries
	.section('news')
	.id(subscriptions|subscriptionIds)
	%}
  {{ entry.title }}
{% endfor %}


## Displaying the most popular subscriptions

{% set popularSubscriptions = craft.sproutLists.subscriptions({
	list: 'listHandle',
	userId: currentUser.id,
	order: 'count DESC',
	limit: 10
}) %}

{% for entry in craft.entries
	.section('news')
	.id(popularSubscriptions|subscriptionIds)
	.fixedOrder(true)
	.limit(10)
	%}
  {{ entry.title }} ({{ popularSubscriptions[entry.id].count }} ❤)<br/>
{% endfor %}

## Displaying the date a User subscribed to an Element

{% set subscriptions = craft.sproutLists.subscriptions({
	list: 'listHandle',
	userId: currentUser.id
}) %}

{% for entry in craft.entries
	.section('news')
	.id(subscriptions|subscriptionIds)
	%}
  {{ entry.title }}: Subscribed on: {{ subscriptions[entry.id].dateCreated|date('F d, Y') }}
{% endfor %}

----

## Retrieving IDs of Subscribers

{# All Subscribers on a List #}
{% set subscribers = craft.sproutLists.subscribers({
	list: 'listHandle'
}) %}

{# All Subscribers to a specific Element on a List #}
{% set subscribers = craft.sproutLists.subscribers({
	list: 'listHandle',
  elementId: entry.id
}) %}

### Looping through the results using the `subscriberIds` filter

{% for user in craft.users
	.id(subscribers|subscriberIds)
	.limit(10)
	%}
  {{ user.username }}<br/>
{% endfor %}
