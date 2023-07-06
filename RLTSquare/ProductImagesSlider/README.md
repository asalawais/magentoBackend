# ProductImagesSlider-Magento2

# Overview

One of the most important factors to build a successful Magento store, is the owner's finesse in taking care of the customers shopping experience. It is essential to make store browsing as easy as possible without much distraction. 
Product Images slider is an extension for Magento that allows user to view all the attached images of products. This extension helps in viewing products by viewing the products in a pop-up slider. Also, this extension enables the store owner to show two to three product images on product page image slider at the same time.
It is guaranteed to improve customer's effective engagement with your online store because of the fact that it allows multiple pictures to be displayed on the product page which grabs the attention of the customer and increase the chance for sale. 

Here are some of the salient features for the extension:

```
1. Enable/disable option available for this extension.
2. Allow users to view products in a pop-up slider.
3. Can also show multiple images on the pop-up slider.
4. Helps in having a more clear view of the product.
5. Zooms the image when the scroll is placed on the image.
6. Show multiple images on the product page image slider.
7. Multiple breakpoints are set up for different screen sizes to enhance the responsiveness of the product image slider.
```

## Installation

### Magento® Marketplace

This extension will also be available on the Magento® Marketplace when approved.

### Manually

1. Go to Magento® 2 root folder

2. Require/Download this extension:

   Enter following commands to install extension.

   ```
   composer require rltsquare/product-images-slider
   ```

   Wait while composer is updated.
   
   #### OR
   
   You can also download code from this repo under Magento® 2 following directory:
    
    ```
    app/code/RLTSquare/ProductImagesSlider
    ```    

3. Enter following commands to enable the module:

   ```
   php bin/magento module:enable RLTSquare_ProductImagesSlider
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   php bin/magento cache:flush
   ```

4. If Magento® is running in production mode, deploy static content: 

   ```
   php bin/magento setup:static-content:deploy
   ```


## Requirements

1. This Magento® extension works on Magento 2.2 and 2.3 versions. Tested on versions 2.3.0 and above.

2. Tested on different themes specifically Ultimo, Porto and certain custom themes

For details, read our blog:
https://www.rltsquare.com/blog/product-images-slider-magento-2-extension/
