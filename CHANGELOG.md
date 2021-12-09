# Changelog

## upcoming
### Changed
- Ignore subscriptions that have no url

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

