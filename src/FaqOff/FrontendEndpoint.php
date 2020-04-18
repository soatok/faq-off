<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Splices\Accounts;

/**
 * Class FrontendEndpoint
 * @package Soatok\FaqOff
 */
abstract class FrontendEndpoint extends Endpoint
{
    /** @var Accounts $accounts */
    protected $accounts;

    /** @var bool $canInvite */
    public $canInvite;

    /**
     * FrontendEndpoint constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        /** @var Accounts $accounts */
        $accounts = $this->splice('Accounts');
        if (!empty($_SESSION['account_id'])) {
            $this->canInvite = $accounts->accountCanInvite($_SESSION['account_id']);
            $this->accounts = $accounts;
            if (!defined('PHPUNIT_FAQOFF_TESTSUITE')) {
                $this->setTwigVar('can_invite', $this->canInvite);
            }
        } else {
            $this->canInvite = false;
        }
    }
}
