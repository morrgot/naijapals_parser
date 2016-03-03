<?php
/**
 * Created by PhpStorm.
 * User: aponirovskiy
 * Date: 03.03.2016
 * Time: 12:11
 */

namespace App\Parsing;


class HtmlProvider
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var int
     */
    protected $status;

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = trim($url);
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    public function readUrl()
    {
        $this->html = '';

        $ch = curl_init($this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);

        $this->status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($this->status >= 200 && $this->status < 300){
            $this->html = $output;
            return true;
        }else{
            return false;
        }
    }

    protected function validateUrl()
    {

    }
}