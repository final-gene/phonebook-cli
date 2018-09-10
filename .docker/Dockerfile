FROM php:7.2-cli-alpine3.8

LABEL maintainer="frank.giesecke@final-gene.de"

ARG VERSION

# Install build dependencies
# hadolint ignore=SC2086,DL3018
RUN apk add --no-cache --virtual=.build-deps \
        bash \
        libxml2-dev

SHELL ["/bin/bash", "-o", "pipefail", "-e", "-c"]

# Configure and install PHP extensions
RUN docker-php-ext-install \
        soap

# Install persistent dependencies
# hadolint ignore=SC2046,DL3018
RUN apk add --no-cache --virtual .persistent-deps \
    $( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' \
        | sort -u \
        | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )

# Add binary
RUN curl -LSs \
        -o /usr/local/bin/phonebook \
        "https://github.com/final-gene/phonebook-cli/releases/download/${VERSION}/phonebook.phar" \
    && chmod a+x /usr/local/bin/phonebook

# Remove build dependencies
RUN apk del .build-deps

# Configure working directory
WORKDIR /app

# Configure entrypoint
ENTRYPOINT ["phonebook"]

CMD [""]
