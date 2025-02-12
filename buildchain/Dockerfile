ARG TAG=22-alpine
FROM node:$TAG

RUN npm install -g npm@^11.0.0

WORKDIR /app/buildchain/

CMD ["run build"]

ENTRYPOINT ["npm"]
