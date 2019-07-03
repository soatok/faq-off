# FAQ Off

[![Support on Patreon](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fshieldsio-patreon.herokuapp.com%2Fsoatok&style=flat)](https://patreon.com/soatok)
[![Linux Build Status](https://travis-ci.org/soatok/faq-off.svg?branch=master)](https://travis-ci.org/soatok/faq-off)
[![Latest Stable Version](https://poser.pugx.org/soatok/faq-off/v/stable)](https://packagist.org/packages/soatok/faq-off)
[![Latest Unstable Version](https://poser.pugx.org/soatok/faq-off/v/unstable)](https://packagist.org/packages/soatok/faq-off)
[![License](https://poser.pugx.org/soatok/faq-off/license)](https://packagist.org/packages/soatok/faq-off)
[![Downloads](https://img.shields.io/packagist/dt/soatok/faq-off.svg)](https://packagist.org/packages/soatok/faq-off)

**FAQ Off** lets you build gamebook-style FAQ websites to counteract
[sealioning](https://en.wikipedia.org/wiki/Sealioning) and mob harassment
on social media.

FAQ Off is a self-hostable microservice based on Slim Framework 3 and 
[AnthroKit](https://github.com/soatok/anthrokit), developed by
[Soatok](https://soatok.com) live on [his Twitch.tv channel](https://twitch.tv/soatok).

> #### Public demo: [https://faq.dhol.es](https://faq.dhol.es)
> Invite links are given to Patreon supporters at the 
> **Brilliant** tier and higher.

## Installation

### Getting the Code and Dependencies

First, clone the git repository.

```
git clone https://github.com/soatok/faq-off target-dir-name
```

Next, run `composer install` inside the destination directory.

### Database Setup

Run `bin/create-config.php` to create a local configuration file.
You should have valid PostgreSQL database credentials onhand at
this stage.

Next, run `bin/install` to finish installing the database tables. 

### Webserver Configuration

TODO

