TAG?=14-alpine
CONTAINER?=$(shell basename $(dir $(CURDIR)))-docs
DOCKERRUN=docker container run \
	--name ${CONTAINER} \
	--rm \
	-p 3002:3002 \
	-t \
	-v "${CURDIR}":/app \
	${CONTAINER}:${TAG}
DOCSDEST?=../../../sites/nystudio107/web/docs/image-optimize

.PHONY: docker build dev fix install lint clean npm

# Start the Docker container
docker:
	docker build \
		. \
		-t ${CONTAINER}:${TAG} \
		--build-arg TAG=${TAG} \
		--no-cache
# Build the production docs
build: docker install
	${DOCKERRUN} \
		run docs:build
	rm -rf ${DOCSDEST}
	mv ./docs/.vitepress/dist ${DOCSDEST}
# Start up the dev server
dev: docker install
	${DOCKERRUN} \
		run docs:dev
# Fix the docs via textlint
fix: docker install
	${DOCKERRUN} \
		run docs:fix
# Run an npm install
install: docker
	${DOCKERRUN} \
		install
# Lint the docs via textlint
lint: docker install
	${DOCKERRUN} \
		run docs:lint
# Remove node_modules/* & package-lock.json
clean:
	rm -rf node_modules/
	rm -f package-lock.json
# Run the passed in npm command
npm: docker
	${DOCKERRUN} \
		$(filter-out $@,$(MAKECMDGOALS)) $(MAKEFLAGS)
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
