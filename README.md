# nextcloud-gpodder
Nextcloud app that replicates basic gpodder.net api

This app serves as synchronization endpoint for AntennaPod: https://github.com/AntennaPod/AntennaPod/pull/5243/  
This feature will be available in AntennaPod as of version 2.5.0, which will be released towards the end of 2021.

# API
## subscription
* **get subscription changes**: `GET /index.php/apps/gpoddersync/subscriptions`
	* *(optional)* GET parameter `since` (UNIX time)
* **upload subscription changes** : `POST /index.php/apps/gpoddersync/subscription_change/create`
	* returns nothing

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/subscriptions.html

## episode action
* **get episode actions**: `GET /index.php/apps/gpoddersync/episode_action`
	* *(optional)* GET parameter `since` (UNIX time)
	* fields: *podcast*, *episode*, *guid*, *action*, *timestamp*, *position*, *started*, *total*
* **create episode actions**: `POST /index.php/apps/gpoddersync/episode_action/create`
  * fields: *podcast*, *episode*, *guid*, *action*, *timestamp*, *position*, *started*, *total*
  * *position*, *started* and *total* are optional, default value is -1
  * returns JSON with current timestamp

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/events.html

we also process the property `guid`

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
