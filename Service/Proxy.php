<?php
namespace Werkint\Bundle\ProxyBundle\Service;

use Doctrine\Common\Cache\CacheProvider;
use Guzzle\Service\Client;

/**
 * Proxy.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Proxy implements
    ProxyInterface
{
    protected $cacher;
    protected $url;
    protected $list;
    protected $lists;

    public function __construct(
        CacheProvider $cacher,
        array $parameters
    ) {
        $this->cacher = $cacher;
        $this->url = $parameters['url'];

        $this->fetchData();
    }

    /**
     * Returns full cached list.
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Finds cached list for certain class
     * @param string|null $class
     * @return string[]
     */
    protected function &getListByClass($class = null)
    {
        if ($class) {
            if (!isset($this->lists[$class])) {
                $this->lists[$class] = $this->list;
            }
            return $this->lists[$class];
        }
        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    public function getNext(
        $class = null
    ) {
        $this->getCurrent($class);
        if ($class) {
            if (!isset($this->lists[$class])) {
                $this->lists[$class] = $this->list;
                shuffle($this->lists[$class]);
            }
            $list = & $this->lists[$class];
        } else {
            $list = & $this->list;
        }
        $row = array_shift($list);
        array_push($list, $row);
        $this->cacher->save('list', $this->list);
        $this->cacher->save('lists', $this->lists);
        return $row['url'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent(
        $class = null
    ) {
        if ($class) {
            if (!isset($this->lists[$class])) {
                $this->lists[$class] = $this->list;
                shuffle($this->lists[$class]);
            }
            return $this->lists[$class][0]['url'];
        }
        return $this->list[0]['url'];
    }

    /**
     * {@inheritdoc}
     */
    public function updateData()
    {
        $client = new Client();
        $ret = $client->get(
            $this->url,
            [],
            ['timeout' => 200]
        )->send();
        $data = $ret->getBody(true);
        $data = explode("\n", $data);
        array_shift($data);

        $this->list = [];
        foreach ($data as $row) {
            $row = str_getcsv($row);
            if (count($row) != 6) {
                continue;
            }
            $this->list[] = [
                'url'  => $row[0] . ':' . $row[1],
                'name' => $row[2] . ' ' . $row[3] . ' ' . $row[4],
            ];
        }

        $this->cacher->save('list', $this->list);
        $this->cacher->save('lists', []);
        return true;
    }

    /**
     * Fetches cached data
     * @return bool
     */
    protected function fetchData()
    {
        $this->list = $this->cacher->fetch('list');
        if (!$this->list) {
            $this->updateData();
        }

        $this->lists = $this->cacher->fetch('lists');
        if (!$this->lists) {
            $this->lists = [];
        }

        return true;
    }

}