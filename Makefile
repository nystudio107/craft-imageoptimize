TAG?=12-alpine
CONTAINER?=imageoptimize-buildchain
DEST?=../../sites/nystudio107/web/docs/image-optimize

.PHONY: dist docker docs npm

dist: docker docs
	docker container run \
		--name ${CONTAINER} \
		--rm \
		-t \
		-v `pwd`:/app \
		nystudio107/${CONTAINER}:${TAG} \
		run build
docker:
	docker build \
		. \
		-t nystudio107/${CONTAINER}:${TAG} \
		--build-arg TAG=${TAG} \
		--no-cache
docs:
	docker container run \
		--name ${CONTAINER} \
		--rm \
		-t \
		-v `pwd`:/app \
		nystudio107/${CONTAINER}:${TAG} \
		run docs
	rm -rf ${DEST}
	mv ./docs/docs/.vuepress/dist ${DEST}
npm:
	docker container run \
		--name ${CONTAINER} \
		--network plugindev_default \
		--rm \
		-t \
		-p 8080:8080 \
		-v `pwd`:/app \
		nystudio107/${CONTAINER}:${TAG} \
		$(filter-out $@,$(MAKECMDGOALS))
%:
	@:
# ref: https://stackoverflow.com/questions/6273608/how-to-pass-argument-to-makefile-from-command-line
