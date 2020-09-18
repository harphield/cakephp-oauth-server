<?php
declare(strict_types=1);

namespace OAuthServer\Model\Entity;

use Cake\ORM\Entity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use OAuthServer\Bridge\Entity\ExpiryDateTimeTrait;
use OAuthServer\Bridge\Entity\TokenEntityTrait;

/**
 * implemented AccessTokenEntityInterface
 *
 * @property string $oauth_token
 * @property int $expires
 * @property string $client_id
 * @property string|int $user_id
 * @property bool $revoked
 * @property \OAuthServer\Model\Entity\Client $client
 * @property \OAuthServer\Bridge\Entity\User $user
 * @property \OAuthServer\Model\Entity\Scope[] $scopes
 */
class AccessToken extends Entity implements AccessTokenEntityInterface
{
    use AccessTokenTrait {
        __toString as AccessTokenTraitToString;
    }
    use ExpiryDateTimeTrait;
    use TokenEntityTrait;

    protected $_accessible = [
        'oauth_token' => true,
        'expires' => true,
        'client_id' => true,
        'user_id' => true,
        'scopes' => true,
        'revoked' => false,
    ];

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->oauth_token;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        $this->oauth_token = $identifier;
    }

    /**
     * workaround method
     *
     * Fatal error: Declaration of League\OAuth2\Server\Entities\Traits\AccessTokenTrait::__toString() must be compatible with Cake\ORM\Entity::__toString(): string in
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->AccessTokenTraitToString();
    }
}
