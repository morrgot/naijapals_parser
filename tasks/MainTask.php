<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:21
 */

namespace App\Tasks;

use App\Models\Artists;
use App\Parsing\HtmlProvider;
use App\Parsing\Parser;
use Phalcon\Cli\Task;

/**
 * Class MainTask
 * @package App\Tasks
 *
 * @property \App\Parsing\HtmlProvider $html_provider
 * @property \App\Parsing\Parser $html_parser
 */
class MainTask extends Task
{

    public function runAction()
    {
        echo 'I\'m working!';

        $this->html_provider->setUrl('http://www.naijapals.com/?L=music.browse&page=1');

        $arr = new \SplFixedArray(20);
        $arr[0] = array('asdasd');
        v($arr[0]);

        /*if($this->html_provider->readUrl()){
            //v($this->html_provider->getStatus());
            $html = $this->html_provider->getHtml();

            $this->html_parser->setHtml($html);
            $this->html_parser->parse();

            v($this->html_parser->getNextUrl());
            v($this->html_parser->getSongs());

            //file_put_contents(time().'.html',$html);
        }*/


    }

}