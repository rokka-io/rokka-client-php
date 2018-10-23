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

    const ROLE_UPLOAD = 'upload';

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
     * Roles.
     *
     * @var array
     */
    public $roles;

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
     * @param array  $roles          Roles
     * @param bool   $active         If it is active
     */
    public function __construct($userId, $organizationId, $roles, $active)
    {
        $this->userId = $userId;
        $this->organizationId = $organizationId;
        $this->roles = $roles;
        $this->active = $active;
    }

    /**
     * Create a user from the JSON data returned by the rokka.io API.
     *
     * @param string $jsonString JSON as a string
     *
     * @return Membership|Membership[]
     */
    public static function createFromJsonResponse($jsonString)
    {
        $data = json_decode($jsonString, true);
        if (\is_array($data) && isset($data['items'])) {
            return array_map(function ($membership) {
                return self::getObjectFromArray($membership);
            }, $data['items']);
        }

        return self::getObjectFromArray($data);
    }

    /**
     * @param array $data
     *
     * @return Membership
     */
    private static function getObjectFromArray($data): self
    {
        return new self($data['user_id'], $data['organization_id'], $data['roles'], $data['active']);
    }
}
