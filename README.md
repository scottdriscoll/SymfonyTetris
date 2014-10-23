Symfony Multiplayer Tetris
==========================

Tetris in a Symfony Console Application

![Tetris Image](resources/tetris.png)

### Installation

Clone project, run `composer install`.

Launch application with `app/console tetris:launch --no-debug`.

Select the default `parameters.yml` options during composer install.

### Gameplay

Single or Multiplayer!

Clear multiple lines to add lines to your opponents board. For every line (over one) that you clear, you will add that many random lines to the bottom of your opponents board.

### Optional Leaderboard

If you would like to store your scores to a local sqlite database, complete the following steps:

* Make sure `php5_sqlite` is installed
* Create directory `app/data` and make sure it is writable
* run: `app/console doctrine:database:create`
* run: `app/console doctrine:migrations:migrate`
