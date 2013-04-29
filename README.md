iobot
=====

`iobot` is the iostudio IRC bot. He's officially-unofficial.


Commands
--------

* `!fire <person>`   - because sometimes people screw up and need to be fired.
* `!hf <person>`     - because sometimes people do good and need to be congratulated.
* `!meme`            - spits out a random meme via [http://automeme.net/](http://automeme.net/).
* `$<stock ticker>`  - get the current price of a stock.
* `!img <keyword>`   - get a random image from a Google Images search for your keyword
* `!gif <keyword>`   - get a random gif from Google Images
* `!msg <who> <msg>` - leaves a message for someone, delivered when they rejoin the room
* `!btc`             - pulls bitcoin ticker from mtgox api.


Listeners
---------

`iobot` also listens to the conversation and provides these functions:

* `SwearJar`   - keeps track of people's foul mouths and how much money people owe to the swear jar
* `REPOST`     - keeps track of URLs posted in channels and shames those who repost them
* `HelloWorld` - say hi to the bot, and it'll say hi back


License
-------

`iobot` is provided without a license of any kind. Fork it and do whatever you want with it.


Acknowledgements
----------------

`iobot` is based on [Philip](http://github.com/epochblue/Philip), and takes more than a few
command ideas from FatLab's [fatbot](https://github.com/jamiew/fatbot).
