<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Models\User;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware backend user model represents a single backend user.
 *
 * The Shopware backend user model represents a row of the s_core_auth table.
 * The user model data set from the Shopware\Models\User\Repository.
 * One user has the follows associations:
 * <code>
 *   - Role        =>  Shopware\Models\User\Role      [n:1] [s_core_auth_roles]
 * </code>
 * The s_core_auth table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Table(name="s_core_auth")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks
 */
class User extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $roleId
     *
     * @ORM\Column(name="roleID", type="integer", nullable=false)
     */
    private $roleId;

    /**
     * @var integer $localeId
     *
     * @ORM\Column(name="localeID", type="integer", nullable=false)
     */
    private $localeId;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var string $encoder
     *
     * @ORM\Column(name="encoder", type="string", length=255, nullable=false)
     */

    private $encoder;

    /**
     * @var string $apiKey
     *
     * @ORM\Column(name="apiKey", type="string", length=40, nullable=true)
     */
    private $apiKey;

    /**
     * @var string $sessionId
     *
     * @ORM\Column(name="sessionID", type="string", length=50, nullable=false)
     */
    private $sessionId = '';

    /**
     * @var \DateTime $lastLogin
     *
     * @ORM\Column(name="lastlogin", type="datetime", nullable=false)
     */
    private $lastLogin;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=120, nullable=false)
     */
    private $email = '';

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active = 1;

    /**
     * @var integer $failedLogins
     *
     * @ORM\Column(name="failedlogins", type="integer", nullable=false)
     */
    private $failedLogins = 0;

    /**
     * @var \DateTime $lockedUntil
     *
     * @ORM\Column(name="lockeduntil", type="datetime", nullable=false)
     */
    private $lockedUntil;

    /**
     * @var boolean $extendedEditor
     *
     * @ORM\Column(name="extended_editor", type="boolean", nullable=false)
     */
    private $extendedEditor = false;

    /**
     * @var boolean $disabledCache
     *
     * @ORM\Column(name="disabled_cache", type="boolean", nullable=false)
     */
    private $disabledCache = false;

    /**
     * The role property is the owning side of the association between user and role.
     * The association is joined over the s_core_auth_roles.id field and the s_core_auth.roleID
     *
     * @var $role \Shopware\Models\User\Role
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Role", inversedBy="users")
     * @ORM\JoinColumn(name="roleID", referencedColumnName="id")
     */
    private $role;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\User", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\User
     */
    protected $attribute;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Blog", mappedBy="author")
     * @ORM\JoinColumn(name="id", referencedColumnName="author_id")
     * @var \Shopware\Models\Blog\Blog
     */
    protected $blog;

    /**
     * Initial the date fields
     */
    public function __construct()
    {
        $this->lastLogin = new \DateTime();
        $this->lockedUntil = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set API-Key
     *
     * @param string $apiKey
     * @return User
     */
    public function setApiKey($apiKey)
    {
        if (empty($apiKey)) {
            $apiKey = null;
        }

        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get API-Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set sessionid
     *
     * @param string $sessionId
     * @return User
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime|string $lastLogin
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        if (!$lastLogin instanceof \DateTime) {
            $lastLogin = new \DateTime((string) $lastLogin);
        }
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * Get lastlogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set failedLogins
     *
     * @param integer $failedLogins
     * @return User
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failedLogins = $failedLogins;
        return $this;
    }

    /**
     * Get failedLogins
     *
     * @return integer
     */
    public function getFailedLogins()
    {
        return $this->failedLogins;
    }

    /**
     * Set lockedUntil
     *
     * @param \DateTime|string $lockedUntil|
     * @return User
     */
    public function setLockedUntil($lockedUntil)
    {
        if (!$lockedUntil instanceof \DateTime) {
            $lockedUntil = new \DateTime((string) $lockedUntil);
        }
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    /**
     * Get lockedUntil
     *
     * @return \DateTime
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * @param boolean $extendedEditor
     * @return User
     */
    public function setExtendedEditor($extendedEditor)
    {
        $this->extendedEditor = (bool) $extendedEditor;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getExtendedEditor()
    {
        return $this->extendedEditor;
    }

    /**
     * @param boolean $disabledCache
     * @return User
     */
    public function setDisabledCache($disabledCache)
    {
        $this->disabledCache = (bool) $disabledCache;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisabledCache()
    {
        return $this->disabledCache;
    }

    /**
     * Getter function for the roleId property
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Setter function for the roleId property
     * @param int $roleId
     * @return \Shopware\Models\User\User
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }

    /**
     * Getter function for the localeId property.
     * @return int
     */
    public function getLocaleId()
    {
        return $this->localeId;
    }

    /**
     * Setter function for the localeId property.
     * @param int $localeId
     * @return \Shopware\Models\User\User
     */
    public function setLocaleId($localeId)
    {
        $this->localeId = $localeId;
        return $this;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Role model which
     * contains all data about the assigned acl role. The association is defined over
     * the User.role property (OWNING SIDE) and the Role.users (INVERSE SIDE) property.
     * The role data is joined over the s_core_auth.roleID field.
     *
     * @return \Shopware\Models\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Setter function for the role association property which is an instance of the Shopware\Models\User\Role model which
     * contains all data about the assigned acl role. The association is defined over
     * the User.role property (OWNING SIDE) and the Role.users (INVERSE SIDE) property.
     * The role data is joined over the s_core_auth.roleID field.
     *
     * @param \Shopware\Models\User\Role $role
     * @return \Shopware\Models\User\User
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return \Shopware\Models\Attribute\User
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\User|array|null $attribute
     * @return \Shopware\Models\Attribute\User
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\User', 'attribute', 'user');
    }

    /**
     * @param string $encoder
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return string
     */
    public function getEncoder()
    {
        return $this->encoder;
    }
}
