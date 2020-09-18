<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AuthCode Model
 *
 * @property \Cake\ORM\Association\BelongsTo|\OAuthServer\Model\Table\OauthClientsTable $OauthClients
 * @property \Cake\ORM\Association\HasMany|\OAuthServer\Model\Table\AuthCodeScopesTable $AuthCodeScopes
 * @property \OAuthServer\Model\Table\BelongsToMany|\OAuthServer\Model\Table\OauthScopesTable $OauthScopes
 * @method \OAuthServer\Model\Table\AuthCode get($primaryKey, $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode newEntity($data = null, array $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode[] newEntities(array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode|bool save(\OAuthServer\Model\Table\EntityInterface $entity, $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode patchEntity(\OAuthServer\Model\Table\EntityInterface $entity, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode[] patchEntities($entities, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\AuthCode findOrCreate($search, callable $callback = null, $options = [])
 */
class AuthCodesTable extends Table implements RevocableTokensTableInterface
{
    use RevocableTokensTableTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('oauth_auth_codes');
        $this->setPrimaryKey('code');

        $this->belongsTo('OauthClients', [
            'className' => 'OAuthServer.OauthClients',
            'foreignKey' => 'client_id',
            'propertyName' => 'client',
        ]);
        $this->hasMany('AuthCodeScopes', [
            'className' => 'OAuthServer.AuthCodeScopes',
            'foreignKey' => 'auth_code',
            'dependant' => true,
        ]);
        $this->belongsToMany('OauthScopes', [
            'className' => 'OAuthServer.OauthScopes',
            'foreignKey' => 'auth_code',
            'targetForeignKey' => 'scope_id',
            'joinTable' => 'oauth_auth_code_scopes',
            'propertyName' => 'scopes',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->maxLength('code', 40);
        $validator->boolean('revoked');

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['code']));
        $rules->addCreate($rules->existsIn('client_id', 'OauthClients'));

        return $rules;
    }
}
