<?php
namespace Werkint\Bundle\ProxyBundle\Service;

/**
 * ProxyInterface.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
interface ProxyInterface
{
    /**
     * Fetches list from remote server, stores it in Redis
     * @return bool
     */
    public function updateData();

    /**
     * @param string|null $class
     * @return string
     */
    public function getCurrent(
        $class = null
    );

    /**
     * @param string|null $class
     * @return string
     */
    public function getNext(
        $class = null
    );

    /**
     * Returns full cached list.
     * @return array
     */
    public function getList();

}
