<?php

namespace Rokka\Client;

use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Core\Membership;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\User as UserModel;

/**
 * User management client for the rokka.io service.
 */
class User extends Base
{
    const USER_RESOURCE = 'users';
    const ORGANIZATION_RESOURCE = 'organizations';

    /**
     * Create a user.
     *
     * @param string $email Email
     *
     * @return UserModel
     *
     * @throws GuzzleException
     */
    public function createUser($email)
    {
        $contents = $this
            ->call('POST', self::USER_RESOURCE, ['json' => [
                'email' => $email,
            ]], false)
            ->getBody()
            ->getContents()
        ;

        return UserModel::createFromJsonResponse($contents);
    }

    /**
     * Create an organization.
     *
     * @param string $name        Organization name
     * @param string $billingMail Billing mail
     * @param string $displayName Optional display name
     *
     * @return Organization
     *
     * @throws GuzzleException
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
     * Return an organization.
     *
     * @param string $name Organization name
     *
     * @return Organization
     *
     * @throws GuzzleException
     */
    public function getOrganization($name)
    {
        $contents = $this
            ->call('GET', self::ORGANIZATION_RESOURCE.'/'.$name)
            ->getBody()
            ->getContents()
        ;

        return Organization::createFromJsonResponse($contents);
    }

    /**
     * Create a membership.
     *
     * @param string $organization Organization
     * @param string $email        Email
     * @param string $role         Role to add
     *
     * @return Membership
     *
     * @throws GuzzleException
     */
    public function createMembership($organization, $email, $role = Membership::ROLE_READ)
    {
        $contents = $this
            ->call('PUT', implode('/', [self::ORGANIZATION_RESOURCE, $organization, 'memberships', $email]), ['json' => [
                'role' => strtolower($role),
            ]])
            ->getBody()
            ->getContents()
        ;

        return Membership::createFromJsonResponse($contents);
    }

    /**
     * Get the membership metadata for the given organization and user's email.
     *
     * @param string $organization Organization
     * @param string $email        Email
     *
     * @return Membership
     *
     * @throws GuzzleException
     */
    public function getMembership($organization, $email)
    {
        $contents = $this
            ->call('GET', implode('/', [self::ORGANIZATION_RESOURCE, $organization, 'memberships', $email]))
            ->getBody()
            ->getContents()
        ;

        return Membership::createFromJsonResponse($contents);
    }
}
