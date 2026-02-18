.PHONY: test lint lint-fix

test:
	phpunit

lint:
	phpcs

lint-fix:
	phpcbf
