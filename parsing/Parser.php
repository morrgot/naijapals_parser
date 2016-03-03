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
     * @var \simple_html_dom
     */
    protected $dom;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var array
     */
    protected $songs = [];

    /**
     * @var string
     */
    protected $next_url;

    public function setHtml($html)
    {
        $this->html = trim($html);
    }

    /**
     * @return array
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * @return \simple_html_dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->next_url;
    }

    public function parse()
    {

        $this->dom = HtmlDomParser::str_get_html( $this->html );


        $this->extractSongs();
        $this->extractNextUrl();
    }

    protected function extractSongs()
    {
        //$this->songs = new \SplFixedArray(30);
        $this->songs = [];

        /**
         * @var $item \simple_html_dom_node
         */
        foreach ($this->dom->find('div.col-xs-6 div.center-block') as $item) {
            $song = $item->find('a.text-nowrap');
            $author = $item->find('small strong');

            $this->songs[] = [
                'song' => trim($song[0]->text()),
                'author' => trim($author[0]->text())
            ];
        }
    }

    protected function extractNextUrl()
    {
        $result = $this->dom->find('ul.pagination li a');

        $url = '';
        if(count($result)){
            $last_a = array_pop($result);
            $url = 'http://www.naijapals.com/'.$last_a->href;
        }

        $this->next_url = $url;
    }

    public function clear()
    {
        $this->dom->clear();
    }
}