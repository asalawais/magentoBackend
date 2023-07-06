
# Image Resize and Crop for Magento 2

Examples:
  1.  $this->helper('Twl\AdaptiveResize\Helper\Image')->init($_product)->adaptiveResize(240,300);
  2.  $this->helper('Twl\AdaptiveResize\Helper\Image')->init($_product,'category_page_grid')->adaptiveResize(240,300);
  3.  $this->helper('Twl\AdaptiveResize\Helper\Image')->init($_product,'category_page_grid')->setCropPosition('top')->adaptiveResize(240);

You can use following parameters with setCropPosition function:
  - top
  - top-left
  - top-right
  - bottom
  - bottom-left
  - bottom-right
  - center
  - center-left
  - center-right

