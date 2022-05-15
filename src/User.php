<?php

namespace Rokka\Client;

use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Core\Membership;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\User as UserModel;
use Rokka\Client\Core\UserApiKey;
use Rokka\Client\Core\UserApiToken;

/**
 * User management client for the rokka.io service.
 */
class User extends Base
{
    public const USERS_RESOURCE = 'users';

    public const USER_RESOURCE = 'user';

    public const USER_API_KEYS_RESOURCE = 'user/apikeys';

    public const ORGANIZATION_RESOURCE = 'organizations';

    public const ORGANIZATION_OPTION_PROTECT_DYNAMIC_STACK = 'protect_dynamic_stack';

    /**
     * Constructor.
     *
     * @param ClientInterface $client              Client instance
     * @param string|null     $defaultOrganization
     * @param string|null     $apiKey              API key
     * @param string|null     $apiToken            API Token
     */
    public function __construct(ClientInterface $client, $defaultOrganization, $apiKey, $apiToken = null)
    {
        parent::__construct($client, $defaultOrganization);

        if (null !== $apiKey) {
            $this->setCredentials($apiKey);
        }

        if (null !== $apiToken) {
            $this->setToken($apiToken);
        }
    }

    /**
     * Create a user.
     *
     * @param string $email Email
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return UserModel
     */
    public function createUser($email)
    {
        $contents = $this
            ->call('POST', self::USERS_RESOURCE, ['json' => [
                'email' => $email,
            ]], false)
            ->getBody()
            ->getContents()
        ;

        return UserModel::createFromJsonResponse($contents);
    }

