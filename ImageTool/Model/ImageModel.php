<?php
namespace ImageTool\Model;

use ImageTool\Model\Entity\Image;
use PDO;
use PDOException;
use Exception;
use ImageTool\Log;
use ImageTool\RandomStringGenerator;
use abeautifulsite\SimpleImage;

class ImageModel extends AbstractModel {

	const ENTITY_CLASS = '\\ImageTool\\Model\\Entity\\Image';
	const IMAGE_NAME_LENGTH = 16;

	private static $validImageTypes = ['png', 'jpg', 'jpeg'];

	/**
	 * @param string $name
	 * @return \ImageTool\Model\Entity\Image
	 * @throws \ImageTool\Model\ModelException
	 */
	public function getImage($name) {
		try {
			$query = $this->resources->getDb()->prepare('SELECT * FROM image WHERE image = ?');
			$query->execute([$name]);
			$image = $query->fetchObject(self::ENTITY_CLASS);
			$query->closeCursor();
			return $image ? $image : null;
		} catch (PDOException $e) {
			Log::get()->error('Failed to get image.', $e);
			throw new ModelException('Failed to get image.', $e);
		}
	}

	/**
	 * Insert image into database
	 *
	 * @param \ImageTool\Model\Entity\Image $image
	 * @throws \ImageTool\Model\DbException
	 */
	public function insert(Image $image) {
		try {
			$query = $this->resources->getDb()->prepare(
				'INSERT INTO image (image, upload_time) VALUES (?, ?)');
			$query->bindValue(1, $image->image, PDO::PARAM_STR);
			$query->bindValue(2, $image->upload_time, PDO::PARAM_INT);
			$query->execute();
		} catch (PDOException $e) {
			Log::get()->error('Failed to insert image.', $e);
			throw new DbException('Failed to insert image.', $e);
		}
	}

	/**
	 * @param string $imageName
	 * @return string
	 * @throws \ImageTool\Model\ModelException
	 */
	public function getUniqueImageName($imageName) {
		$fileType = $this->getFileExtention($imageName);

		$i = 0;
		do {
			if ($i > 10) {
				Log::get()->warn('Failed to generate unique file name.');
				throw new ModelException('Failed to generate unique file name.');
			}

			$imageName = RandomStringGenerator::get(self::IMAGE_NAME_LENGTH) .'.'. $fileType;
			$i++;
		} while ($this->getImage($imageName) != null);

		return $imageName;
	}

	/**
	 * Check whether image is valid
	 *
	 * @param array $file
	 * @return boolean
	 */
	public function isValidImage($file) {
		if (empty($file) || !empty($file['error'])) {
			return false;
		}

		$fileType = $this->getFileExtention($file['name']);
		return \in_array($fileType, self::$validImageTypes);
	}

	/**
	 * @param string $file
	 * @return string
	 */
	private function getFileExtention($file) {
		$parts = \explode('.', $file);
		return \strtolower(\end($parts));
	}

	/**
	 * Resize image
	 *
	 * @param string $imagePath
	 * @param int $width
	 * @param int $height
	 * @throws \ImageTool\Model\ModelException
	 */
	public function resize($imagePath, $width, $height) {
		try {
			$simpleImage = new SimpleImage($imagePath);
			$simpleImage->resize($width, $height)->save();
		} catch (Exception $e) {
			Log::get()->error('Failed to resize image.', $e);
			throw new ModelException('Failed to resize image.');
		}
	}

	/**
	 * Crop image to dimensions
	 *
	 * @param string $imagePath
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @throws \ImageTool\Model\ModelException
	 */
	public function crop($imagePath, $x1, $y1, $x2, $y2) {
		try {
			$simpleImage = new SimpleImage($imagePath);
			$simpleImage->crop($x1, $y1, $x2, $y2)->save();
		} catch (Exception $e) {
			Log::get()->error('Failed to resize image.', $e);
			throw new ModelException('Failed to resize image.');
		}
	}

}
