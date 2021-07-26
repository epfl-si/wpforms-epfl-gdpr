SHELL := /bin/bash

# Define some useful variables
PROJECT_NAME := $(shell basename $(CURDIR))
VERSION := $(shell cat wpforms-epfl-gdpr.php | grep '* Version:' | awk '{print $$3}')
REPO_OWNER_NAME := $(shell git config --get user.name)
REPO_OWNER_EMAIL := $(shell git config --get user.email)


.PHONY: help
## Print this help (see <https://gist.github.com/klmr/575726c7e05d8780505a> for explanation).
help:
	@echo "$$(tput bold)Available rules (alphabetical order):$$(tput sgr0)";sed -ne"/^## /{h;s/.*//;:d" -e"H;n;s/^## //;td" -e"s/:.*//;G;s/\\n## /---/;s/\\n/ /g;p;}" ${MAKEFILE_LIST}|LC_ALL='C' sort -f |awk -F --- -v n=$$(tput cols) -v i=20 -v a="$$(tput setaf 6)" -v z="$$(tput sgr0)" '{printf"%s%*s%s ",a,-i,$$1,z;m=split($$2,w," ");l=n-i;for(j=1;j<=m;j++){l-=length(w[j])+1;if(l<= 0){l=n-i-length(w[j])-1;printf"\n%*s ",-i," ";}printf"%s ",w[j];}printf"\n";}'

# This create the whole jam for publishing a new release on github, including 
# a new version number, updated translation, a "Bounce version commit", a new
# tag and a new release including the wpforms-epfl-gdpr.zip as asset.
.PHONY: release
## Run all the target needed to publishing a new release.
release: check
	$(MAKE) version
	$(MAKE) pot zip commit tag gh-release

.PHONY: check
## Run mandatory software checks (jq, wp, zip, curl, git, gettext).
check: check-wp check-zip check-git check-jq check-curl

check-jq:
	@type jq > /dev/null 2>&1 || { echo >&2 "Please install jq. Aborting."; exit 1; }

check-wp:
	@type wp > /dev/null 2>&1 || { echo >&2 "Please install wp-cli (https://wp-cli.org/#installing). Aborting."; exit 1; }

check-zip:
	@type zip > /dev/null 2>&1 || { echo >&2 "Please install zip. Aborting."; exit 1; }

check-curl:
	@type curl > /dev/null 2>&1 || { echo >&2 "Please install curl. Aborting."; exit 1; }

check-git:
	@type git > /dev/null 2>&1 || { echo >&2 "Please install git. Aborting."; exit 1; }

check-gettext:
	@type gettext > /dev/null 2>&1 || { echo >&2 "Please install gettext. Aborting."; exit 1; }

define JSON_HEADERS
{"Project-Id-Version": "WPForms EPFL GDPR $(VERSION)",\
"Last-Translator": "$(REPO_OWNER_NAME) <$(REPO_OWNER_EMAIL)>",\
"Language-Team": "EPFL IDEV-FSD <https://github.com/epfl-si/$(PROJECT_NAME)>",\
"Report-Msgid-Bugs-To":"https://github.com/wp-cli/i18n-command/issues",\
"X-Domain": "$(PROJECT_NAME)"}
endef

# By default, bounce patch version
# .PHONY: version
# version: bump-version.sh
# 	$(MAKE) version-patch
# 
# .PHONY: version-patch
# version-patch: bump-version.sh
# 	./bump-version.sh -p
# 
# .PHONY: version-minor
# version-minor: bump-version.sh
# 	./bump-version.sh -m
# 
# .PHONY: version-major
# version-major: bump-version.sh
# 	./bump-version.sh -M

.PHONY: pot
## Generate the translations files.
pot: check-wp check-gettext languages/$(PROJECT_NAME).pot
	@wp i18n make-pot . languages/$(PROJECT_NAME).pot --headers='$(JSON_HEADERS)'
	if [ -f languages/$(PROJECT_NAME)-fr_FR.po ] ; then \
		sed -i.bak '/Project-Id-Version:/c "Project-Id-Version: WPForms EPFL GDPR $(VERSION)\\n"' languages/$(PROJECT_NAME)-fr_FR.po; \
		msgmerge --update languages/$(PROJECT_NAME)-fr_FR.po languages/$(PROJECT_NAME).pot; \
	else \
		msginit --input=languages/$(PROJECT_NAME).pot --locale=fr --output=languages/$(PROJECT_NAME)-fr_FR.po; \
	fi
	msgfmt --output-file=languages/$(PROJECT_NAME)-fr_FR.mo languages/$(PROJECT_NAME)-fr_FR.po

