<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RefreshToken Model
 *
 * @property \OAuthServer\Model\Table\BelongsTo|\OAuthServer\Model\Table\AccessTokensTable $AccessTokens
 * @method \OAuthServer\Model\Table\RefreshToken get($primaryKey, $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken newEntity($data = null, array $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken[] newEntities(array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken|bool save(\OAuthServer\Model\Table\EntityInterface $entity, $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken patchEntity(\OAuthServer\Model\Table\EntityInterface $entity, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken[] patchEntities($entities, array $data, array $options = [])
 * @method \OAuthServer\Model\Entity\RefreshToken findOrCreate($search, callable $callback = null, $options = [])
 */
class RefreshTokensTable extends Table implements RevocableTokensTableInterface
{
    use RevocableTokensTableTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('oauth_refresh_tokens');
        $this->setPrimaryKey('refresh_token');

        $this->belongsTo('AccessTokens', [
            'className' => 'OAuthServer.AccessTokens',
            'foreignKey' => 'oauth_token',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->maxLength('refresh_token', 40);
        $validator->boolean('revoked');

        return $validator;
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules the rules
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['refresh_token']));

        return $rules;
    }
}
