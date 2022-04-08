<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AccessToken Model
 *
 * @property \Cake\ORM\Association\BelongsTo|\OAuthServer\Model\Table\OauthClientsTable $OauthClients
 * @property \Cake\ORM\Association\HasMany|\OAuthServer\Model\Table\AccessTokenScopesTable $AccessTokenScopes
 * @property \OAuthServer\Model\Table\BelongsToMany|\OAuthServer\Model\Table\OauthScopesTable $OauthScopes
 * @method \OAuthServer\Model\Table\AccessToken get($primaryKey, $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken newEntity($data = null, array $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken[] newEntities(array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken|bool save(\OAuthServer\Model\Table\EntityInterface $entity, $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken patchEntity(\OAuthServer\Model\Table\EntityInterface $entity, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken[] patchEntities($entities, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AccessToken findOrCreate($search, callable $callback = null, $options = [])
 */
class AccessTokensTable extends Table implements RevocableTokensTableInterface
{
    use RevocableTokensTableTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('oauth_access_tokens');
        $this->setPrimaryKey('oauth_token');

        $this->belongsTo('OauthClients', [
            'className' => 'OAuthServer.OauthClients',
            'foreignKey' => 'client_id',
            'propertyName' => 'client',
        ]);
        $this->hasMany('AccessTokenScopes', [
            'className' => 'OAuthServer.AccessTokenScopes',
            'foreignKey' => 'oauth_token',
            'dependant' => true,
        ]);
        $this->belongsToMany('OauthScopes', [
            'className' => 'OAuthServer.OauthScopes',
            'foreignKey' => 'oauth_token',
            'targetForeignKey' => 'scope_id',
            'joinTable' => 'oauth_access_token_scopes',
            'propertyName' => 'scopes',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->maxLength('oauth_token', 40);
        $validator->boolean('revoked');

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['oauth_token']));
        $rules->addCreate($rules->existsIn('client_id', 'OauthClients'));

        return $rules;
    }
}
