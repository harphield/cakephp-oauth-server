<?php
declare(strict_types=1);

namespace OAuthServer\Bridge;

class GrantTypes
{
    public const CLIENT_CREDENTIALS = 'client_credentials';

    public const AUTHORIZATION_CODE = 'authorization_code';

    public const REFRESH_TOKEN = 'refresh_token';

    public const PASSWORD = 'password';

    protected static $classNameMap = [
        'ClientCredentialsGrant' => self::CLIENT_CREDENTIALS,
        'AuthCodeGrant' => self::AUTHORIZATION_CODE,
        'RefreshTokenGrant' => self::REFRESH_TOKEN,
        'PasswordGrant' => self::PASSWORD,
    ];

    /**
     * Get implemented and allowed grant types
     *
     * @return array
     */
    public static function getAllowedGrantTypes(): array
    {
        return [
            self::CLIENT_CREDENTIALS,
            self::AUTHORIZATION_CODE,
            self::REFRESH_TOKEN,
            self::PASSWORD,
        ];
    }

    /**
     * get grant type from grant class name
     *
     * @param string $grantClassName eg: AuthCodeGrantGrant
     * @return string|null
     */
    public static function convertFromGrantClassName(string $grantClassName): ?string
    {
        return static::$classNameMap[$grantClassName] ?? null;
    }
}
