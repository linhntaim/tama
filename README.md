<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

<p align="center">
<a href="https://github.com/linhntaim/laravel-9-starter" target="_blank">Laravel 9 Starter</a><br>
<a href="https://www.ta-lib.org/" target="_blank">TA-Lib</a>
(<a href="https://www.php.net/manual/en/book.trader.php" target="_blank">Trader PHP Extension</a>)<br>
<a href="https://github.com/binance/binance-connector-php" target="_blank">Binance Connector PHP</a><br>
<a href="https://reactphp.org/" target="_blank">ReactPHP</a><br>
<a href="https://github.com/ratchetphp/Ratchet" target="_blank">Ratchet</a><br>
<a href="https://github.com/laravel-notification-channels/telegram" target="_blank">Telegram Notifications Channel for Laravel</a><br>
</p>

## About "tama"

"tama" is the codename of the project which aims to create a crypto bot that:

- Provide trading information.
- Test/Run automated trading strategies.

## Installation

### Requirements

- [Laravel > Server Requirements](https://laravel.com/docs/9.x/deployment#server-requirements)
- PHP >= 8.1
- [Trader PHP Extension](https://pecl.php.net/package/trader) 
- [Redis PHP Extension](https://pecl.php.net/package/redis) _(recommended)_ or [predis package](https://github.com/predis/predis)
  - See: https://laravel.com/docs/9.x/redis
- MySQL 8.x / MariaDB 10.x
- Redis 7.x
- Telegram > Bots

### Sourcecode

After cloning the sourcecode, run following commands at the root directory:

```shell
# Install packages
composer install

# Setup the .env file if none exists
php artisan setup

# Again, setup the sourcecode (incl. database migration and seeding)
php artisan setup
```

### Others

#### [Telegram Bot](https://core.telegram.org/bots)



[Create a bot](https://core.telegram.org/bots#3-how-do-i-create-a-bot) and fill its information 
in the .env file.

```dotenv
TELEGRAM_BOT_NAME=
TELEGRAM_BOT_USERNAME=
TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_WEBHOOK_SECRET=
```

`TELEGRAM_BOT_NAME` & `TELEGRAM_BOT_USERNAME` is the name & username of the bot and `TELEGRAM_BOT_TOKEN` is the authentication token generated after creating the bot.

See: https://core.telegram.org/bots#creating-a-new-bot.

`TELEGRAM_BOT_WEBHOOK_SECRET` is the secret token used to create the webhook.

See: https://core.telegram.org/bots/api#setwebhook.