.PHONY: zip
## Build the zip to be released.
zip: check-zip
	@mkdir builds || true
	cd ..; zip -r -FS $(PROJECT_NAME)/builds/$(PROJECT_NAME)-$(VERSION).zip $(PROJECT_NAME) \
		--exclude *.git* \
		--exclude *.zip \
		--exclude *.po~ \
		--exclude *.php.bak \
		--exclude *.po.bak \
		--exclude \*builds\* \
		--exclude \*doc\* \
		--exclude Makefile \
		--exclude create-gh-release.sh \
		--exclude bump-version.sh; cd $(PROJECT_NAME)
	@if [ -L ./builds/$(PROJECT_NAME).zip ] ; then \
		cd ./builds; \
		ln -sfn $(PROJECT_NAME)-$(VERSION).zip ./$(PROJECT_NAME).zip; \
		ln -sfn $(PROJECT_NAME)-$(VERSION).zip ./latest.zip; \
	else \
		cd ./builds; \
		ln -s $(PROJECT_NAME)-$(VERSION).zip ./$(PROJECT_NAME).zip; \
		ln -s $(PROJECT_NAME)-$(VERSION).zip ./latest.zip; \
	fi
	@echo "Zip for version $(VERSION) is now available in ./builds/$(PROJECT_NAME).zip"

.PHONY: commit
## Automated commit. 
commit:
	@if [[ -z $$(git commit --dry-run --short | grep CHANGELOG.md) ]]; then \
		read -p "Did you forget to modify the CHANGELOG? Want to abort? [Yy]: " -n 1 -r; \
		if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
			echo -e "\nAborting....\n"; \
			exit 1; \
		else \
			echo -e "\nContinuing....\n"; \
		fi \
	fi
	@-git add languages/*
	@-git commit -o languages -m "[T9N] Translations updated"
	@-git add wpforms-epfl-gdpr.php
	@-git commit -o wpforms-epfl-gdpr.php -m "[VER] Bump to v$(VERSION)"
	read -p "Would you like to git add and commit all? [Yy]: " -n 1 -r; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		git commit -am "[ARE] Automated releasing change" ; \
	fi
	@-git push
	@-git status

.PHONY: tag
## Create a Git tag with latest version.
tag:
	@-git tag -a v$(VERSION) -m "Version $(VERSION)"
	@-git push origin --tags

# .PHONY: gh-release
# ## Create a GitHub release
# gh-release: create-gh-release.sh
# 	./create-gh-release.sh


#
# Please follow the WordPress Coding Standards
#   (https://github.com/WordPress/WordPress-Coding-Standards)
# Check `make lint-install` to install PHP_CodeSniffer and WPCS
#
.PHONY: lint
lint: lint-check lint-fix

.PHONY: lint-install
lint-install:
	composer global require "squizlabs/php_codesniffer=*"
	composer require dealerdirect/phpcodesniffer-composer-installer --update-no-dev
	composer require wp-coding-standards/wpcs --update-no-dev

.PHONY: lint-check
lint-check:
	vendor/bin/phpcs --ignore='wpcs,vendor' --standard=WordPress -p .

.PHONY: lint-check-error
lint-check-error:
	vendor/bin/phpcs -n --ignore='wpcs,vendor' --standard=WordPress -p .
#	vendor/bin/phpcs -n --ignore='wpcs,vendor' -p class-wpforms-epfl-gdpr.php --standard=WordPress

.PHONY: lint-fix
lint-fix:
	vendor/bin/phpcbf --standard=WordPress class-epfl-gdpr.php --suffix=.fixed
	vendor/bin/phpcbf --standard=WordPress class-wpforms-epfl-gdpr.php --suffix=.fixed
