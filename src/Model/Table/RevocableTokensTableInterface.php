<?php
declare(strict_types=1);

namespace OAuthServer\Model\Table;

use Cake\ORM\Query;

interface RevocableTokensTableInterface
{
    /**
     * find expired token
     *
     * @param \Cake\ORM\Query $query the query
     * @return \Cake\ORM\Query
     */
    public function findExpired(Query $query): Query;

    /**
     * find revoked token
     *
     * @param \Cake\ORM\Query $query the query
     * @return \Cake\ORM\Query
     */
    public function findRevoked(Query $query): Query;
}
