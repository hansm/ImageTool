<?php
namespace ImageTool\Controller;

use ImageTool\Model\Entity\Image;
use ImageTool\Model\ModelException;
use ErrorException;
use ImageTool\Log;

class MainController extends \ImageTool\AbstractController {

	public function actionMain() {
		$this->view->setViewName('Main');
	}

	public function actionUpload() {
		$this->view->setJson();
		$imageModel = $this->modelFactory->getImageModel();

		if (!$imageModel->isValidImage(isset($_FILES['image']) ? $_FILES['image'] : null)) {
			$this->view->error('Invalid image.');
			return;
		}

		if (!empty($_POST['width']) && !is_numeric($_POST['width'])
				|| !empty($_POST['height']) && !is_numeric($_POST['height'])) {
			$this->view->error('Invalid resize dimensions.');
			return;
		}

		if (!empty($_POST['x1']) && !is_numeric($_POST['x1'])
				|| !empty($_POST['x2']) && !is_numeric($_POST['x2'])
				|| !empty($_POST['y1']) && !is_numeric($_POST['y1'])
				|| !empty($_POST['y2']) && !is_numeric($_POST['y2'])) {
			$this->view->error('Invalid crop values.');
			return;
		}

		try {
			$image = new Image();
			$image->upload_time = U_TIME;
			$image->image = $imageModel->getUniqueImageName($_FILES['image']['name']);

			try {
				$uploadFilePath = ROOT . $this->settings['upload_dir'] . \DIRECTORY_SEPARATOR . $image->image;
				\move_uploaded_file($_FILES['image']['tmp_name'],
					$uploadFilePath);
			} catch (ErrorException $e) {
				Log::get()->error('Failed to upload image.', $e);
				throw new ModelException('Failed to upload image.');
			}

			if (!empty($_POST['width']) && !empty($_POST['height'])) {
				Log::get()->debug('Resizing ('. $_POST['width'] .'x'. $_POST['height'] .').');
				$imageModel->resize($uploadFilePath, (int) $_POST['width'], (int) $_POST['height']);
			}

			if (!empty($_POST['x1']) && !empty($_POST['x2'])
					&& !empty($_POST['y1']) && !empty($_POST['y1'])) {
				Log::get()->debug('Cropping (x1='. $_POST['x1'] .', y1='. $_POST['y1']
					. ', x2='. $_POST['x2'] .', y2='. $_POST['y2'] .').');
				$imageModel->crop($uploadFilePath, (int) $_POST['x1'], (int) $_POST['y1'], (int) $_POST['x2'], (int) $_POST['y2']);
			}

			$imageModel->insert($image);

			$this->set([
				'message'	=>	'Image uploaded.',
				'url'		=>	$this->settings['upload_dir_public_url'] .'/'. $image->image
			]);
		} catch (ModelException $e) {
			$this->view->error('Failed to upload image.');
		}
	}

}
