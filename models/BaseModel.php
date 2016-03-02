<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:49
 */

namespace App\Models;


use Phalcon\Mvc\Model;

abstract class BaseModel extends Model
{
    public static function className()
    {
        return get_called_class();
    }
}