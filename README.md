# Magento 1.9 - S3 Product Image Task

Here at Blackstone we keep our book cover images on amazons s3 service and deliver them to various places on the web using a cloudfront url. The images are publicly accessable via a url generated from the products sku. For example the book 'Matterhorn' which has a product sku of '5751' has its book cover located at 'http://d1exhaoem38lup.cloudfront.net/5/7/5751/5751-square-400.jpg'.

You'll notice that the image path is the cloudfront base url, followed by the first character of the sku, then a slash, then the second character of the sku, then a slash, then the sku again, then another slash, then the sku and '-square-400.jpg'. Because we sync our images to the s3, we don't use magentos built in product image uploader and instead get the image urls based on the product sku, and display them in various places on the website using that generated url.

What we would like from you, is to display the matterhorn cover image in place of the default image on the product page ('http://{{your-install}}/catalog/product/view/id/1') as well as in the shopping cart when the book is added.

You'll find a copy of the database to match the repo sitting in the root as 'image-task.sql' please use that along with the code from the repo to complete the task

--db credentials (as set in htdocs/app/etc/local.xml)--
user: root
pwd:

--magento backend credentials--
username: admin
pwd: task123
email: tasks@blackstoneaudio.com

--magento encryption key--
2fbe5c29f95c0606712bded10e8034f5