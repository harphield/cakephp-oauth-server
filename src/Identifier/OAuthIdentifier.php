<?php
declare(strict_types=1);

namespace OAuthServer\Identifier;

use Authentication\Identifier\AbstractIdentifier;
use Authentication\Identifier\Resolver\ResolverAwareTrait;
use Authentication\Identifier\Resolver\ResolverInterface;

class OAuthIdentifier extends AbstractIdentifier
{
    use ResolverAwareTrait;

    public const CREDENTIAL_OAUTH = 'oauth_user_id';

    /**
     * - `fields` The fields to use to identify a user by:
     *   - `oauth_user_id`: one or many oauth_user_id fields.
     * - `resolver` The resolver implementation to use.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            self::CREDENTIAL_OAUTH => 'id',
        ],
        'resolver' => 'Authentication.Orm',
        'oauthIdentityKey' => '_oauth',
    ];

    /**
     * @inheritDoc
     */
    public function identify(array $data)
    {
        if (!isset($data[self::CREDENTIAL_OAUTH])) {
            return null;
        }

        $identity = $this->_findIdentity($data[self::CREDENTIAL_OAUTH]);

        if ($identity) {
            $identity->set($this->getConfig('oauthIdentityKey'), $data['oauth']);
        }

        return $identity;
    }

    /**
     * Find a user record using the username/identifier provided.
     *
     * @param string $identifier The username/identifier.
     * @return \ArrayAccess|array|null
     */
    protected function _findIdentity(string $identifier)
    {
        $fields = $this->getConfig('fields.' . self::CREDENTIAL_OAUTH);
        $conditions = [];
        foreach ((array)$fields as $field) {
            $conditions[$field] = $identifier;
        }

        return $this->getResolver()->find($conditions, ResolverInterface::TYPE_OR);
    }
}
