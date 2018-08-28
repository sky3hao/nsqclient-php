<?php
/**
 * Conn for Lookupd
 * User: moyo
 * Date: 31/03/2017
 * Time: 4:53 PM
 */

namespace NSQClient\Connection;

use NSQClient\Access\Endpoint;
use NSQClient\Connection\Transport\HTTP;
use NSQClient\Exception\LookupTopicException;
use NSQClient\Logger\Logger;

class Lookupd
{
    /**
     * @var string
     */
    private static $queryFormat = '/lookup?topic=%s';

    /**
     * @var string
     */
    private static $nodesFind = '/nodes';

    /**
     * @var string
     */
    private static $createTopic = '/topic/create?topic=%s';

    /**
     * @var array
     */
    private static $caches = [];

    /**
     * @param Endpoint $endpoint
     * @param $topic
     * @param $switch
     * @return array
     * @throws LookupTopicException
     */
    public static function getNodes(Endpoint $endpoint, $topic, $switch = true)
    {
        if (isset(self::$caches[$endpoint->getUniqueID()][$topic]))
        {
            return self::$caches[$endpoint->getUniqueID()][$topic];
        }

        $url = $endpoint->getLookupd() . sprintf(self::$queryFormat, $topic);

        list($error, $result) = HTTP::get($url);

        if ($error)
        {
            list($netErrNo, $netErrMsg) = $error;

            if ($netErrNo == 22 && $switch == true) {
                $url = self::getAvailabelNodeAddr($endpoint) . sprintf(self::$createTopic, $topic);
                HTTP::post($url, []);
                return self::getNodes($endpoint, $topic, false);
            } else {
                Logger::ins()->error('Lookupd request failed', ['no' => $netErrNo, 'msg' => $netErrMsg]);
                throw new LookupTopicException($netErrMsg, $netErrNo);
            }
        }
        else
        {
            Logger::ins()->debug('Lookupd results got', ['raw' => $result]);
            return self::$caches[$endpoint->getUniqueID()][$topic] = self::parseResult($result, $topic);
        }
    }

    /**
     * Get available Node-Address
     *
     * @param Endpoint $endpoint
     * @return string
     */
    private static function getAvailabelNodeAddr(Endpoint $endpoint)
    {
        list($err, $rsRaw) = HTTP::get($endpoint->getLookupd() . self::$nodesFind);
        if ($err) {
            list($netErrNo, $netErrMsg) = $err;
            throw new LookupTopicException($netErrMsg, $netErrNo);
        }

        $nodes = self::parseResult($rsRaw);
        $node = $nodes[array_rand($nodes)];

        return $node['host'] . ':' . $node['ports']['http'];
    }
    /**
     * @param $rawJson
     * @param $scopeTopic
     * @return array
     */
    private static function parseResult($rawJson, $scopeTopic = '')
    {
        $result = json_decode($rawJson, true);

        $nodes = [];

        if (isset($result['producers']))
        {
            foreach ($result['producers'] as $producer)
            {
                $nodes[] = [
                    'topic' => $scopeTopic,
                    'host' => $producer['broadcast_address'],
                    'ports' => [
                        'tcp' => $producer['tcp_port'],
                        'http' => $producer['http_port']
                    ]
                ];
            }
        }

        return $nodes;
    }
}