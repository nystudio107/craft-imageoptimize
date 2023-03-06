ARG TAG=18-alpine
FROM node:$TAG

RUN npm install -g npm@^9.3.0

WORKDIR /app/buildchain/

CMD ["run build"]

ENTRYPOINT ["npm"]
