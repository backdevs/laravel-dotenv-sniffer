FROM php:8.2.9-cli-alpine

COPY docker-entrypoint.sh /

COPY dist/desniff.phar /usr/bin/desniff

RUN apk add --no-cache --virtual .runtime-deps \
      git \
      tini \
    && chmod +x /usr/bin/desniff

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["desniff"]
