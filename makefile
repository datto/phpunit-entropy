# MAKEFILE
#
# @author      Christopher Hoult <choult@datto.com>
# @link        https://github.com/datto/phpunit-entropy
# ------------------------------------------------------------------------------

# List special make targets that are not associated with files

.PHONY: help all test unit lint mess

# Current directory
CURRENTDIR=`pwd`

# --- MAKE TARGETS ---

# Display general help about this command
help:
	@echo ""
	@echo "Welcome to Entropy make."
	@echo "The following commands are available:"
	@echo ""
	@echo "    make test        : Run all QA tasks"
	@echo "    make unit     : Run the unit tests"
	@echo "    make lint        : Run style tests"
	@echo "    make mess        : Run mess detection"
	@echo ""


# alias for help target
all: help

# Quality tests

test: unit lint mess

unit:
	@./vendor/bin/phpunit

lint:
	@./vendor/bin/phpcs --standard=PSR2 src
	@./vendor/bin/phpcs --standard=PSR2 tests

mess:
	@./vendor/bin/phpmd src text ./phpmd.xml,unusedcode,design --exclude "vendor"
