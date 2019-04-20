<?php

namespace App\Helpers;

/**
 * Class Reporter
 */
class Reporter
{
    /**
     * @var mixed
     */
    private $domain;
    private $reports;

    /**
     * Reporter constructor.
     * @param $url
     */
    function __construct($url)
    {
        $this->reports = [];
        $files = [];
        if ($handle = opendir('./reports')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $files[] = $file . PHP_EOL;
                }
            }
            closedir($handle);
        }
        if (!empty($files)) {
            foreach ($files as &$file) {
                $exp = explode('---', $file);
                $this->reports[$exp[0]][] = $file;
            }
        }
        $this->domain = parse_url($url, PHP_URL_HOST);
        $this->checkFile();
    }


    public function checkFile()
    {
        $domains = [];
        foreach ($this->reports as $key => $value) {
            $domains[] = $key;
        }
        if (!in_array($this->domain, $domains)) {
            die("Can't find report for domain: ' . $this->domain . '. Please use 'parse' command for make report." . PHP_EOL);
        }
    }

    /**
     * @return string
     */
    public function report()
    {
        foreach ($this->reports[$this->domain] as &$report) {
            $report = trim($report);
            $handle = fopen('reports/' . $report, "r");
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                for ($c = 0; $c < count($data); $c++) {
                    echo $data[$c] . PHP_EOL;
                }
            }
            fclose($handle);
        }
        return 'End of report for domain : ' . $this->domain . PHP_EOL;

    }
}