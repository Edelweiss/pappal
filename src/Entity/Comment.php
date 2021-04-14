<?php

namespace App\Entity;

use App\Repository\CommentRepository;

class Comment {
    private $id;
    private $postingDate;
    private $post;
    private $sample;
    private $user;

    public function getId() {
        return $this->id;
    }

    public function setPostingDate($postingDate) {
        $this->postingDate = $postingDate;
    }

    public function getPostingDate() {
        return $this->postingDate;
    }

    public function setPost($post) {
        $this->post = $post;
    }

    public function getPost() {
        return $this->post;
    }

    public function setSample(\App\Entity\Sample $sample) {
        $this->sample = $sample;
    }

    public function getSample() {
        return $this->sample;
    }

    public function setUser(\App\Entity\User $user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }
}