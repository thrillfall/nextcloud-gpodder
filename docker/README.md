# Nextcloud Development Environment

## Installation / Running

```bash
docker-compose up
```

Afterwards you should be able to open <http://localhost:8081/index.php/settings/user/gpoddersync> (admin/admin) to
log in to your Nextcloud instance.

## Check nextcloud.log

For debugging, you can show the `nextcloud.log`:

```bash
make show-log
```

There also is a [logging web interface](http://localhost:8081/index.php/settings/admin/logging).
