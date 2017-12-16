<?php

namespace Rokka\Client\Core;

/**
 * Represents the relation of a user to an organization.
 */
class Membership
{
    const ROLE_ADMIN = 'admin';

    const ROLE_WRITE = 'write';

    const ROLE_READ = 'read';

    /**
     * UUID v4 of user.
     *
     * @var string
     */
    public $userId;

    /**
     * UUID v4 of organization.
     *
     * @var string
     */
    public $organizationId;

    /**
     * Role.
     *
     * @var string
     */
    public $role;

    /**
     * Active.
     *
     * @var bool
     */
    public $active;

    /**
     * Constructor.
     *
     * @param string $userId         User id
     * @param string $organizationId Organization id
     * @param string $role           Role
     * @param bool   $active         If it is active
     */
    public function __construct($userId, $organizationId, $role, $active)
    {
        $this->userId = $userId;
        $this->organizationId = $organizationId;
        $this->role = $role;
        $this->active = $active;
    }

    /**
     * Create a user from the JSON data returned by the rokka.io API.
     *
     * @param string $jsonString JSON as a string
     *
     * @return Membership
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);

        return new self($data['user_id'], $data['organization_id'], $data['role'], $data['active']);
    }
}
