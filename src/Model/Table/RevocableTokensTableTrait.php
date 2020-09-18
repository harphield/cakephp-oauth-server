<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\I18n\FrozenTime;
use Cake\ORM\Query;

trait RevocableTokensTableTrait
{
    /**
     * find expired token
     *
     * @param \Cake\ORM\Query $query the query
     * @return \Cake\ORM\Query
     */
    public function findExpired(Query $query): Query
    {
        return $query->where([
            $this->aliasField('expires <') => FrozenTime::now()->getTimestamp(),
        ]);
    }

    /**
     * find revoked token
     *
     * @param \Cake\ORM\Query $query the query
     * @return \Cake\ORM\Query
     */
    public function findRevoked(Query $query): Query
    {
        return $query->where([$this->aliasField('revoked') => true]);
    }
}
