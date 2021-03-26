.PHONY: ${TARGETS}

# https://www.gnu.org/software/make/manual/html_node/Force-Targets.html
always:

cs-fix: vendor
	vendor/bin/php-cs-fixer fix --diff-format udiff -vvv

cs-diff: vendor
	vendor/bin/php-cs-fixer fix --diff-format udiff --dry-run -vvv

phpstan: vendor
	vendor/bin/phpstan analyze

psalm: vendor
	vendor/bin/psalm.phar

phpunit: vendor
	@vendor/bin/phpunit

.PHONY: baseline
baseline: vendor ## Generate baseline files
	vendor/bin/phpstan analyze --generate-baseline
	vendor/bin/psalm.phar --update-baseline

static: cs-diff phpstan

test: static phpunit

vendor: always
	composer update --no-interaction
	composer bin all install --no-interaction
	vendor/bin/simple-phpunit install
