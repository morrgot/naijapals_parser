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

    const PAGES_PER_SAVE = 30;

    const MAX_ATTEMPTS = 5;

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
     * @var
     */
    protected $attempts = self::MAX_ATTEMPTS;

    /**
     * Parsing facade constructor.
     *
     * @param DiInterface $di
     * @param string $url
     */
    public function __construct(DiInterface $di, $url = '?L=music.browse&page=2079')
    {
        $this->parser = $di->get('html_parser');
        $this->provider = $di->get('html_provider');
        $this->mapper = $di->get('songs_mapper');
        $this->setUrl($url);
    }

    public function setUrl($url)
    {
        $url = trim($url);

        if(!preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})?([\?\/])?/', $url)){
            $url = self::HOST_URL.'/'.$url;
        }

        $this->url = $url;
    }

    public function runParsing($restore_attempts = false)
    {
        $this->provider->setUrl($this->url);
        pf('Trying to parse %s ..', $this->url);
        if($this->provider->readUrl()){

            $this->pages_processed++;
            $html = $this->provider->getHtml();

            $this->parser->setHtml($html);
            $this->parser->parse();

            $songs = $this->parser->getSongs();

            $this->songs = array_merge($songs, $this->songs);

            if($this->pages_processed % self::PAGES_PER_SAVE == 0){
                p('gonna put songs into db ...');
                $this->map2db();
            }

            $next_url = $this->parser->getNextUrl();

            if(!empty($next_url) /*&& $this->pages_processed < 2*/){
                if($restore_attempts){
                    $this->attempts = self::MAX_ATTEMPTS;
                }

                $this->setUrl($next_url);
                $this->runParsing();
            }else{
                p('Empty url was given. Seems like we need to stop ..');
                $this->map2db();
                p('That\'s it :)');
            }
        }else{
            if(--$this->attempts > 0){
                pf('--Smth went wrong on reading url"%s". Gonna try again in %d sec. We have %d more.',$this->url, 10, $this->attempts);
                sleep(10);
                $this->runParsing(true);
            }

            $this->map2db();
            p('That\'s it :)');
        }

        return true;
    }

    public function map2db()
    {
        $this->mapper->mapIntoDb($this->songs);
        $this->songs = null;
        $this->songs = [];
    }

}