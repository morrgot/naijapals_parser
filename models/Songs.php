<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:48
 */

namespace App\Models;


use Phalcon\Mvc\Model;

/**
 * Class Songs
 * @package App\Models
 *
 * @property Artists $author
 */
class Songs extends BaseModel
{
    public $id;

    public $name;

    public $artist_id;

    public function initialize()
    {
        $this->belongsTo('artist_id', Artists::className(), 'id', 'author');
    }
}