<?php
namespace Uppu4\Helper;

use Uppu4\Entity\Comment;

class CommentHelper
{
    private $em;
    public $comment;

    function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function createComment($comment, Comment $parent = null, \Uppu4\Entity\File $file, \Uppu4\Entity\User $user ) {
        $this->comment =new Comment;
        $this->comment->setUser($user);
        $this->comment->setComment($comment);
        $this->comment->setParent($parent);
        $this->comment->setFileId($file);
        $this->comment->setPosted();
}
    public function commentSave() {
        $this->em->persist($this->comment);
        $this->em->flush();
    }
}