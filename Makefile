.PHONY: test lint lint-fix install clean

install:
	composer install --no-interaction --prefer-dist

test:
	phpunit

lint:
	phpcs

lint-fix:
	phpcbf

clean:
	rm -rf vendor/ .phpunit.result.cache coverage/
