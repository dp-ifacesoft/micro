<?php /** MicroAction */

namespace Micro\mvc;

use Micro\base\IContainer;

/**
 * Class Action
 *
 * @author Oleg Lunegov <testuser@mail.linpax.org>
 * @link https://github.com/lugnsk/micro
 * @copyright Copyright &copy; 2013 Oleg Lunegov
 * @license /LICENSE
 * @package micro
 * @subpackage mvc
 * @version 1.0
 * @since 1.0
 */
abstract class Action
{
    /** @var IContainer $container */
    protected $container;

    /**
     * @param IContainer $container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Running action
     *
     * @access public
     *
     * @return mixed
     */
    abstract public function run();
}
