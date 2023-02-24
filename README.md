# nextcloud-gpodder
Nextcloud app that replicates basic gpodder.net api to sync podcast consumer apps (podcatchers) like AntennaPod.

### Clients supporting sync
| client | support status |
| :- | :- |
| [AntennaPod](https://antennapod.org) | Initial purpose for this project, as a synchronization endpoint for this client.<br> Support is available [as of version 2.5.1](https://github.com/AntennaPod/AntennaPod/pull/5243/). |
| [KDE Kasts](https://apps.kde.org/de/kasts/) | Supported since version 21.12 |
| [Garmin Podcasts](https://lucasasselli.github.io/garmin-podcasts/) | Only for [compatible Garmin watches](https://apps.garmin.com/en-US/apps/b5b85600-0625-43b6-89e9-1245bd44532c), supported since version 3.3.4 |

### Installation
Either from the official Nextcloud app store ([link to app page](https://apps.nextcloud.com/apps/gpoddersync)) or by downloading the [latest release](https://github.com/thrillfall/nextcloud-gpodder/releases/latest) and extracting it into your Nextcloud apps/ directory.

## API
### subscription
* **get subscription changes**: `GET /index.php/apps/gpoddersync/subscriptions`
	* *(optional)* GET parameter `since` (UNIX time)
* **upload subscription changes** : `POST /index.php/apps/gpoddersync/subscription_change/create`
  * returns JSON with current timestamp

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/subscriptions.html

#### Example requests:
```json
GET /index.php/apps/gpoddersync/subscriptions?since=1633240761

{
  "add": [
    "https://example.com/feed.xml",
    "https://example.org/feed/"
  ],
  "remove": [
    "https://example.net/feed.rss"
  ],
  "timestamp": 1663540502
}
```
```json
POST /index.php/apps/gpoddersync/subscription_change/create

{
  "add": [
    "https://example.com/feed.xml",
    "https://example.org/feed/"
  ],
  "remove": [
    "https://example.net/feed.rss"
  ]
}
```

### episode action
* **get episode actions**: `GET /index.php/apps/gpoddersync/episode_action`
	* *(optional)* GET parameter `since` (UNIX time)
	* fields: *podcast*, *episode*, *guid*, *action*, *timestamp*, *position*, *started*, *total*
* **create episode actions**: `POST /index.php/apps/gpoddersync/episode_action/create`
  * fields: *podcast*, *episode*, *guid*, *action*, *timestamp*, *position*, *started*, *total*
  * *position*, *started* and *total* are optional, default value is -1
  * *guid* is also optional, but should be sent if available
  * identification is done by *guid*, or *episode* if *guid* is missing
  * returns JSON with current timestamp

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/events.html  

#### Example requests:
```json
GET /index.php/apps/gpoddersync/episode_action?since=1633240761

{
    "actions": [
      {
       "podcast": "http://example.com/feed.rss",
       "episode": "http://example.com/files/s01e20.mp3",
       "guid": "s01e20-example-org",
       "action": "PLAY",
       "timestamp": "2009-12-12T09:00:00",
       "started": 15,
       "position": 120,
       "total":  500
      },
      {
       "podcast": "http://example.com/feed.rss",
       "episode": "http://example.com/files/s01e20.mp3",
       "guid": "s01e20-example-org",
       "action": "DOWNLOAD",
       "timestamp": "2009-12-12T09:00:00",
       "started": -1,
       "position": -1,
       "total":  -1
      },
    ],
    "timestamp": 12345
}
```
```json
POST /index.php/apps/gpoddersync/episode_action/create

[
  {
   "podcast": "http://example.com/feed.rss",
   "episode": "http://example.com/files/s01e20.mp3",
   "guid": "s01e20-example-org",
   "action": "play",
   "timestamp": "2009-12-12T09:00:00",
   "started": 15,
   "position": 120,
   "total":  500
  },
  {
   "podcast": "http://example.org/podcast.php",
   "episode": "http://ftp.example.org/foo.ogg",
   "guid": "foo-bar-123",
   "action": "DOWNLOAD",
   "timestamp": "2009-12-12T09:05:21",
  }
]
```

## Development

### Testing
- mount project into apps-extra of nextcloud environment (https://github.com/juliushaertl/nextcloud-docker-dev) 
- `docker-compose exec nextcloud occ app:enable gpoddersync` enable app so we have database tables
- `docker-compose exec nextcloud phpunit9 -c apps-extra/nextcloud-gpodder/tests/phpunit.xml`
