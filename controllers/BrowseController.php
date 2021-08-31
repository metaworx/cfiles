<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\cfiles\controllers;

use humhub\modules\cfiles\models\File;
use humhub\modules\cfiles\models\Folder;
use humhub\modules\cfiles\models\rows\FileRow;
use humhub\modules\cfiles\widgets\FileSystemItem;
use Yii;
use humhub\modules\cfiles\widgets\FileList;
use yii\web\HttpException;

/**
 * Description of BrowseController
 *
 * @author luke, Sebastian Stumpf
 */
class BrowseController extends BaseController
{
    public function actionIndex()
    {
        $currentFolder = $this->getCurrentFolder();
        if(!$currentFolder->content->canView()) {
            throw new HttpException(403);
        }

        return $this->render('index', [
                    'contentContainer' => $this->contentContainer,
                    'folder' => $currentFolder,
                    'canWrite' => $this->canWrite()
        ]);
    }

    public function actionFileList()
    {
        return $this->asJson(['output' => $this->renderFileList()]);
    }

    /**
     * Returns rendered file list.
     *
     * @param boolean $withItemCount true -> also calculate and return the item count.
     * @param array $filesOrder orderBy array appended to the files query
     * @param array $foldersOrder orderBy array appended to the folders query
     * @return array|string the rendered view or an array of the rendered view and the itemCount.
     */
    public function renderFileList($filesOrder = null, $foldersOrder = null)
    {
        return FileList::widget([
                    'folder' => $this->getCurrentFolder(),
                    'contentContainer' => $this->contentContainer,
                    'filesOrder' => $filesOrder,
                    'foldersOrder' => $foldersOrder
        ]);
    }

    public function actionLoadEntry()
    {
        $fileId = Yii::$app->request->get('id');
        $fileId = strpos($fileId, 'file_') === 0 ? substr($fileId, 5) : 0;

        if ($fileId && ($file = File::findOne(['id' => $fileId]))) {
            return $this->asJson(['output' => $this->renderFileRow($file)]);
        }

        return $this->asJson([
            'success' => false,
            'error' => Yii::t('CfilesModule.base', 'No file found!')
        ]);
    }

    private function renderFileRow(File $file)
    {
        if ($file->parent_folder_id) {
            $folder = Folder::findOne(['id' => $file->parent_folder_id]);
        } else {
            $folder = $this->getRootFolder();
        }

        return FileSystemItem::widget([
            'folder' => $folder,
            'row' => new FileRow(['item' => $file]),
        ]);
    }

}
