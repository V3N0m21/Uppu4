<?php
namespace Uppu4\Helper;

use Uppu4\Entity\File;

class FileHelper
{

    private $em;

    private $maxSize = 10485760;
    private $pictures = array('image/jpeg', 'image/gif', 'image/png');
    public $errors;

    function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    public function fileValidate($data) {
        if (($data['size'] >= $this->maxSize) || ($data['size'] == 0)) {
            $this->errors[] = 'Файл должен быть до 10мб.';
        }
    }

    public function fileSave($data, \Uppu4\Entity\User $user) {
        $fileResource = new File;
        $fileResource->setName($data['name']);
        $fileResource->setSize($data['size']);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $fileResource->setExtension($finfo->file($data['tmp_name']));
        $fileResource->setComment($_POST['comment']);
        $mediainfo = \Uppu4\Entity\MediaInfo::getMediaInfo($data['tmp_name']);
        $fileResource->setMediainfo($mediainfo);
        $fileResource->setUploaded();
        $fileResource->setUploadedBy($user);
        $this->em->persist($fileResource);
        $this->em->flush();
        $id = $fileResource->getId();
        $tmpFile = $data['tmp_name'];
        $newFile = \Uppu4\Helper\FormatHelper::formatUploadLink($id, $data['name']);
        move_uploaded_file($tmpFile, $newFile);

        if (in_array($fileResource->getExtension(), $this->pictures)) {
            $path = \Uppu4\Helper\FormatHelper::formatUploadResizeLink($id, $data['name']);
            $resize = new \Uppu4\Helper\Resize;
            $resize->resizeFile($newFile, $path);
        }
        return $fileResource;
    }

    public function fileDelete($id) {
        $file = $this->em->getRepository('Uppu4\Entity\File')->findOneById($id);
        $filePath = \Uppu4\Helper\FormatHelper::formatUploadLink($file->getId(), $file->getName());
        if (in_array($file->getExtension(), $this->pictures)) {
            $fileResizePath = \Uppu4\Helper\FormatHelper::formatUploadResizeLink($file->getId(), $file->getName());
            unlink($fileResizePath);
        }
        unlink($filePath);
        $this->em->remove($file);
        $this->em->flush();
    }
}