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
use Phalcon\Db;
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
    protected $songs_insert;

    /**
     * @var array
     */
    protected $artists_hashes;

    /**
     * @var array
     */
    protected $new_artists;

    /**
     * @var array
     */
    protected $old_artists;

    /**
     * @var Artists[]
     */
    protected $artists_found;

    /**
     * @var int
     */
    protected $added_artists_count;

    /**
     * @var int
     */
    protected $added_songs_count;

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

        pf('Mapper received %d songs! ..', count($this->songs_array));

        $this->db->connect();

        $this->initAll();

        try {
            $this->parseIncomeData();

            $this->filterArtists();

            $this->saveNewArtists();

            $this->prepareSongInsert();

            $this->saveNewSongs();
        } catch (\Exception $e){
            pf('%s : %s', get_class($e), $e->getMessage());

            $str = '';
            $str .= "new artists:\n";
            $str .= print_r($this->new_artists,1);

            $str .= "old artists:\n";
            $str .= print_r($this->old_artists,1);

            $str .= "found artists:\n";
            $str .= print_r($this->artists_found->toArray(),1);

            file_put_contents(RUNTIME_PATH.'/error_log.txt', $str);

            throw new \Exception('aborting all process');
        }

        $this->db->close();
        $this->clearAll();
    }

    /**
     *
     */
    protected function parseIncomeData()
    {
        foreach ($this->songs_array as $item) {
            $hash = Artists::hashArtistName($item['author']);

            $this->new_artists[$hash] = $item['author'];
            $this->artists_hashes[] = $hash;
        }

        $this->artists_hashes = array_unique($this->artists_hashes);
    }

    /**
     *
     */
    protected function filterArtists()
    {
        pf('gonna find %d artists!', count($this->artists_hashes));
        $this->artists_found = Artists::getByHashes($this->artists_hashes);
        foreach ($this->artists_found as $artist) {
            $this->addOldArtist($artist);
        }
    }

    protected function saveNewArtists()
    {
        // todo: batch insert
        pf('We have %d old artists and %d new artists', count($this->old_artists), count($this->new_artists));
        foreach ($this->new_artists as $new_artist) {
            $model = new Artists();
            $model->name = $new_artist;

            if($model->save()){

                $this->addOldArtist($model);

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

            if($model->save()){
                p('New song saved - '.$item['song']. ' id = '.$model->id);
            } else {
                p('Error saving '.$item['song'].' : ');
                foreach ($model->getMessages() as $message) {
                    p($message->getMessage());
                }
            }
        }
    }

    protected function prepareSongInsert()
    {
        foreach ($this->songs_array as $k => $item) {

            $key = Artists::hashArtistName($item['author']);

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
            $where .= '(name = '.$this->db->escapeString($item['song']) .' AND artist_id = '.(int)$item['artist_id'].') OR ';
        }

        /**
         * @var \Phalcon\Db\Result\Pdo $result
         * @var object $song
         */

        $result = $this->db->query('SELECT `id`, `name` FROM `songs` WHERE '.substr($where, 0, -4).';');
        $result->setFetchMode(Db::FETCH_OBJ);

        $old_songs = [];
        foreach ($result->fetchAll() as $song) {
            $old_songs[$song->name] = $song;
        }

        foreach ($this->songs_array as $k => $item) {
            if(!isset($old_songs[$item['song']])){
                $this->songs_insert[] = $item;
            }
        }

        pf('Found %d old songs! ', count($this->songs_array)-count($this->songs_insert));
    }

    /**
     * Adds known artists to $this->old_artists and deletes him from new ones if need
     *
     * @param Artists $artist
     */
    protected function addOldArtist(Artists $artist)
    {
        if(isset($this->new_artists[$artist->hash])){
            unset($this->new_artists[$artist->hash]);
        }

        $this->old_artists[$artist->hash] = (int)$artist->id;
    }

    protected function clearAll()
    {
        $this->artists_found = null;
        $this->artists_hashes = null;
        $this->new_artists = null;
        $this->old_artists = null;
        $this->songs_insert = null;
    }

    protected function initAll()
    {
        $this->new_artists = [];
        $this->old_artists = [];
        $this->artists_hashes = [];
        $this->songs_insert = [];
    }
}