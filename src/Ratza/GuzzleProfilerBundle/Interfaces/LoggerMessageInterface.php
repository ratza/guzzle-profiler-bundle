<?php
/**
 * This file is part of the Z package.
 *
 * @author Ion Marusic <ion.marusic@gmail.com>
 */

namespace Ratza\GuzzleProfilerBundle\Interfaces;

/**
 * Interface LoggerMessageInterface
 * Should be implemented by the request and response objects that the Guzzle Client logs
 *
 * @package Ratza\GuzzleProfilerBundle\Interfaces
 */
interface LoggerMessageInterface
{
    /**
     * Set at what time was the request was sent or the response was received
     * If null is passed a new microtime(true) is set as the timestamp.
     *
     * @param int|null $timestamp
     */
    public function setTimestamp($timestamp = null);

    /**
     * Gets the time at which was the request was sent or the response was received
     *
     * @return int
     */
    public function getTimestamp();
}
