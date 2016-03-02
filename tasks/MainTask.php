<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:21
 */

namespace App\Tasks;

use App\Models\Artists;
use App\Parsing\Parser;
use Phalcon\Cli\Task;

class MainTask extends Task
{

    public function runAction()
    {
        echo 'I\'m working!';

        $parser = new Parser();
        $parser->setUrl('http://basic.dev');
        $parser->readUrl();
    }

}