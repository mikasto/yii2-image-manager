<?php

namespace mikasto\imagemanager\models;

use mikasto\imagemanager\Module;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "ImageManager".
 *
 * @property integer $id
 * @property string $tag
 * @property string $fileName
 * @property string $fileHash
 * @property string $created
 * @property string $modified
 * @property string $createdBy
 * @property string $modifiedBy
 */
class ImageManager extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%image_manager}}';
    }

    /**
     * Get the DB component that the model uses
     * This function will throw error if object could not be found
     * The DB connection defaults to DB
     * @return null|object
     */
    public static function getDb()
    {
        // Get the image manager object
        $oImageManager = Yii::$app->get('imagemanager', false);

        if ($oImageManager === null) {
            // The image manager object has not been set
            // The normal DB object will be returned, error will be thrown if not found
            return Yii::$app->get('db');
        }

        // The image manager component has been loaded, the DB component that has been entered will be loaded
        // By default this is the Yii::$app->db connection, the user can specify any other connection if needed
        return Yii::$app->get($oImageManager->databaseComponent);
    }

    /**
     * Set Created date to now
     */
    public function behaviors()
    {
        $aBehaviors = [];

        // Add the time stamp behavior
        $aBehaviors[] = [
            'class' => TimestampBehavior::className(),
            'createdAtAttribute' => 'created',
            'updatedAtAttribute' => 'modified',
            'value' => new Expression('NOW()'),
        ];

        // Get the imagemanager module from the application
        $moduleImageManager = Yii::$app->getModule('imagemanager');
        /* @var $moduleImageManager Module */
        if ($moduleImageManager !== null) {
            // Module has been loaded
            if ($moduleImageManager->setBlameableBehavior) {
                // Module has blame able behavior
                $aBehaviors[] = [
                    'class' => BlameableBehavior::className(),
                    'createdByAttribute' => 'createdBy',
                    'updatedByAttribute' => 'modifiedBy',
                ];
            }
        }

        return $aBehaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fileName', 'fileHash'], 'required'],
            [['created', 'modified', 'tag'], 'safe'],
            [['fileName'], 'string', 'max' => 128],
            [['fileHash'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('imagemanager', 'ID'),
            'tag' => Yii::t('imagemanager', 'Tag'),
            'fileName' => Yii::t('imagemanager', 'File Name'),
            'fileHash' => Yii::t('imagemanager', 'File Hash'),
            'created' => Yii::t('imagemanager', 'Created'),
            'modified' => Yii::t('imagemanager', 'Modified'),
            'createdBy' => Yii::t('imagemanager', 'Created by'),
            'modifiedBy' => Yii::t('imagemanager', 'Modified by'),
        ];
    }

    public function afterDelete()
    {
        parent::afterDelete();

        // Check if file exists
        if (file_exists($this->getImagePathPrivate())) {
            unlink($this->getImagePathPrivate());
        }
    }

    /**
     * Get image path private
     * @return string|null If image file exists the path to the image, if file does not exists null
     */
    public function getImagePathPrivate()
    {
        //set media path
        $sMediaPath = \Yii::$app->imagemanager->mediaPath;
        $sFileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        //get image file path
        $sImageFilePath = $this->getImageSavePath();
        if (file_exists($sImageFilePath)) {
            return $sImageFilePath;
        }
        // support previous version
        $sImageFilePathOld = $sMediaPath . DIRECTORY_SEPARATOR
            . $this->id . '_' . $this->fileHash . '.' . $sFileExtension;
        if (file_exists($sImageFilePathOld)) {
            if (rename($sImageFilePathOld, $sImageFilePath)) {  // to convert files automatically
                return $sImageFilePath;
            }
            return $sImageFilePathOld;
        }
        return null;
    }

    public function getImageSavePath()
    {
        $sMediaPath = \Yii::$app->imagemanager->mediaPath;
        $sFileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $fullPath = $sMediaPath . DIRECTORY_SEPARATOR
            . (empty($this->tag) ? '' : $this->tag);
        if (!file_exists($fullPath)) {
            mkdir($fullPath);
        }
        return $fullPath . DIRECTORY_SEPARATOR . $this->id . '_' . $this->fileHash . '.' . $sFileExtension;
    }

    /**
     * Get image data dimension/size
     * @return array The image sizes
     */
    public function getImageDetails()
    {
        //set default return
        $return = ['width' => 0, 'height' => 0, 'size' => 0];
        //set media path
        $sMediaPath = \Yii::$app->imagemanager->mediaPath;
        $sFileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        //get image file path
        $sImageFilePath = $this->getImagePathPrivate();
        //check file exists
        if (!is_null($sImageFilePath) && file_exists($sImageFilePath)) {
            $aImageDimension = getimagesize($sImageFilePath);
            $return['width'] = isset($aImageDimension[0]) ? $aImageDimension[0] : 0;
            $return['height'] = isset($aImageDimension[1]) ? $aImageDimension[1] : 0;
            $return['size'] = Yii::$app->formatter->asShortSize(filesize($sImageFilePath), 2);
        }
        return $return;
    }

}
