# Changelog

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

