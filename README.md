# nextcloud-gpodder
nextcloud app that replicates basic gpodder.net api 

This app serves as synchronization endpoint for AntennaPod: https://github.com/AntennaPod/AntennaPod/pull/5243/

# API
## subscription
* *subscription*: `/index.php/apps/gpoddersync/subscriptions`
* *subscription change* : `/index.php/apps/gpoddersync/subscription_change/create`

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/subscriptions.html

## episode action
* *episode actions*: `/index.php/apps/gpoddersync/episode_action`
* *create episode actions*: `/index.php/apps/gpoddersync/episode_action/create`

The API replicates this: https://gpoddernet.readthedocs.io/en/latest/api/reference/events.html

we also process the property `uuid`.

