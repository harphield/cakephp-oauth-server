<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use OAuthServer\Model\Entity\Scope;

/**
 * Scope Model
 *
 * @property \Cake\ORM\Association\HasMany|\OAuthServer\Model\Table\AccessTokenScopesTable $AccessTokenScopes
 * @property \OAuthServer\Model\Table\HasMany|\OAuthServer\Model\Table\AuthCodeScopesTable $AuthCodeScopes
 * @method \OAuthServer\Model\Entity\Scope get($primaryKey, $options = [])
 * @method \OAuthServer\Model\Entity\Scope newEntity($data = null, array $options = [])
 * @method \OAuthServer\Model\Entity\Scope[] newEntities(array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\Scope|bool save(\OAuthServer\Model\Table\EntityInterface $entity, $options = [])
 * @method \OAuthServer\Model\Entity\Scope patchEntity(\OAuthServer\Model\Table\EntityInterface $entity, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\Scope[] patchEntities($entities, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\Scope findOrCreate($search, callable $callback = null, $options = [])
 */
class OauthScopesTable extends Table
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('oauth_scopes');
        $this->setPrimaryKey('id');
        $this->setEntityClass(Scope::class);

        $this->hasMany('AccessTokenScopes', [
            'className' => 'OAuthServer.AccessTokenScopes',
        ]);
        $this->hasMany('AuthCodeScopes', [
            'className' => 'OAuthServer.AuthCodeScopes',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->maxLength('id', 40)
            ->requirePresence('id', 'create');
        $validator
            ->maxLength('description', 200)
            ->allowEmptyString('description');

        return $validator;
    }
}
