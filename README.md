# Sprout Lists

Allow users to subscribe to, follow, or like any Element including Entries, Categories, Products, Users, and Lists. Easily display counts and related user info.

Use Sprout Lists with Sprout Email for powerful email list management and dynamic notification emails.

## Usage

Example Subscribe and Unsubscribe for Craft Users with a User ID:

``` twig
{% set params = {
  listHandle: 'listHandle',
  userId: currentUser.id,
  elementId: entry.id,
} %}

{# Check if a user is subscribed already #}
{% if craft.sproutLists.isSubscribed(params) %}

  {# Unsubscribe a user from a specific List #}
  <form method="post" accept-charset="utf-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="sprout-lists/lists/unsubscribe">
    <input type="hidden" name="listHandle" value="listHandle">
    <input type="hidden" name="elementId" value="{{ entry.id }}">
    <input type="hidden" name="userId" value="{{ currentUser.id }}">
    <input type="submit" value="Remove from List">
  </form>

{% else %}

  {# Subscribe a user to a specific List #}
  <form method="post" accept-charset="utf-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="sprout-lists/lists/subscribe">
    <input type="hidden" name="listHandle" value="listHandle">
    <input type="hidden" name="elementId" value="{{ entry.id }}">
    <input type="hidden" name="userId" value="{{ currentUser.id }}">
    <input type="submit" value="Add to List">
  </form>

{% endif %}
```

Example Subscribe and Unsubscribe for Guests using an email address:

``` twig
{# Allow a user to submit their email to unsubscribe from a specific List #}
<form method="post" accept-charset="utf-8">
  {{ csrfInput() }}
  <input type="hidden" name="action" value="sprout-lists/lists/unsubscribe">
  <input type="hidden" name="listHandle" value="listHandle">
  <input type="hidden" name="elementId" value="{{ entry.id }}">
  <input type="email" name="email" value="username@website.com">
  <input type="submit" value="Remove from List">
</form>

{# Allow a user to submit their email to subscribe from a specific List #}
<form method="post" accept-charset="utf-8">
  {{ csrfInput() }}
  <input type="hidden" name="action" value="sprout-lists/lists/subscribe">
  <input type="hidden" name="listHandle" value="listHandle">
  <input type="hidden" name="elementId" value="{{ entry.id }}">
  <input type="email" name="email" value="username@website.com">
  <input type="submit" value="Add to List">
</form>
```

See the documentation for additional template tags and use cases.

## Documentation

See the [Sprout Website](https://sprout.barrelstrengthdesign.com/craft-plugins/lists/docs) for documentation, guides, and additional resources. 

## Support

- [Send a Support Ticket](https://sprout.barrelstrengthdesign.com/craft-plugins/request/support) via the Sprout Website.
- [Create an issue](https://github.com/barrelstrength/craft-sprout-lists/issues) on Github.

<a href="https://sprout.barrelstrengthdesign.com" target="_blank">
  <img src="https://sprout.barrelstrengthdesign.com/content/plugins/sprout-icon.svg" width="72" align="right">
</a>