<?php

namespace App\Core;
use App\Helpers;

/**
 * Class Module
 */
class Module
{

    /**
     * Module constructor.
     * @param array $options
     */
    function __construct(array $options)
    {
        switch (key($options)) {
            case "parser":
                $parser = new Helpers\Parser($this->checkUrl($options['parser']));
                echo $parser->parse();
                break;
            case "report":
                $reporter = new Helpers\Reporter($this->checkUrl($options['report']));
                echo $reporter->report();
                break;

            case "help":
            default:
            var_dump(key($options));
                echo $this->help();
        }

    }

    /**
     * @return string
     */
    protected function help()
    {
        $message = "В командной строке запуск " . PHP_EOL
            . "php parser.php [--parser=example.org] [--report=example.org] [--help]" . PHP_EOL
            . "Опции:" . PHP_EOL
            . "--parser=url" . PHP_EOL
            . "запускает парсер, принимает обязательный параметр url (как с протоколом, так и без)" . PHP_EOL
            . "--report=url" . PHP_EOL
            . "выводит в консоль результаты анализа для домена, принимает обязательный параметр domain (как с протоколом, так и без)" . PHP_EOL
            . "--help " . PHP_EOL
            . "выводит список команд с пояснениями" . PHP_EOL;
        return $message;
    }


    /**
     * @param $url
     * @return string
     */
    private function checkUrl($url)
    {
        return stripos($url, "http") === false ? 'http://' . $url : $url;
    }
}