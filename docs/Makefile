MAJOR_VERSION?=4
TAG?=14-alpine
CONTAINER?=$(shell basename $(dir $(CURDIR)))-v${MAJOR_VERSION}-docs
DOCS_DEV_PORT?=400${MAJOR_VERSION}
DOCS_DEST?=../../../sites/nystudio107/web/docs/image-optimize
IMAGE_INFO=$(shell docker image inspect $(CONTAINER):$(TAG))
IMAGE_NAME=${CONTAINER}:${TAG}
DOCKER_RUN=docker container run --rm -it -v "${CURDIR}":/app

.PHONY: build clean dev fix image-build image-check lint npm ssh

# Perform a dist build via npm run docs:build
build: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} run docs:build
	rm -rf ${DOCS_DEST}
	mv ./docs/.vitepress/dist ${DOCS_DEST}
# Remove node_modules/ & package-lock.json
clean:
	rm -rf node_modules/
	rm -f package-lock.json
# Run the development server via npm run docs:dev
dev: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ -e DOCS_DEV_PORT="${DOCS_DEV_PORT}" -p ${DOCS_DEV_PORT}:${DOCS_DEV_PORT} ${IMAGE_NAME} run docs:dev
# Fix the docs with textlint via npm run docs:fix
fix: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} run docs:fix
# Build the Docker image & run npm install
image-build:
	docker build . -t ${IMAGE_NAME} --build-arg TAG=${TAG} --no-cache
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} install
# Ensure the image has been created
image-check:
ifeq ($(IMAGE_INFO), [])
image-check: image-build
endif
# Lint the docs with textlint via npm run docs:lint
lint: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} run docs:lint
# Run the passed in npm command
npm: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ ${IMAGE_NAME} $(filter-out $@,$(MAKECMDGOALS)) $(MAKEFLAGS)
# Open a shell inside of the container
ssh: image-check
	${DOCKER_RUN} --name ${CONTAINER}-$@ --entrypoint=/bin/sh ${IMAGE_NAME}
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
