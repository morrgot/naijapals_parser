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
        //$result = $this->dom->find('.body-content .col-lg-4');
        $result = $this->dom->find('#w1 a');
        v($result[0]);
        $this->dom->clear();
    }
}