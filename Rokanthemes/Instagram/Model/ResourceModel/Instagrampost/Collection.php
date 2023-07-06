<?php
namespace Rokanthemes\Instagram\Model\ResourceModel\Instagrampost;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'rokanthemes_instagram_post_collection';
	protected $_eventObject = 'instagram_post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Rokanthemes\Instagram\Model\Instagrampost', 'Rokanthemes\Instagram\Model\ResourceModel\Instagrampost');
	}

}