MAJOR_VERSION?=5
PLUGINDEV_PROJECT_DIR?=/Users/andrew/webdev/sites/plugindev/cms_v${MAJOR_VERSION}/
VENDOR?=nystudio107
PROJECT_PATH?=${VENDOR}/$(shell basename $(CURDIR))

.PHONY: dev docs release

# Start up the buildchain dev server
dev:
	${MAKE} -C buildchain/ dev
# Start up the docs dev server
docs:
	${MAKE} -C docs/ dev
# Run code quality tools, tests, and build the buildchain & docs in preparation for a release
release: --code-quality --code-tests --buildchain-clean-build --docs-clean-build
# The internal targets used by the dev & release targets
--buildchain-clean-build:
	${MAKE} -C buildchain/ clean
	${MAKE} -C buildchain/ image-build
	${MAKE} -C buildchain/ build
--code-quality:
	${MAKE} -C ${PLUGINDEV_PROJECT_DIR} -- ecs check vendor/${PROJECT_PATH}/src --fix
	${MAKE} -C ${PLUGINDEV_PROJECT_DIR} -- phpstan analyze -c vendor/${PROJECT_PATH}/phpstan.neon
--code-tests:
--docs-clean-build:
	${MAKE} -C docs/ clean
	${MAKE} -C docs/ image-build
	${MAKE} -C docs/ fix
