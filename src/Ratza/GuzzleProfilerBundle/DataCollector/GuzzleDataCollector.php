<?php
/**
 * This file is part of the Ratza package.
 *
 * @author Ion Marusic <ion.marusic@gmail.com>
 */

namespace Ratza\GuzzleProfilerBundle\DataCollector;

use GuzzleHttp\Subscriber\History;

use GuzzleHttp\Message\RequestInterface as GuzzleRequestInterface;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponseInterface;
use Ratza\GuzzleProfilerBundle\Interfaces\LoggerMessageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Guzzle Profiler DataCollector.
 *
 * @package Ratza\GuzzleProfilerBundle\DataCollector
 */
class GuzzleDataCollector extends DataCollector
{
    /**
     * @var History
     */
    private $profiler;

    /**
     * Constructor.
     *
     * @param History $profiler
     */
    public function __construct(History $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = array(
            'calls'       => array(),
            'error_count' => 0,
            'methods'     => array(),
            'total_time'  => 0,
        );

        /**
         * Aggregates global metrics about Guzzle usage
         *
         * @param array $request
         * @param array $response
         * @param array $time
         * @param bool  $error
         */
        $aggregate = function ($request, $response, $time, $error) use (&$data) {

            $method = $request['method'];
            if (!isset($data['methods'][$method])) {
                $data['methods'][$method] = 0;
            }

            $data['methods'][$method]++;
            $data['total_time'] += $time['total'];
            $data['error_count'] += (int) $error;
        };

        foreach ($this->profiler as $call) {
            $request = $call['sent_request'];
            $response = $call['response'];
            $requestData = $this->collectRequest($request);
            $responseData = $this->collectResponse($response);
            $time = $this->collectTime($request, $response);
            /** @var GuzzleResponseInterface $response */
            $error =
                null === $call['response'] ||
                100 > $response->getStatusCode() ||
                400 <= $response->getStatusCode();

            $aggregate($requestData, $responseData, $time, $error);

            $data['calls'][] = array(
                'request' => $requestData,
                'response' => $responseData,
                'time' => $time,
                'error' => $error
            );
        }

        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getCalls()
    {
        return isset($this->data['calls']) ? $this->data['calls'] : array();
    }

    /**
     * @return int
     */
    public function countErrors()
    {
        return isset($this->data['error_count']) ? $this->data['error_count'] : 0;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return isset($this->data['methods']) ? $this->data['methods'] : array();
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return isset($this->data['total_time']) ? $this->data['total_time'] : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }

    /**
     * Collect & sanitize data about a Guzzle request
     *
     * @param GuzzleRequestInterface $request
     *
     * @return array
     */
    private function collectRequest(GuzzleRequestInterface $request)
    {
        return array(
            'headers' => $request->getHeaders(),
            'method'  => $request->getMethod(),
            'scheme'  => $request->getScheme(),
            'host'    => $request->getHost(),
            'path'    => $request->getPath(),
            'query'   => $request->getQuery(),
            'body'    => (string) $request->getBody()
        );
    }

    /**
     * Collect & sanitize data about a Guzzle response
     *
     * @param GuzzleResponseInterface $response
     *
     * @return array
     */
    private function collectResponse(GuzzleResponseInterface $response = null)
    {
        if ($response === null) {
            return array(
                'statusCode'    => null,
                'reasonPhrase'  => null,
                'headers'       => null,
                'body'          => null
            );
        }

        return array(
            'statusCode'   => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'headers'      => $response->getHeaders(),
            'body'         => (string) $response->getBody()
        );
    }

    /**
     * Collect time for a Guzzle request
     *
     * @param GuzzleRequestInterface  $request
     * @param GuzzleResponseInterface $response
     *
     * @return array
     */
    private function collectTime(GuzzleRequestInterface $request, GuzzleResponseInterface $response = null)
    {
        if (!($request instanceof LoggerMessageInterface) || !($response instanceof LoggerMessageInterface)) {
            $time = 0;
        } else {
            $time = null !== $response ? $response->getTimestamp() - $request->getTimestamp() : 0;
        }

        return array(
            'total' => $time,
        );
    }
}
