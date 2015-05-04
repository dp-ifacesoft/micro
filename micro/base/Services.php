<?php /** MicroServices */

namespace Micro\base;

/**
 * Services class file.
 *
 * @author Oleg Lunegov <testuser@mail.linpax.org>
 * @link https://github.com/lugnsk/micro
 * @copyright Copyright &copy; 2013 Oleg Lunegov
 * @license /LICENSE
 * @package micro
 * @version 1.0
 * @since 1.0
 */
class Services
{
    /** @var array $servers Configuration servers */
    protected $servers = [];
    /** @var array $routes Configuration routes */
    protected $routes = [];
    /** @var array $brokers Started servers */
    protected $brokers = [];


    /**
     * Initialize service manager
     *
     * @access public
     *
     * @param array $params Initialization parameters
     *
     * @result void
     */
    public function __construct( array $params = [] )
    {
        $this->servers = !empty($params['servers']) ? $params['servers'] : [];
        $this->routes  = !empty($params['routes'])  ? $params['routes']  : [];
    }

    /**
     * @param string $uri
     *
     * @return \Micro\queues\IQueue
     * @throws Exception
     */
    private function getBroker($uri, $type, $retry)
    {
        $servers = $this->getServersFromRoute($this->getRoute($uri), $type);
        $server = null;

        for ( $counter = 0; $counter < $retry; $counter++ ) {
            $random = rand(0, count($servers)-1);

            if (!array_key_exists($servers[$random], $this->brokers)) {
                $cls = $this->servers[$servers[$random]];
                $this->brokers[$servers[$random]] = new $cls['class']($cls);
            }
            if ($this->brokers[$servers[$random]]->test()) {
                $server = $servers[$random];
            }
        }
        if (!$server) {
            throw new Exception('Message not send, random servers is down into `' . $uri . '`');
        }

        return $this->brokers[$server];
    }

    /**
     * Get rules from route by pattern
     *
     * @access protected
     *
     * @param string $uri URI for match
     *
     * @return array Rules for URI
     * @throws Exception
     */
    protected function getRoute($uri)
    {
        $keys = array_keys($this->routes);
        $countRoutes = count($keys);

        for ($a=0; $a < $countRoutes; $a++) {
            if (preg_match('/'.$keys[$a].'/', $uri)) {
                if (!is_array($this->routes[ $keys[$a] ])) {
                    $this->routes[ $keys[$a] ] = [ '*' => $this->routes[ $keys[$a] ] ];
                }
                return $this->routes[ $keys[$a] ]; // роут найден
            }
        }
        throw new Exception('Route `' . $uri . '` not found');
    }

    /**
     * Get servers list from routing rule
     *
     * @access protected
     *
     * @param array $route Routing rule
     * @param string $type Sending type
     *
     * @return array
     * @throws Exception
     */
    protected function getServersFromRoute( array $route, $type = '*' )
    {
        $servers = [];

        foreach ($route AS $key=>$val) {
                if (is_string($val)) {
                    $route['*'] = [ $val ];
                    unset($route[$key]);
                }
        }
        if (array_key_exists($type, $route)) {
            $servers += $route[$type];
        }
        if (array_key_exists('*', $route)) {
            $servers += $route['*'];
        }
        if (!$servers) {
            throw new Exception( 'Type `' . $type . '` not found into route' );
        }

        return $servers;
    }

    /**
     * Send message into service on selected server
     *
     * @access public
     *
     * @param string $route
     * @param array $data
     * @param string $type
     * @param int $retry
     *
     * @return mixed
     * @throws Exception
     */
    public function send( $route, array $data = [], $type = 'sync', $retry = 5 )
    {
        switch($type) {
            case 'sync': {
                return $this->getBroker($route, $type, $retry)->sync($route, $data);
                break;
            }
            case 'async': {
                return $this->getBroker($route, $type, $retry)->async($route, $data);
                break;
            }
            case 'stream': {
                return $this->getBroker($route, $type, $retry)->stream($route, $data);
                break;
            }
            default: {
                throw new Exception('Service type `' . $type . '` wrong name.');
            }
        }
    }
}