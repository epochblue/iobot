<?php
/**
 * The officially-unofficial iostudio IRC bot.
 *
 * @author Bill Israel <bill.israel@gmail.com>
 */
require __DIR__ . '/vendor/autoload.php';

use Philip\Philip;
use Philip\IRC\Response;
use Philip\IRC\Event;

$config = require __DIR__ . '/config/config.php';

// Create a generic context to be used when requesting from 3rd-party APIs.
$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'timeout' => 5 // timeout after 5 seconds
    )
));

// Create the bot, passing in configuration options
$bot = new Philip($config);

// Load my plugins
$bot->loadPlugins(array(
    new \Philip\Plugin\AdminPlugin($bot),
    new \Philip\Plugin\AnsweringMachinePlugin($bot),
    new \Philip\Plugin\SwearJarPlugin($bot),
    new \Philip\Plugin\ImageMePlugin($bot),
    new \Philip\Plugin\CannedResponsePlugin($bot),
    new \Philip\Plugin\DarkSkyPlugin($bot, $config['DarkSkyPlugin']),
));


// Say hi back to the nice people
$hi_re = "/^(hi|hello|hey|yo|was+up|waz+up|werd|hai|lo) {$config['nick']}$/";
$bot->onChannel($hi_re, function(Event $event) {
    $request = $event->getRequest();
    $event->addResponse(
        Response::msg($request->getSource(), 'Hello, ' . $request->getSendingUser() . '!')
    );
});


// Spit out a random meme (thanks @inky!!)
$bot->onChannel("/^!meme$/", function(Event $event) use ($context) {
    $meme = file_get_contents('http://api.automeme.net/text?lines=1', false, $context);
        $event->addResponse(
            Response::msg($event->getRequest()->getSource(), trim($meme)
        )
    );
});


// Gives high-fives
$bot->onChannel("/^!hf (\w+)$/", function(Event $event) use ($config) {
    $matches = $event->getMatches();
    $who = $matches[0];

    // Better way of having the bot high-five itself.
    if ($who === $config['nick']) {
        $who = 'itself';
    }

    $event->addResponse(
        Response::action($event->getRequest()->getSource(), "gives $who a high-five!")
    );
});


// You can't have a bot without the ability to fire people...
$fired = array();
$bot->onChannel("/^!fire([\s\w]+)?$/", function(Event $event) use (&$fired, $config) {
    $matches = $event->getMatches();
    $request = $event->getRequest();
    $who = empty($matches) ? 'The employee formerly known as Jarvis' : trim($matches[0]);
    $normal = strtolower($who);

    // The bot shouldn't fire itself, that's just silly
    if ($who === $config['nick']) {
        $event->addResponse(
            Response::msg(
                $request->getSource(),
                "I'm sorry {$request->getSendingUser()}, I can't let you do that."
            )
        );
    }

    if (!in_array($normal, array_keys($fired))) {
        $fired[$normal] = 0;
    }

    $count = ++$fired[$normal];
    $times = ($count === 1) ? 'time' : 'times';
    $event->addResponse(
        Response::msg($request->getSource(), "$who, you're fired! (that's $count $times so far)")
    );
});


// Look for URLs, shame people who repost them.
$urls = array();
$url_re = '/((http|https):?\/\/(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})?=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?)/';
$bot->onChannel($url_re, function(Event $event) use (&$urls) {
    $matches = $event->getMatches();
    $request = $event->getRequest();

    $url = $matches[0];
    $normal = rtrim(preg_replace('/(:?https?:\/\/)?(:?www\.)?/', '', $url), '/');
    $source = $request->getSource();

    if (isset($urls[$source]) && in_array($normal, array_keys($urls[$source]))) {
        $who = $urls[$source][$normal];
        $event->addResponse(
            Response::msg($source, "REPOST!! ($who already posted that)")
        );
    } else {
        $urls[$source][$normal] = $request->getSendingUser();
    }
});


// Stock prices
$bot->onChannel('/\$(\w+(\.\w+)?)/', function (Event $event) use ($context) {
    $matches = $event->getMatches();
    $stock = strtoupper(str_replace('.', '-', $matches[0]));
    $price = file_get_contents(
        "http://download.finance.yahoo.com/d/quotes.csv?s=${stock}&f=b2",
        false,
        $context
    );
    $price = trim($price);
    $event->addResponse(
        Response::msg(
            $event->getRequest()->getSource(),
            "Current $stock price: $price -- http://google.com/finance?q=$stock"
        )
    );
});

// Bitcoin info
$bot->onChannel('/^!btc/', function (Event $event) {
    $price = json_decode(
        file_get_contents("http://data.mtgox.com/api/1/BTCUSD/ticker"),
        true
        );

    $event->addResponse(
        Response::msg(
            $event->getRequest()->getSource(),
            "MtGox Buy: {$price['return']['buy']['display_short']}; Sell: {$price['return']['sell']['display_short']}; High: {$price['return']['high']['display_short']}; Low: {$price['return']['low']['display_short']}"
            )
        );
});


// Ready, set, go.
$bot->run();

