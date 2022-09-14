<p align="center"><a href="https://tama.linhntaim.com" target="_blank"><img src="https://raw.githubusercontent.com/linhntaim/tama-art/master/logo.text.color.png" width="400" alt="tama Logo"></a></p>

<p align="center">
Powered by:<br>
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

The project is still in progress.

## Roadmap

1. Launch:
    1. Bot: Exchange/Ticker/Interval => Price => Indication
    2. Broadcast: Indication => Actions
    3. Orchestration: Bot => Indication => Broadcast
    4. Chatbot: Message => Execute
2. Phase 1:
    1. Exchange supported: Binance (Spot market)
    2. Indication supported: RSI divergences
    3. Orchestration supported:
        1. On-demand (use exchange API)
        2. Real-time (use exchange websocket connection)
    4. Action supported:
        1. Report:
            1. Type:
                1. Plaintext
            2. To:
                1. Telegram
    5. Chatbot:
        1. Platform supported:
            1. Telegram via Webhook
        2. Execution supported:
            1. Provide system information
            2. Provide trading information
            3. Manage trading subscriptions
3. Phase 2 _(current)_:
    1. Automated trading strategy: Capital/Risk/Bot => Indication => Buy|Sell => Exchange
        1. Order type support:
            1. Market
        2. Tool for testing trading strategy with historical data
        3. Add a "Trade" action to broadcast
        4. Support to run fake trading strategies to test in the real world
        5. Support chatbot
    2. Better indication from more oscillators/indicators
4. Phase 3:
    1. Run automated trading strategies in the real world
5. Phase 4:
    1. AI integration for better indication & risk management based on testing with historical data
    2. Support more exchanges

## Installation

### Requirements

- [Laravel > Server Requirements](https://laravel.com/docs/9.x/deployment#server-requirements)
- PHP >= 8.1
- [Trader PHP Extension](https://pecl.php.net/package/trader)
- [Redis PHP Extension](https://pecl.php.net/package/redis) _(recommended)_
  or [predis package](https://github.com/predis/predis)
    - See: https://laravel.com/docs/9.x/redis
- MySQL 8.x / MariaDB 10.x
- Redis 7.x

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

`TELEGRAM_BOT_NAME` & `TELEGRAM_BOT_USERNAME` is the name & username of the bot
and `TELEGRAM_BOT_TOKEN` is the authentication token generated after creating the bot.

See: https://core.telegram.org/bots#creating-a-new-bot.

`TELEGRAM_BOT_WEBHOOK_SECRET` is the secret token used to create the webhook.

See: https://core.telegram.org/bots/api#setwebhook.
