<?php
/**
 * Access endpoint info
 * User: moyo
 * Date: 31/03/2017
 * Time: 4:24 PM
 */

namespace NSQClient\Access;

use NSQClient\Exception\InvalidLookupdException;

class Endpoint
{
    /**
     * @var string
     */
    private $lookupd = 'http://127.0.0.1:4161';

    /**
     * @var string
     */
    private $nsqd = "http://127.0.0.1:4151";

    /**
     * @var string
     */
    private $uniqueID = 'hash';

    /**
     * Endpoint constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param $lookupd
     */
    public function setLookupd($lookupd)
    {
        $this->lookupd = $lookupd;
        $this->uniqueID = spl_object_hash($this);

        // checks
        $parsed = parse_url($this->lookupd);
        if (!isset($parsed['host']))
        {
            throw new InvalidLookupdException;
        }
    }

    /**
     * @param $nsqd
     */
    public function setNsqd($nsqd)
    {
        // checks
        $parsed = parse_url($nsqd);
        if (!isset($parsed['host']))
        {
            throw new InvalidLookupdException;
        }
        $this->nsqd = $nsqd;
    }

    /**
     * @return string
     */
    public function getNsqd()
    {
        return $this->nsqd;
    }

    /**
     * @return string
     */
    public function getUniqueID()
    {
        return $this->uniqueID;
    }

    /**
     * @return string
     */
    public function getLookupd()
    {
        return $this->lookupd;
    }

    /**
     * @return string
     */
    public function getConnType()
    {
        return PHP_SAPI == 'cli' ? 'tcp' : 'http';
    }
}