FROM php:8.1-cli-alpine

COPY docker-entrypoint.sh /

COPY dist/desniff /usr/bin/desniff

RUN apk add --no-cache --virtual .runtime-deps \
      git \
      tini

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["desniff"]
