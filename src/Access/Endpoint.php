<?php
/**
 * Access endpoint info
 * User: moyo
 * Date: 31/03/2017
 * Time: 4:24 PM
 */

namespace NSQClient\Access;

class Endpoint
{
    /**
     * @var string
     */
    private $lookupd = 'http://nsqlookupd.local.moyo.im:4161';

    /**
     * @var string
     */
    private $uniqueID = 'hash';

    /**
     * Endpoint constructor.
     * @param $lookupd
     */
    public function __construct($lookupd)
    {
        $this->lookupd = $lookupd;
        $this->uniqueID = spl_object_hash($this);
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