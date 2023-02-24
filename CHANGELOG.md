# Changelog

## 3.7.3 - 2023-02-24
### Fixed
- If EpisodeAction is updated with new episode url and there is a conflicting EpisodeAction with that same episode url the later will be deleted 


## 3.7.2 - 2023-02-24
### Fixed
- EpisodeActions are explicitly searched in database by guid. Episode url is used as fallback. Combined search produces multiple results thus broke synchronization 

## 3.7.1 - 2022-11-11
### Fixed
- Fix error where app couldn't be installed with some databases

## 3.7.0 - 2022-11-10
### Fixed
- Podcast overview page no longer produces errors when using php8.X
### Changed
- Allow longer feed URLs with up to 1000 characters

## 3.6.0 - 2022-10-28
### Added
- New overview page of synchronized data in personal settings (thanks @jilleJr)

## 3.5.0 - 2022-10-18
### Changed
- Add support for Nextcloud 25

## 3.4.0 - 2022-05-26
### Fixed
- Don't crash if no sync timestamp is passed
### Changed
- Return all entries on list actions if no timestamp is passed

## 3.3.0 - 2022-05-03
### Fixed
- Don't crash on unauthenticated api call
### Changed
- Add support for Nextcloud 24

## 3.2.0 - 2021-12-09
### Changed
- Ignore subscriptions that have no url
- Add support for Nextcloud 23

## 3.1.0 - 2021-10-18
### Changed
- Add timestamp to subscription change response @JonOfUs

## 3.0.1 - 2021-10-13
### Fixed
- Timestamp migration writes correct values to database @JonOfUs

## 3.0.0 - 2021-10-06
### Changed
- EpisodeAction upload now expects JSON similar to gpodder.net (see README)
- expanded minimal API documentation (see README)
- query episodes by UNIX time instead of DateTime

## 2.0.0 - 2021-08-29
### Changed
- add field "guid" to episode_action
- identify episode actions by guid. episode_action.episode (episode url) serves as fallback if no guid is provided.

## 1.0.14 - 2021-08-24
### Fixed
- enable processing of multiple EpisodeActions (thanks https://github.com/JonOfUs)

## 1.0.13 - 2021-08-22
### Fixed
- increase table column episode_action.action length limit to fit episode actions like DOWNLOAD
### Changed
- narrow catch to nextcloud dbal exceptions

## 1.0.12 - 2021-08-21
### Fixed
-  handle UniqueConstraintViolationException thrown by nc < v22.0


## 1.0.11 - 2021-08-16
### Fixed
-  stop creating unnecessary log file in nextcloud root folder

## 1.0.10 - 2021-08-14
### Fixed
- return all subscriptions and episode changes for parameter since=0


## 1.0.9 - 2021-07-27
### Changed
- save episode action timestamps as UTC (thanks @JohnOfUs)

## 1.0.8 - 2021-07-22
### Fixed
- convert timestamp from episode action request to format also mysql can process (#13)


## 1.0.7 – 2021-07-13
### Changed
- accept only arrays on subscription change endpoint (thanks https://github.com/mattsches)

