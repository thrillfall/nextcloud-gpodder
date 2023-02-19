.PHONY: bash check-code sign-app show-log test

ls-db:
	docker-compose run --rm app su -c "ls -hal data/mydb.db*" www-data

fetch-db:
	docker-compose run --rm app su -c "cp data/mydb.db apps/gpoddersync" www-data

push-db:
	docker-compose run --rm app su -c "cp apps/gpoddersync/mydb.db* data" www-data

bash:
	docker-compose run --rm app su -c "bash" www-data

check-code:
	docker-compose run --rm app su -c "./occ app:check-code gpoddersync" www-data

show-log:
	docker-compose run --rm app tail -f /var/www/html/data/nextcloud.log
