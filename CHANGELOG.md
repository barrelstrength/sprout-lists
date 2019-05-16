# Changelog

## 2.0.0-rc.4 - 2019-05-16

### Updated
- Updated barrelstrength/sprout-base requirement to v5.0.0

## 2.0.0-rc.3 - 2019-04-30

### Changed
- Updated icon

## 2.0.0-rc.2 - 2019-04-09

### Fixed
- Required Sprout Lists to be installed to use User Sync 
- Improved Postgres support

## 2.0.0-rc.1 - 2019-04-09

> {warning} This is a Major release and includes breaking changes. Please see the [Sprout Lists upgrade documentation](https://sprout.barrelstrengthdesign.com/docs/lists/installing-and-updating-craft-3.html#upgrading-to-v2-x) for more details on template tags and controller actions that have changed.

### Added
- Added craft.sproutLists.lists variable
- Added craft.sproutLists.subscribers variable

### Changed
- Updated Lists to require Element ID + Handle to be unique
- Updated controller action `sprout-lists/lists/subscribe` => `sprout-base-lists/lists/add`
- Updated controller action `sprout-lists/lists/unsubscribe` => `sprout-base-lists/lists/remove`
- Updated controller action `sproutlists_subscriptions.subscriberId` => `sproutlists_subscriptions.itemId`
- Renamed `SubscriberListType` => `MailingList`
- Improved support for Edit List and Edit Subscriber permissions
- Moved core logic to barrelstrength/sprout-base-lists
- Updated barrelstrength/sprout-base requirement to v4.0.8
- Added barrelstrength/sprout-base-lists requirement v1.0.2
 
### Fixed
- Fixed notification error when deleting Subscriber
- Fixed delete subscriber path

## 1.0.0-beta.16 - 2019-02-13

### Changed
- Updated settings to implement SproutSettingsInterface
- Improved translation support
- Updated barrelstrength/sprout-base requirement to v4.0.6
- Added barrelstrength/sprout-base-lists requirement v1.0.0
	
## 1.0.0-beta.15 - 2019-01-31

### Added
- Added support for capturing First Name and Last Name values with subscriptions

## 1.0.0-beta.14 - 2019-01-25

### Added
- Added initial support for Craft 3.1

### Changed
- Updated Craft CMS requirement to v3.1.0
- Updated Sprout Base requirement to v4.0.5

## 1.0.0-beta.13 - 2019-01-23

### Changed
- Updated Sprout Lists naming convention: Subscribers => Subscriber
- Updated Sprout Lists naming convention: Lists => SubscriberList

### Fixed
- Updated subscribers to allow subscriptions to multiple lists via front-end ([#23])
- Fixed bug where Lists query did not recognize table prefix
- Fixed bug where Subscriptions query did not recognize table prefix
- Fixed bug where new lists would not get assigned a List ID [#26]
- Fixed subquery for sources on Subscriber Element Index page

[#23]: https://github.com/barrelstrength/craft-sprout-lists/issues/23
[#26]: https://github.com/barrelstrength/craft-sprout-lists/issues/26

## 1.0.0-beta.12 - 2018-10-29

### Changed
- Updated Sprout Base requirement to v4.0.0

## 1.0.0-beta.11 - 2018-10-27

### Changed
- Updated Sprout Base requirement to v3.0.10

## 1.0.0-beta.10 - 2018-07-26

## Changed
- Updated Sprout Base requirement to v3.0.0

## 1.0.0-beta.9 - 2018-07-26

## Added
- Added support for creating lists dynamically
- Added support to filter by elementId on subscribe, unsubscribe and isSubscribed methods

## Changed
- Updated Sprout Base requirement to v2.0.10

## Fixed
- Added elementId on updating and adding List record elementId
- Fixed behavior for User Sync Events
- Fixed missing translation category
- Updated to use registerTwigExtension()

## 1.0.0-beta.8 - 2018-05-17

### Fixed
- Fixes release notes warning syntax

## 1.0.0-beta.7 - 2018-05-15

> {warning} If you have more than one Sprout Plugin installed, to avoid errors use the 'Update All' option.

### Added
- Added Sprout Lists v0.7.1 as minVersionRequired

### Changed
- Renamed BaseListType => ListType
- Updated application folder structure
- Moved all templates to Sprout Base
- Moved schema and component definitions to Plugin class
- Updated Sprout Base requirement to v2.0.0 
- Updated Craft requirement to v3.0.0

## 1.0.0-beta.4 - 2018-04-05

### Fixed
- Fixed icon mask display issue

## 1.0.0-beta.3 - 2018-04-03

## Changed
- Fixed potential conflicts with svg icon styles

## 1.0.0-beta.2 - 2018-03-05

## Changed
- Updated Events to use more specific User::class

## 1.0.0-beta.1 - 2018-03-03

### Added
- Initial Craft 3 release

## 0.7.1 - 2017-09-13

### Changed
- Updated access to `actionSubscribe` and `actionUnsubscribe` controller methods to allowAnonymous
- Updated SproutLists_SubscriptionModel email attribute to use `AttributeType::Email`
- Updated getListCount and getSubscriberCount on variable class to better support third party integrations
- Removed MailChimp integration (Moved to Sprout Mailchimp plugin)
- Removed unused getListTypes methods
- Removed SproutListsBaseListType::getSettings method

### Fixed
- Fixed bug where listHandle was required for third-party integrations
- Fixed bug when retrieving all lists with SproutLists_SubscriberListType::getLists

## 0.7.0 - 2017-05-17

### Added
- Public Beta

## 0.6.1 - 2016-01-17

### Added
- Private Beta
