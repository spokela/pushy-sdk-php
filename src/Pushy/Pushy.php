<?php
/**
 * Pushy - Publish/Subscribe system for realtime webapps.
 *
 * Copyright (c) 2013-2014, Spokela <info@spokela.com>.
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * PHP Version 5.3
 * 
 * @category  Pushy
 * @package   Pushy
 * @author    Julien Ballestracci <julien@nitronet.org>
 * @copyright 2013-2014 Spokela <info@spokela.com>
 * @license   http://opensource.org/licenses/MIT  MIT License
 * @link      http://www.spokela.com
 */
namespace Pushy;

/**
 * Pushy Main class
 * 
 * @category Utilities
 * @package  Pushy
 * @author   Julien Ballestracci <julien@nitronet.org>
 * @license  http://opensource.org/licenses/MIT  MIT License
 * @link     http://www.spokela.com
 */
class Pushy
{
    /**
     * Full URL to Pushy server (scheme://hostname:port)
     * 
     * @var string
     */
    protected $url;
    
    /**
     * Pushy's server secret key
     * 
     * @var string
     */
    protected $secret;

    /**
     * Constructor
     * 
     * @param string $pushyUrl  Full URL to Pushy server (scheme://hostname:port)
     * @param string $secretKey Pushy's server secret key
     * 
     * @throws \Exception if cURL isn't available
     * @return void
     */
    public function __construct($pushyUrl, $secretKey)
    {
        // check cURL
        if (!extension_loaded('curl')) {
            throw new \Exception("cURL extension is required to use pushy");
        }

        $this->url      = $pushyUrl;
        $this->secret   = $secretKey;
    }

    /**
     * Prepare authorization token to access private channels.
     * Returns an array to be sent to the client (not encoded).
     * 
     * @param string  $channel       The channel name
     * @param string  $socketId      The connection ID
     * @param boolean $allowed       Allow/Deny access
     * @param array   $presenceData  Optional presence data
     * 
     * @return array
     */
    public function authorize($channel, $socketId, $allowed = true, 
        array $presenceData = null
    ) {
        if (!$allowed) {
            return array("error" => "not authorized");
        }
        
        $sig = sprintf('%s:%s', $socketId, $channel);
        if ($presenceData !== null) {
            // double-encoding
            $sig .= ':'. json_encode(json_encode($presenceData));
        }
        
        $sig .= ':'. $this->secret;
        $final = array('auth' => hash('sha256', str_replace('\\\\\\/', '\\\\/', $sig)));
        if ($presenceData !== null) {
            $final['data'] = json_encode($presenceData);
        }
        
        return $final;
    }

    /**
     * Trigger an event on a given Pushy channel
     * 
     * @param string $channel The Channel name
     * @param string $event   The Event name
     * @param array  $data    Serializable data sent with the event
     * 
     * @return void
     */
    public function trigger($channel, $event, $data = array())
    {
        $ts     = time(null);
        $sign   = sprintf(
            '%s:%s:%s:%s:%s', 
            strtolower($channel), 
            $event, 
            $ts, 
            json_encode($data), 
            $this->secret
        );
        
        $key    = hash('sha256', $sign);
        $url    = sprintf(
            '%s/pushy/channel/%s/trigger?event=%s&timestamp=%s&auth_key=%s',
            rtrim($this->url, '/'),
            $channel,
            $event,
            $ts,
            $key
        );
        
        $feed   = curl_init();
        curl_setopt_array($feed, array(
            CURLOPT_POST => 1,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query($data)
        )); 
        
        $json   = curl_exec($feed);
        curl_close($feed);
        
        $obj    = json_decode($json);
    }
    
    /**
     * Gets the Pushy server's full URL
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Defines the Pushy server's full URL (scheme://hostname:port)
     * 
     * @param string $url Full URL to Pushy server
     * 
     * @return Pushy
     */
    public function setUrl($url)
    {
        $this->url = $url;
        
        return $this;
    }

    /**
     * Gets the Pushy server's secret key
     * 
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * Defines the Pushy server's secret key
     * 
     * @param string $secret Secret key
     * 
     * @return Pushy
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        
        return $this;
    }
}