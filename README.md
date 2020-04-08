# FAQ Off

[![Support on Patreon](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fshieldsio-patreon.herokuapp.com%2Fsoatok&style=flat)](https://patreon.com/soatok)
[![Linux Build Status](https://travis-ci.org/soatok/faq-off.svg?branch=master)](https://travis-ci.org/soatok/faq-off)
[![Latest Stable Version](https://poser.pugx.org/soatok/faq-off/v/stable)](https://packagist.org/packages/soatok/faq-off)
[![Latest Unstable Version](https://poser.pugx.org/soatok/faq-off/v/unstable)](https://packagist.org/packages/soatok/faq-off)
[![License](https://poser.pugx.org/soatok/faq-off/license)](https://packagist.org/packages/soatok/faq-off)

**FAQ Off** lets you build gamebook-style FAQ websites to counteract
[sealioning](https://en.wikipedia.org/wiki/Sealioning) and mob harassment
on social media.

FAQ Off is a self-hostable microservice based on Slim Framework 3 and 
[AnthroKit](https://github.com/soatok/anthrokit), developed by
[Soatok](https://soatok.com) live on [his Twitch.tv channel](https://twitch.tv/soatok).

> #### Public demo: [https://faq.dhol.es](https://faq.dhol.es)
> Invite links are given to Patreon supporters at the 
> **Brilliant** tier and higher.

To learn more, please see [the Patreon post that introduced FAQ Off](https://www.patreon.com/posts/tell-sea-lions-24475473).

## FAQ Off Features at a Glance

* **Interactive Question and Answer Website**:
  * Guide your readers to the answers to their question.
  * Short-circuit common lines of disruptive discourse.  
    *Write once, answer ad nauseum!*
* **HTML and Markdown Support**
* **Troll and Spam Defense**:
  * Administrators can enable "invite only" mode, which requires an invitation code
    from an existing user to sign up.
  * The invitation tree: Administrators can see who invited who, to identify common
    entry points of misbehaving users to curate their own community.
* **Collaboration**:
  * Users can share an **Author** profile with colleagues and publish as a group.
  * Users can belong to an unlimited number of **Authors**.
  * Authors can share access to an unlimited number of users, or just one.
* **Security**:
  * Entries are written in HTML / Markdown and processed by [HTML Purifier](http://htmlpurifier.org)
    to protect against cross-site scripting attacks.
  * Passwords are [stored securely](https://github.com/soatok/dhole-cryptography#password-storage), or you can use Twitter. 
  * Your username or Twitter handle is only knowable by *administrators*.
    A randomly generated `Public ID` is provided to keep your login handle
    and/or Twitter handle anonymous to everyone else.

## Installation

### Getting the Code and Dependencies

First, clone the git repository.

```
git clone https://github.com/soatok/faq-off target-dir-name
```

Next, run `composer install --no-dev` inside the destination directory.

### Database Setup

Run `bin/create-config.php` to create a local configuration file.
You should have valid PostgreSQL database credentials onhand at
this stage.

Next, run `bin/install` to finish installing the database tables. 

### Webserver Configuration

Make sure you configure your virtual host to use the `public` directory
as its document root.

* BAD: `/var/www/faq-off` 
* Good: `/var/www/faq-off/public`

General rule: If your users can read this `README.md` file, you've configured your
server incorrectly and need to go another layer down. 

It's highly recommended that you use HTTPS (TLSv1.3, TLSv1.2). If you cannot
afford a TLS certificate, [Let's Encrypt](https://letsencrypt.org) offers free
certificates with automatic renewal (via `certbot`).

For example, an nginx configuration might look like this:

```nginx
server {
    listen 443 ssl;
    listen [::]:443 ssl;

    ssl_certificate /etc/letsencrypt/live/faq.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/faq.example.com/privkey.pem;

    include snippets/ssl-params.conf;

    root /var/www/faq-off/public;

    server_name faq.example.com;

    index index.php index.html index.htm;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
    }
    location / {
        # First attempt to serve request as file, then
        # as directory, then fall back to displaying a 404.
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ /.well-known {
        allow all;
    }
}

server {
    listen 80;
    listen [::]:80;

    root /var/www/faq-off/public;
    index index.html index.htm;

    server_name faq.example.com;

    location ~ /.well-known {
        allow all;
    }

    location / {
        # First attempt to serve request as file, then
        # as directory, then fall back to displaying a 404.
        try_files $uri $uri/ =404;
    }

    # Redirect to HTTPS
    return 301 https://faq.example.com$request_uri;
}
```
