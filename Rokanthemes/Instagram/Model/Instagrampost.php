<?php

namespace Rokanthemes\Instagram\Model;

class Instagrampost extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'rokanthemes_instagram_post';

	protected $_cacheTag = 'rokanthemes_instagram_post';

	protected $_eventPrefix = 'rokanthemes_instagram_post';

	protected function _construct()
	{
		$this->_init('Rokanthemes\Instagram\Model\ResourceModel\Instagrampost');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}