    /**
     * Get current userID.
     *
     * @since 1.7.0
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getCurrentUserId()
    {
        $contents = $this
            ->call('GET', self::USER_RESOURCE)
            ->getBody()
            ->getContents();

        $json = json_decode($contents, true);

        return $json['user_id'];
    }

    /**
     * Add an Api Key to the current user.
     *
     * @since 1.16.0
     *
     * @param string|null $comment Optional comment
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addUserApiKey(string $comment = null): UserApiKey
    {
        $contents = $this
            ->call('POST', self::USER_API_KEYS_RESOURCE, ['json' => [
                'comment' => $comment,
            ]])
            ->getBody()
            ->getContents()
        ;

        $data = json_decode($contents, true);

        return UserApiKey::createFromArray($data);
    }

    /**
     * Gets info about the currently used Api Key.
     *
     * @since 1.16.0
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCurrentUserApiKey(): UserApiKey
    {
        $contents = $this
            ->call('GET', self::USER_API_KEYS_RESOURCE.'/current')
            ->getBody()
            ->getContents();
        $data = json_decode($contents, true);

        return UserApiKey::createFromArray($data);
    }

    /**
     * Deletes an Api Key for the current user.
     *
     * @since 1.16.0
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return bool returns false, if key didn't exist, true if operation succeeded
     */
    public function deleteUserApiKey(string $id): bool
    {
        try {
            $response = $this
            ->call('DELETE', self::USER_API_KEYS_RESOURCE.'/'.$id);
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return '204' == $response->getStatusCode();
    }

    /**
     * Get current user.
     *
     * @since 1.16.0
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return UserModel
     */
    public function getCurrentUser()
    {
        $contents = $this
            ->call('GET', self::USER_RESOURCE)
            ->getBody()
            ->getContents();

        return UserModel::createFromJsonResponse($contents);
    }

    /**
     * Gets a new API JWT Token with an $apiKey.
     *
     * @since 1.17.0
     * @see  https://api.rokka.io/doc/#/admin/getUserToken
     *
     * @param string|null $apiKey     The api key, if different from the base one
     * @param array       $parameters The /user/apikeys/token query parameters
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return UserApiToken
     */
    public function getNewToken($apiKey = null, $parameters = [])
    {
        $contents = $this
            ->call('GET', implode('/', [self::USER_API_KEYS_RESOURCE, 'token']), ['query' => $parameters], true, ['key' => $apiKey])
            ->getBody()
            ->getContents();

        $data = json_decode($contents, true);

        return UserApiToken::createFromArray($data);
    }

    /**
     * Create an organization.
     *
     * @param string $name        Organization name
     * @param string $billingMail Billing mail
     * @param string $displayName Optional display name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Organization
     */
    public function createOrganization($name, $billingMail, $displayName = '')
    {
        $options = ['json' => [
            'billing_email' => $billingMail,
        ]];

        if (!empty($displayName)) {
            $options['json']['display_name'] = $displayName;
        }

        $contents = $this
            ->call('PUT', self::ORGANIZATION_RESOURCE.'/'.$name, $options)
            ->getBody()
            ->getContents()
        ;

        return Organization::createFromJsonResponse($contents);
    }

    /**
     * Create an organization.
     *
     * @since 1.13.0
     *
     * @param string $organization Organization name
     * @param string $name         Option name
     * @param string $value        Option Value
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Organization
     */
    public function setOrganizationOption($organization, $name, $value)
    {
        $options = ['json' => $value];

        $contents = $this
            ->call('PUT', self::ORGANIZATION_RESOURCE.'/'.$this->getOrganizationName($organization).'/options/'.$name, $options)
            ->getBody()
            ->getContents()
        ;

        return Organization::createFromJsonResponse($contents);
    }

    /**
     * Return an organization.
     *
     * @since 1.7.0
     *
     * @param string $organization Organization name
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Organization
     */
    public function getOrganization($organization = '')
    {
        $contents = $this
            ->call('GET', self::ORGANIZATION_RESOURCE.'/'.$this->getOrganizationName($organization))
            ->getBody()
            ->getContents()
        ;

        return Organization::createFromJsonResponse($contents);
    }

    /**
     * Create a membership.
     *
     * @since 1.7.0
     *
     * @param string       $organization Organization
     * @param string       $userId       User ID
     * @param string|array $roles        Role to add
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Membership
     */
    public function createMembership($userId, $roles = [Membership::ROLE_READ], $organization = '')
    {
        if (\is_string($roles)) {
            $roles = [$roles];
        }
        $roles = array_map(function ($role) {
            return strtolower($role);
        }, $roles);
        $contents = $this
            ->call('PUT', implode('/', [self::ORGANIZATION_RESOURCE, $this->getOrganizationName($organization), 'memberships', $userId]), ['json' => [
                'roles' => $roles,
            ]])
            ->getBody()
            ->getContents();

        // get the membership, if it already exists
        if (empty($contents)) {
            return $this->getMembership($userId, $this->getOrganizationName($organization));
        }

        return $this->getSingleMemberShipFromJsonResponse($contents);
    }

    /**
     * Create a user and membership associated to this organization.
     *
     * @since 1.7.0
     *
     * @param string       $organization Organization
     * @param string|array $roles        Role to add
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Membership
     */
    public function createUserAndMembership($roles = [Membership::ROLE_READ], $organization = '')
    {
        if (\is_string($roles)) {
            $roles = [$roles];
        }
        $roles = array_map(function ($role) {
            return strtolower($role);
        }, $roles);
        $contents = $this
            ->call('POST',
                implode('/', [self::ORGANIZATION_RESOURCE, $this->getOrganizationName($organization), 'memberships']),
                ['json' => [
                    'roles' => $roles,
            ]])
            ->getBody()
            ->getContents();

        return $this->getSingleMemberShipFromJsonResponse($contents);
    }

    /**
     * Get the membership metadata for the given organization and user's ID.
     *
     * @since 1.7.0
     *
     * @param string $organization Organization
     * @param string $userId       User ID
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Membership
     */
    public function getMembership($userId, $organization = '')
    {
        $contents = $this
            ->call('GET', implode('/', [self::ORGANIZATION_RESOURCE, $this->getOrganizationName($organization), 'memberships', $userId]))
            ->getBody()
            ->getContents();

        return $this->getSingleMemberShipFromJsonResponse($contents);
    }

    /**
     * Deletes a membership for the given organization and user's ID.
     *
     * @since 1.7.0
     *
     * @param string $organization Organization
     * @param string $userId       User ID
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function deleteMembership($userId, $organization = '')
    {
        try {
            $response = $this
            ->call('DELETE', implode('/', [self::ORGANIZATION_RESOURCE, $this->getOrganizationName($organization), 'memberships', $userId]));
        } catch (GuzzleException $e) {
            if (404 == $e->getCode()) {
                return false;
            }

            throw $e;
        }

        return '204' == $response->getStatusCode();
    }

    /**
     * List the membership metadata for the given organization.
     *
     * @since 1.7.0
     *
     * @param string $organization Organization
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     *
     * @return Membership[]
     */
    public function listMemberships($organization = '')
    {
        $contents = $this
            ->call('GET', implode('/', [self::ORGANIZATION_RESOURCE, $this->getOrganizationName($organization), 'memberships']))
            ->getBody()
            ->getContents()
        ;

        $membership = Membership::createFromJsonResponse($contents);
        if (!\is_array($membership)) {
            throw new \RuntimeException('Something went wrong, return was not array, but should be');
        }

        return $membership;
    }

    /**
     * @param string $jsonString
     *
     * @throws \RuntimeException
     *
     * @return Membership
     */
    private function getSingleMemberShipFromJsonResponse($jsonString)
    {
        $membership = Membership::createFromJsonResponse($jsonString);
        if (\is_array($membership)) {
            throw new \RuntimeException("Something went wrong, return was an array, but shouldn't be");
        }

        return $membership;
    }
}
