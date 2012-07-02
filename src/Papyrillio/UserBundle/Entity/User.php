<?php

namespace Papyrillio\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Papyrillio\PapPalBundle\Entity\Comment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Papyrillio\PapPalBundle\Entity\Comment", mappedBy="user")
     */
    private $comments;

    public function __construct()
    {
        parent::__construct();
        // your own logic
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
     * Add comments
     *
     * @param Papyrillio\PapPalBundle\Entity\Comment $comments
     */
    public function addComment(\Papyrillio\PapPalBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
}