<?php

namespace Yoda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 *
 * @ORM\Table(name="yoda_user")
 * @ORM\Entity(repositoryClass="Yoda\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="username", message="This username is already taken!")
 */
class User implements AdvancedUserInterface, \Serializable {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     *
     * @Assert\NotBlank(message="Please enter a name...")
     * @Assert\Length(min=3, minMessage="Enter at least 3 characters.")
     * @ORM\Column(name="username", type="string", length=255)
     *
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Please enter a password...")
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;
    
    /**
     *
     * @ORM\OneToMany(targetEntity="Yoda\EventBundle\Entity\Event", mappedBy="owner")
     */
    private $events;
    
    public function __construct() {
        $this->events = new ArrayCollection();
    }

    /**
     * @var array
     * 
     * @ORM\Column(name="roles", type="json_array")
     */
    private $roles = array();

    /**
     * @var bool 
     * 
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set id
     *
     */
    public function setId() {
        $this->id = $id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    public function getRoles() {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles) {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials() {
        
    }

    public function getSalt() {
        return null;
    }

    /**
     * @return boolean
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    // methods for AdvanceUserInterface
    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return $this->getIsActive();
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

    public function serialize() {
        return serialize(array($this->id, $this->username, $this->password));
    }

    public function unserialize($serialized) {
        list($this->id, $this->username, $this->password) = unserialize($serialized);
    }
    
    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

}
