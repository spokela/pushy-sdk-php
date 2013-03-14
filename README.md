# Pushy SDK (PHP)

Pushy is a Publish/Subscribe system for realtime web applications. 
This is the SDK to use in your application to trigger events from server-side.

## Composer install


## Basic Usage

```php
var pushy = new Pushy("<pushy-server-url>:<port>", "<auth-handler-uri>");
var channel = pushy.channel("<channel-name>");

channel.bind('subscribe', function() {
    // do something on subscription
});

channel.bind('customEvent', function(data) {
    // this event is trigger server-side by your application
    // and will be propagated to all subscribers
});

channel.subscribe();
```
