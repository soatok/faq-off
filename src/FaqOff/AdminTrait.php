<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use Slim\Container;

/**
 * Trait AdminTrait
 * @package Soatok\FaqOff
 *
 * @property Container $container
 */
trait AdminTrait
{
    /**
     * @return int[]
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getAdminAccountIDs(): array
    {
        if (is_readable(APP_ROOT . '/local/admins.json')) {
            $data = json_decode(
                file_get_contents(APP_ROOT . '/local/admins.json'),
                true
            );
            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        $settings = $this->container->get('settings')['settings'];
        return $settings['admin-accounts'] ?? [];
    }
}
