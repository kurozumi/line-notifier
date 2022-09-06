Line Notifier
================

Provides [LINE Notify](https://notify-bot.line.me/ja/) integration for Symfony Notifier.

DSN example
-----------

```
LINE_DSN=line://TOKEN@default
```

Install
-----------

```
composer req kurozumi/line-notifier
```

Setting
-----------

```yaml
# notifier.yaml
framework:
    notifier:
        chatter_transports: 
           line: '%env(LINE_DSN)%'
```

```
# .env
LINE_DSN=line://TOKEN@default
```

```yaml
# services.yaml
services:
  Kurozumi\Notifier\Bridge\Line\LineTransportFactory:
    parent: notifier.transport_factory.abstract
    tags: ['chatter.transport_factory']
```