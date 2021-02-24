<?php

namespace Papyrillio\PapPalBundle\Entity;

use Papyrillio\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\PapPalBundle\Entity\Comment
 */
class Comment
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var datetime $postingDate
     */
    private $postingDate;

    /**
     * @var text $post
     */
    private $post;

    /**
     * @var Papyrillio\PapPalBundle\Entity\Sample
     */
    private $sample;

    /**
     * @var Papyrillio\UserBundle\Entity\User
     */
    private $user;


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
     * Set postingDate
     *
     * @param datetime $postingDate
     */
    public function setPostingDate($postingDate)
    {
        $this->postingDate = $postingDate;
    }

    /**
     * Get postingDate
     *
     * @return datetime 
     */
    public function getPostingDate()
    {
        return $this->postingDate;
    }

    /**
     * Set post
     *
     * @param text $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return text 
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set sample
     *
     * @param Papyrillio\PapPalBundle\Entity\Sample $sample
     */
    public function setSample(\Papyrillio\PapPalBundle\Entity\Sample $sample)
    {
        $this->sample = $sample;
    }

    /**
     * Get sample
     *
     * @return Papyrillio\PapPalBundle\Entity\Sample 
     */
    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Set user
     *
     * @param Papyrillio\UserBundle\Entity\User $user
     */
    public function setUser(\Papyrillio\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Papyrillio\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}