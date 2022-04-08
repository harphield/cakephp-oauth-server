<?php
declare(strict_types=1);

namespace OAuthServer\Bridge;

use Cake\Datasource\EntityInterface;

interface UserFinderByUserCredentialsInterface
{
    /**
     * Find user from repository
     *
     * @param string $username a username
     * @param string $password a password
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function findUser($username, $password): ?EntityInterface;

    /**
     * Get Users repository identity path
     *
     * @return string
     */
    public function getUserIdentityPath();
}
