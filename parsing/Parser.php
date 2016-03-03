<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:55
 */

namespace App\Parsing;


use Phalcon\Mvc\User\Plugin;
use Sunra\PhpSimple\HtmlDomParser;

class Parser
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var \simple_html_dom
     */
    protected $dom;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function readUrl()
    {
        /**
         * @var $result \simple_html_dom_node[]
         */
        $this->dom = HtmlDomParser::file_get_html( $this->url );
        $result = $this->dom->find('div.col-xs-6 div.center-block');
        $arr = [];
        foreach ($result as $item) {
            $song = $item->find('a.text-nowrap');
            $author = $item->find('small strong');


            $arr[] = [
                'song' => $song[0]->text(),
                'author' => $author[0]->text()
            ];
        }

        p($arr);
        $this->dom->clear();
    }
}