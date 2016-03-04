<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:21
 */

namespace App\Tasks;

use App\Parsing\Facade;
use Phalcon\Cli\Task;
use Phalcon\Db;
use Phalcon\Di;

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
        p("I'm working!\n");

        $facade = new Facade($this->di);
        $facade->runParsing();
    }

}