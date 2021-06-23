SHELL := /bin/bash

# Define some useful variables
# wpforms-epfl-gdpr
PROJECT_NAME := $(shell basename $(CURDIR))
# 0.0.4
VERSION := $(shell cat wpforms-epfl-gdpr.php | grep '* Version:' | awk '{print $$3}')
# Nicolas Borboën
REPO_OWNER_NAME := $(shell git config --get user.name)
# ponsfrilus@gmail.com
REPO_OWNER_EMAIL := $(shell git config --get user.email)

.PHONY: check
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


.PHONY: pot
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

