<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 03.03.2016
 * Time: 0:44
 */

namespace App\Models;


use Phalcon\Mvc\Model;

/**
 * Class Artists
 * @package App\Models
 *
 * @property Songs[] $songs
 */
class Artists extends BaseModel
{

    public $id;

    public $name;

    public $hash;

    public function initialize()
    {
        $this->hasMany('id', Songs::className(), 'artist_id', 'author');
    }

    public static function getByNames($names)
    {
        return self::query()
            ->inWhere('name', $names)
            ->execute();
    }

    public static function getByHashes($hashes)
    {
        return self::query()
            ->inWhere('hash', $hashes)
            ->execute();
    }

    public function beforeSave()
    {
        $this->hash = self::hashArtistName($this->name);
    }

    public static function hashArtistName($name)
    {
        return md5(strtolower($name));
    }
}