<?php
/**
 * Created by PhpStorm.
 * User: aponirovskiy
 * Date: 03.03.2016
 * Time: 15:57
 */

namespace App\Parsing;


use App\Models\Artists;
use App\Models\Songs;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Resultset;

class DBMapper
{
    /**
     * @var \Phalcon\Db\Adapter\Pdo
     */
    protected $db;

    /**
     * @var array
     */
    protected $songs_array;

    /**
     * @var array
     */
    protected $songs_insert = [];

    /**
     * @var array
     */
    protected $artists = [];

    /**
     * @var array
     */
    protected $new_artists = [];

    /**
     * @var array
     */
    protected $old_artists = [];

    /**
     * DBMapper constructor.
     * @param Pdo $db
     */
    public function __construct(Pdo $db)
    {
        $this->db = $db;
        $db->close();
    }

    public function mapIntoDb($songs_array = array())
    {
        $this->songs_array = $songs_array;

        $this->db->connect();

        $this->parseIncomeData();

        $this->filterArtists();

        $this->saveNewArtists();

        $this->prepareSongInsert();

        $this->saveNewSongs();

        $this->db->close();

    }

    /**
     *
     */
    protected function parseIncomeData()
    {
        foreach ($this->songs_array as $item) {
            $this->artists[strtolower($item['author'])] = $item['author'];
        }
    }

    /**
     *
     */
    protected function filterArtists()
    {
        /**
         * @var $artists Artists[]
         */
        $artists = Artists::getByNames($this->artists);
        $this->new_artists = $this->artists;
        foreach ($artists as $artist) {
            $this->addOldArtist($artist->id, $artist->name);
        }
    }

    protected function saveNewArtists()
    {
        // todo: batch insert
        pf('We have %d old artists and %d new artists', count($this->old_artists), count($this->new_artists));
        foreach ($this->new_artists as $new_artist) {
            $model = new Artists();
            $model->name = $new_artist;

            pf('Saving new artist - %s', $new_artist);
            if($model->save()){

                $this->addOldArtist($model->id, $model->name);

                p('New artist saved - '.$new_artist. ' id = '.$model->id);
            } else {
                p('Error saving '.$new_artist.' : ');
                foreach ($model->getMessages() as $message) {
                    p($message->getMessage());
                }
            }
        }
    }

    protected function saveNewSongs()
    {
        // todo: batch insert
        pf('We have %d new songs to save!', count($this->songs_insert));
        foreach ($this->songs_insert as $item) {
            $model = new Songs();
            $model->name = $item['song'];
            $model->artist_id = $item['artist_id'];

            pf('Saving new song - %s', $item['song']);
            if($model->save()){
                p('New song saved - '.$item['song']. ' id = '.$model->id);
            } else {
                p('Error saving '.$item['song'].' : ');
                foreach ($model->getMessages() as $message) {
                    p($message->getMessage());
                }
            }
        }

        $this->songs_insert = [];
    }

    protected function prepareSongInsert()
    {
        foreach ($this->songs_array as $k => $item) {

            $key = strtolower($item['author']);

            if(isset($this->old_artists[$key])){
                $this->songs_array[$k]['artist_id'] = $this->old_artists[$key];
            }else{
                p('unknown artist. Removing from insert');
                unset($this->songs_array[$k]);
            }
        }

        $this->filterExistsSongs();
    }

    protected function filterExistsSongs()
    {

        $where = '';
        foreach ($this->songs_array as $item) {
            $where .= 'name = '.$this->db->escapeString($item['song']) .' AND artist_id = '.(int)$item['artist_id'].' OR ';
        }

        /**
         * @var Resultset $result
         * @var object $song
         */
        $result = Songs::find(array(
            substr($where, 0, -4),
            "hydration" => Resultset::HYDRATE_OBJECTS
        ));

        $old_songs = [];
        foreach ($result as $song) {
            $old_songs[$song->name] = $song;
        }

        $this->songs_insert = [];
        foreach ($this->songs_array as $k => $item) {
            if(!isset($old_songs[$item['song']])){
                $this->songs_insert[] = $item;
            }else{
                pf('Old song found! "%s"', $item['song']);
            }
        }
    }

    /**
     * Adds known artists to $this->old_artists and deletes him from new ones if need
     *
     * @param int|string $id
     * @param string $name
     */
    protected function addOldArtist($id, $name)
    {
        $name = strtolower($name);

        if(isset($this->new_artists[$name])){
            unset($this->new_artists[$name]);
        }

        $this->old_artists[$name] = (int)$id;
    }
}