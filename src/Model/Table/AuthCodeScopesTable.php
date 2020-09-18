<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;

/**
 * Class AuthCodeScopesTable
 *
 * @property \Cake\ORM\Association\BelongsTo|\OAuthServer\Model\Table\AuthCodeScopesTable $AuthCodes
 * @property \Cake\ORM\Association\BelongsTo|\OAuthServer\Model\Table\OauthScopesTable $OauthScopes
 */
class AuthCodeScopesTable extends Table
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('oauth_auth_code_scopes');
        $this->setPrimaryKey(['auth_code', 'scope_id']);

        $this->belongsTo('AuthCodes', [
            'className' => 'OAuthServer.AuthCodes',
            'foreignKey' => 'auth_code',
            'propertyName' => 'code',
        ]);
        $this->belongsTo('OauthScopes', [
            'className' => 'OAuthServer.OauthScopes',
            'foreignKey' => 'scope_id',
            'propertyName' => 'scopes',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->isUnique(['auth_code', 'scope_id']);
        $rules->addCreate($rules->existsIn('auth_code', 'AuthCodes'));
        $rules->addCreate($rules->existsIn('scope_id', 'OauthScopes'));

        return $rules;
    }
}
