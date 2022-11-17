TAG?=16-alpine
CONTAINER?=$(shell basename $(dir $(CURDIR)))-buildchain
DOCKERRUN=docker container run \
	--name ${CONTAINER} \
	--rm \
	-t \
	--network plugindev_default \
	-p 3001:3001 \
	-v "${CURDIR}"/../:/app \
	${CONTAINER}:${TAG}

.PHONY: build dev docker install clean npm

# Build the production assets
build: docker install
	${DOCKERRUN} \
		run build
# Start up the dev server
dev: docker install
	${DOCKERRUN} \
		run dev
# Start the Docker container
docker:
	docker build \
		. \
		-t ${CONTAINER}:${TAG} \
		--build-arg TAG=${TAG} \
		--no-cache
# Run an npm install
install: docker
	${DOCKERRUN} \
		install
# Remove node_modules/* & package-lock.json
clean:
	rm -rf node_modules/
	rm -f package-lock.json
# Run the passed in npm command
npm: docker
	${DOCKERRUN} \
		$(filter-out $@,$(MAKECMDGOALS))
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
