<?php namespace Tartan\Signature;

class Auth
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $auth = [
        'key',
        'version',
        'timestamp',
        'signature'
    ];

    /**
     * Create a new Auth instance
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     * @param array $guards
     * @return void
     */
    public function __construct($method, $uri, $version, array $params, array $guards)
    {
        $this->method  = strtoupper($method);
        $this->uri     = $uri;
        $this->version = $version;
        $this->params  = $params;
        $this->guards  = $guards;
    }

    /**
     * Attempt to authenticate a request
     *
     * @param Token  $token
     * @param string $prefix
     * @return bool
     */
    public function attempt(Token $token, $prefix = Request::PREFIX)
    {
        $auth = $this->getAuthParams($prefix);
        $body = $this->getBodyParams($prefix);

        $request   = new Request($this->method, $this->uri, $body, $auth[$prefix . 'timestamp'], $this->version);
        $signature = $request->sign($token, $prefix);

        foreach ($this->guards as $guard) {
            $guard->check($auth, $signature, $prefix);
        }

        return true;
    }

    /**
     * Get the auth params
     *
     * @param $prefix
     * @return array
     */
    protected function getAuthParams($prefix)
    {
        return array_intersect_key($this->params, array_flip($this->addPrefix($this->auth, $prefix)));
    }

    /**
     * Get the body params
     *
     * @param $prefix
     * @return array
     */
    protected function getBodyParams($prefix)
    {
        return array_diff_key($this->params, array_flip($this->addPrefix($this->auth, $prefix)));
    }

    /**
     * @param array $auth
     * @param       $prefix
     *
     * @return array
     */
    protected function addPrefix(array $auth, $prefix)
    {
        return array_map(function ($item) use ($prefix) {
            return $prefix . $item;
        }, $auth);
    }
}
