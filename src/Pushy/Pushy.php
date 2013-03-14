<?php
namespace Pushy;


class Pushy
{
    protected $url;
    
    protected $secret;  

    public function __construct($pushyUrl, $secretKey)
    {
        if (!extension_loaded('curl')) {
            throw new \Exception("cURL extension is required to use pushy");
        }

        $this->url      = $pushyUrl;
        $this->secret   = $secretKey;
    }

    public function authorize($channel, $socketId, $allowed = true, 
        array $presenceData = null
    ) {
        if (!$allowed) {
            return array("error" => "not authorized");
        }
        
        $sig = sprintf('%s:%s', $socketId, $channel);
        if ($presenceData !== null) {
            $sig .= json_encode(json_encode($presenceData));
        }
        
        $sig .= $this->secret;
        $final = array('auth' => hash('sha256', str_replace('\\\\\\/', '\\\\/', $sig)));
        if ($presenceData !== null) {
            $final['data'] = json_encode($presenceData);
        }
        
        return $final;
    }

    public function trigger($channel, $event, $data = array())
    {
        $ts = time(null);
        $sign = sprintf('%s:%s:%s%s%s', $channel, $event, $ts, ((is_array($data) && count($data)) ? json_encode($data) : ""), $this->secret);
        
        $key = hash('sha256', $sign);
        $url = sprintf('%s/channel/%s/trigger?event=%s&timestamp=%s&auth_key=%s',
                rtrim($this->url, '/'),
                strtolower($channel),
                $event,
                $ts,
                $key);
        
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query($data)
        );

        $feed = curl_init();
        curl_setopt_array($feed, $defaults); 
    
        $json = curl_exec($feed);
        curl_close($feed);
        $obj = json_decode($json);
    }
}