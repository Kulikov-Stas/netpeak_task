#!/usr/bin/php
<?php

require_once( __DIR__ . '/vendor/autoload.php');

$module = new \App\Core\Module(getopt("p:r:h", ["parser:", "report:", "help"]));