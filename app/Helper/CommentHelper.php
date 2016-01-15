<?php
namespace Uppu4\Helper;

use Uppu4\Entity\Comment;

class CommentHelper
{
    private $em;
    private $comment;

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
        return $this->comment;
}
    public function commentSave(Comment $comment) {
        $this->em->persist($comment);
        $this->em->flush();
    }

    public function getAllComments($fileId) {
        return $this->em->getRepository('Uppu4\Entity\Comment')
                ->findBy(array('fileId' => $fileId), array('path' => 'ASC'));
    }
}