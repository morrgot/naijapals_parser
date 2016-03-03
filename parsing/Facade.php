<?php
/**
 * Created by PhpStorm.
 * User: aponirovskiy
 * Date: 03.03.2016
 * Time: 15:31
 */

namespace App\Parsing;


use Phalcon\DiInterface;

class Facade
{
    const HOST_URL = 'http://www.naijapals.com';

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var HtmlProvider
     */
    protected $provider;

    /**
     * @var DBMapper
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $songs = [];

    /**
     * @var int
     */
    protected $pages_processed = 0;

    /**
     * Parsing facade constructor.
     *
     * @param DiInterface $di
     * @param string $url
     */
    public function __construct(DiInterface $di, $url = self::HOST_URL.'/?L=music.browse&page=1')
    {
        $this->parser = $di->get('html_parser');
        $this->provider = $di->get('html_provider');
        $this->mapper = $di->get('songs_mapper');
        $this->url = trim($url);
    }

    public function setUrl($url)
    {
        $url = trim($url);

    }

    public function runParsing()
    {
        $this->provider->setUrl($this->url);

        if($this->provider->readUrl()){

            $this->pages_processed++;
            p('processed '.$this->pages_processed.' pages..');
            $html = $this->provider->getHtml();

            $this->parser->setHtml($html);
            $this->parser->parse();

            $this->url = $this->parser->getNextUrl();
            $songs = $this->parser->getSongs();

            $this->songs = array_merge($songs, $this->songs);

            if($this->pages_processed % 10 == 0){
                $this->map2db();
            }

            $this->runParsing();
        }

        return true;
    }

    public function map2db()
    {
        $this->mapper->mapIntoDb($this->songs);
        $this->songs = [];
    }

}