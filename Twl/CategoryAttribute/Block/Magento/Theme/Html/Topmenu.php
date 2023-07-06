<?php

namespace Twl\CategoryAttribute\Block\Magento\Theme\Html;

use Magento\Backend\Model\Menu;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
// use Magento\Catalog\Model\CategoryFactory;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{

  /**
   * Get top menu html
   *
   * @param string $outermostClass
   * @param string $childrenWrapClass
   * @param int $limit
   * @return string
   */
  public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
  {
      $this->_eventManager->dispatch(
          'page_block_html_topmenu_gethtml_before',
          ['menu' => $this->getMenu(), 'block' => $this, 'request' => $this->getRequest()]
      );

      $this->getMenu()->setOutermostClass($outermostClass);
      $this->getMenu()->setChildrenWrapClass($childrenWrapClass);
      // $menu = $this->_getHtml(
      //     $this->getMenu(),
      //     $childrenWrapClass,
      //     $limit
      // );
      //
      $menuTree = $this->getMenu();
      $children = $menuTree->getChildren();
      $childLevel = $this->getChildLevel($menuTree->getLevel());
      $html = "";
      $counter = 0;
      $childrenCount = $children->count();
      $parentPositionClass = $menuTree->getPositionClass();
      $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
      // echo '<pre>';
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


      // var_dump($categoryData->getData('cms_block_list'));
      foreach ($children as $child) {
        $subMenu = "";
        $cateId = str_replace("category-node-", "", $child->getId());

        $categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory')->create();
        $categoryData = $categoryFactory->load($cateId);
        $cms_block_list = $categoryData->getData('cms_block_list');
        if ($cms_block_list) {
          $staticBlock = $objectManager->get('Magento\Cms\Block\BlockFactory')->create();
          $staticBlock->setBlockId($cms_block_list);
          $subMenu = '<div class="sub-menu '.$child->getId().'">'.$staticBlock->toHtml().'</div>';
          // $subMenu = $staticBlock->toHtml();
        }

        // echo '<br>';
        // var_dump($child->getName().'=='.$cateId.'=='.$cms_block_list);
          $child->setLevel($childLevel);
          $child->setIsFirst($counter === 1);
          $child->setIsLast($counter === $childrenCount);
          $child->setPositionClass($itemPositionClassPrefix . $counter);

          $outermostClassCode = '';
          $outermostClass = $menuTree->getOutermostClass();
          //
          if ($childLevel === 0 && $outermostClass) {
              $outermostClassCode = ' class="' . $outermostClass . '" ';
              $this->setCurrentClass($child, $outermostClass);
          }
          //
          // if ($this->shouldAddNewColumn($colBrakes, $counter)) {
          //     $html .= '</ul></li><li class="column"><ul>';
          // }
          //
          // $this->_addSubMenu(
          //     $child,
          //     $childLevel,
          //     $childrenWrapClass,
          //     $limit
          // )
		  $itemId = $child->getName();
		  $itemId = strtolower($itemId);
		  $itemId = str_replace(' ','_',$itemId);
          $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . ' id="nav-id-'.$child->getId().'">';
          $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
              $child->getName()
          ) . '</span></a>' . $subMenu . '</li>';
          $counter++;
      }

      // var_dump($children);
      // exit;
      $transportObject = new DataObject(
          [
              'html' => $this->_getHtml(
                  $this->getMenu(),
                  $childrenWrapClass,
                  $limit
              )
          ]
      );

      $this->_eventManager->dispatch(
          'page_block_html_topmenu_gethtml_after',
          ['menu' => $this->getMenu(), 'transportObject' => $transportObject]
      );

      return $html;
  }


}
