# Changelog

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
