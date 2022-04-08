<?php
declare(strict_types=1);

namespace OAuthServer\Bridge\Repository;

use Cake\Datasource\ModelAwareTrait;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

/**
 * implemented ClientRepositoryInterface
 */
class ClientRepository implements ClientRepositoryInterface
{
    use ModelAwareTrait;

    /**
     * @var \OAuthServer\Model\Table\OauthClientsTable
     */
    private $table;

    /**
     * RefreshTokenRepository constructor.
     */
    public function __construct()
    {
        $this->table = $this->loadModel('OAuthServer.OauthClients');
    }

    /**
     * @inheritDoc
     */
    public function getClientEntity($clientIdentifier)
    {
        return $this->table->find()->where([$this->table->getPrimaryKey() => $clientIdentifier])->first();
    }

    /**
     * @inheritDoc
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $conditions = [
            $this->table->getPrimaryKey() => $clientIdentifier,
        ];
        if ($clientSecret !== null) {
            $conditions[$this->table->aliasField('client_secret')] = $clientSecret;
        }

        $client = $this->table->find()->where($conditions)->first();
        /** @var \OAuthServer\Bridge\Repository\Client|null $client */

        if ($client === null) {
            return false;
        }

        if ($grantType === null) {
            return true;
        }

        return in_array($grantType, $client->grant_types, true);
    }
}
