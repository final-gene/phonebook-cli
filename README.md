# PhoneBook CLI project

[![Build Status](...)](...)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/df076931cab0487e9598d1c0ee85d5cc)](https://www.codacy.com/app/final-gene/phonebook-cli?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=final-gene/phonebook-cli&amp;utm_campaign=Badge_Grade)

## Set Up Host

1. This project uses Docker
    * macOS: [Download Docker for Mac](https://www.docker.com/docker-mac)
    * Windows: [Download Docker for Windows](https://www.docker.com/docker-windows)
2. Install Docker and [Docker Toolbox](https://www.docker.com/toolbox)
3. Install [Composer](https://getcomposer.org)

## Set Up Working Copy

### Clone this repository

```bash
git clone git@github.com:final-gene/php-phonebook-cli.git
```

Or via HTTPS.

```bash
git clone https://github.com/final-gene/php-phonebook-cli.git
```

### Install dependencies

```bash
composer install && composer run-script install-tools
```

#### XSD converter

This project supports XSD files which are used to generate PHP classes and JMS serializer definition.

```bash
composer run-script convert-xsd
```

## Behaviors

The read commands will create [VCards in version 4.0](https://tools.ietf.org/html/rfc6350).
