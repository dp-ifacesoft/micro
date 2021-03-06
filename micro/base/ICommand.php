<?php /** MicroInterfaceCommand */

namespace Micro\base;

/**
 * Interface Command
 *
 * @author Oleg Lunegov <testuser@mail.linpax.org>
 * @link https://github.com/lugnsk/micro
 * @copyright Copyright &copy; 2013 Oleg Lunegov
 * @license /LICENSE
 * @package micro
 * @version 1.0
 * @since 1.0
 */
interface ICommand
{
    /**
     * Execute command
     *
     * @access public
     * @return void
     */
    public function execute();
}
