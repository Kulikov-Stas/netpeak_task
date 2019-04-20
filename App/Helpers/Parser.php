<?php

namespace App\Helpers;

/**
 * Class Parser
 */
class Parser
{
    private $url;
    private $domain;
    private $protocol;
    private $file;
    public $links;

    /**
     * Parser constructor.
     * @param $url
     */
    function __construct($url)
    {
        $this->url = $url;
        $this->domain = parse_url($url, PHP_URL_HOST);
        $this->protocol = parse_url($url, PHP_URL_SCHEME);
        $this->links[] = $url;
    }

    /**
     * @return string
     */
    public function parse() {
        $result = $this->getPage($this->url);
        if (!empty($result['errmsg'])) {
            $message = 'Unable to parse url ' . $this->url . ': ' . $result['errmsg'];
            return 'Failed to parse url : ' . $this->url . PHP_EOL;
        } else {
            $filename = 'reports/' . $this->domain . '---' . date('Y-m-d-H-i-s') . '.csv';
            $this->file = fopen($filename, "w");
            $this->parseImages($result['content'], $this->domain . parse_url($this->url, PHP_URL_PATH), true);
            $this->parseSubUrls($result['content']);
            fclose($this->file);
            return $filename;
        }
    }

    /**
     * @param $content
     */
    private function parseSubUrls($content)
    {
        try {
            preg_match_all('/<a([^>]*) href=\"[^http|ftp|https|mailto]([^\"]*)\"/', $content, $links, PREG_SET_ORDER);
            if (!empty($links)) {
                foreach ($links as $link) {
                    $subPage = trim($link[2], '"');
                    if (!in_array($subPage, $this->links)) {
                        $this->links[] = $subPage;
                        $subContent = $this->getPage($this->protocol . '://' . $this->domain . $subPage);
                        if (!empty($subContent['errmsg'])) {
                            continue;
                        } else {
                            $this->parseImages($subContent['content'], $this->domain . $subPage, true);
                            $this->parseSubUrls($subContent['content']);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // nothing
        }
    }

    /**
     * @param $content
     * @param $domain
     * @param bool $extended
     */
    private function parseImages($content, $domain, $extended = false)
    {
        preg_match_all('/<img[^>]+>/i', $content, $result);
        $img = $src = $alt = $title = [];
        if ($extended) {
            foreach($result[0] as $img_tag)
            {
                preg_match_all('/(src)=("[^"]*")/i',$img_tag, $src,PREG_SET_ORDER);
                preg_match_all('/(alt)=("[^"]*")/i',$img_tag, $alt, PREG_SET_ORDER);
                preg_match_all('/(title)=("[^"]*")/i',$img_tag, $title, PREG_SET_ORDER);
                $this->saveImageUrl($domain, [$src, $alt, $title], true);
            }
        } else {
            foreach ($result[0] as $img_tag) {
                preg_match_all('/(src)=("[^"]*")/i', $img_tag, $img, PREG_SET_ORDER);                
                $this->saveImageUrl($domain, $img);
            }
        }

    }

    /**
     * @param $domain
     * @param array $images
     * @param bool $extended
     */
    private function saveImageUrl($domain, array $images, $extended = false)
    {
        foreach ($images as &$image) {
            if (!empty($image)) {
                if ($extended) {
                    fputcsv($this->file, [
                        $domain,
                        !isset($images[0][0][2]) ? 'no src' : $images[0][0][2],
                        !isset($images[1][0][2]) ? 'no alt' : $images[1][0][2],
                        !isset($images[2][0][2]) ? 'no title' : $images[2][0][2]
                    ],";");
                } else {
                    fputcsv($this->file, [$domain, $this->checkImageUrl($image[2], $domain)], ";");
                }
            }
        }
    }

    /**
     * @param $url
     * @param $domain
     * @return string
     */
    private function checkImageUrl($url, $domain)
    {
        $url = trim($url, '""');
        switch ($url[0]) {
            case "/":
                return trim($domain, "/") . $url;
            case "h":
                return $url;
            default:
                return $domain . '/' . $url;
        }
    }


    /**
     * @param $url
     * @return mixed
     */
    private function getPage($url)
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        $options = [
            CURLOPT_CUSTOMREQUEST  =>"GET",        //set request type post or get
            CURLOPT_POST           =>false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        ];
        $ch  = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        $data['errno']   = $err;
        $data['errmsg']  = $errmsg;
        $data['content'] = $content;
        $data['headers'] = $headers;
        return $data;
    }

}