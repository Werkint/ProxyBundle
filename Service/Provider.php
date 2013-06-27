<?php
namespace Werkint\Bundle\ProxyBundle\Service;

use Doctrine\Common\Cache\CacheProvider;

class Proxy
{
    const PROXY_URL = 'http://premium.freeproxy.ru/proxies/csv/?countries%5B%5D=Russian+Federation&type%5B%5D=HTTPS&anon%5B%5D=ANM&ports=&sort_by=trust&sort_order=desc&per_page=50&code=';

    protected $cacher;
    protected $code;

    public function __construct(
        CacheProvider $cacher,
        array $parameters
    ) {
        $this->cacher = $cacher;
        $this->code = $parameters['code'];

        $this->fetchData();
    }

    protected $list;

    public function fetchData()
    {
        $this->list = $this->cacher->fetch('list');
        if (!$this->list) {
            $this->updateData();
        }
    }

    public function updateData()
    {
        $url = static::PROXY_URL . $this->code;
        $data = file_get_contents($url);
        $data = str_getcsv($data);
        // NOT READY

        $this->cacher->save('list', $data);
        $this->list = $data;
    }
}