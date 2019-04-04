# Hiboutik App example

This repository contains an app that changes the prices of line items on a sale.


## Install a local environment

Installing any web-application locally requires that you first install the adequate environment, namely the Apache web server, the PHP language interpreter, the MySQL database server (used to store tokens).


## Create a database


```
CREATE DATABASE `hibou_apptest` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `hibou_apptest`;

CREATE TABLE `oauth_tokens` (
  `account` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `expires_in` int(11) NOT NULL DEFAULT '600',
  `token_type` varchar(255) NOT NULL DEFAULT 'Bearer',
  `scope` text NOT NULL,
  `refresh_token` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `oauth_tokens`
  ADD PRIMARY KEY (`access_token`);
```


## Edit lib/config.php



## Install you app


