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

docker:
	docker build \
		. \
		-t ${CONTAINER}:${TAG} \
		--build-arg TAG=${TAG} \
		--no-cache
build: docker install
	${DOCKERRUN} \
		run docs:build
	rm -rf ${DOCSDEST}
	mv ./docs/.vitepress/dist ${DOCSDEST}
dev: docker install
	${DOCKERRUN} \
		run docs:dev
fix: docker install
	${DOCKERRUN} \
		run docs:fix
install: docker
	${DOCKERRUN} \
		install
lint: docker install
	${DOCKERRUN} \
		run docs:lint
clean:
	rm -rf node_modules/
	rm -f package-lock.json
npm: docker
	${DOCKERRUN} \
		$(filter-out $@,$(MAKECMDGOALS))
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
