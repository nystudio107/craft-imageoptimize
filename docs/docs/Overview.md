# ImageOptimize Overview

![Screenshot](./resources/screenshots/image-optimize-field-composite2.jpg)

ImageOptimize allows you to automatically create & optimize responsive image transforms from your Craft 3 assets. It works equally well with native Craft image transforms, and image services like [imgix](https://imgix.com), [Thumbor](http://thumbor.org/), [Sharp JS](https://nystudio107.com/blog/setting-up-your-own-image-transform-service) with zero template changes.

You use the native Craft UI/UX to create your image transforms, whether in the Control Panel or via your templates. ImageOptimize takes care of the rest, optimizing all of your image transforms automatically by running a variety of image optimization tools on them.

ImageOptimize also comes with an **OptimizedImages** Field that makes creating responsive image sizes for `<img srcset="">` or `<picture>` elements sublimely easy. These responsive image transforms are created when an asset is _saved_, rather than at page load time, to ensure that frontend performance is optimal.

Because ImageOptimize has already pre-generated and saved the URLs to your optimized image variants, no additional database requests are needed to fetch this information (unlike with Assets or Transforms).

As configured by default, all of these are _lossless_ image optimizations that remove metadata and otherwise optimize the images without changing their appearance in any way.

Out of the box, ImageOptimize allows for the optimization of `JPG`, `PNG`, `SVG`, & `GIF` images, but you can add whatever additional types you want. It also supports using [imgix](https://www.imgix.com/), [Thumbor](http://thumbor.org/), or [Sharp JS](https://nystudio107.com/blog/setting-up-your-own-image-transform-service) to create the responsive image transforms.

It's important to create optimized images for frontend delivery, especially for mobile devices. If you want to learn more about it, read the [Creating Optimized Images in Craft CMS](https://nystudio107.com/blog/creating-optimized-images-in-craft-cms) article.

Once ImageOptimize is installed, optimized versions of image transforms are created without you having to do anything. This makes it great for client-proofing websites.

ImageOptimize works equally well with both local and remote assets such as Amazon S3 buckets. It also works great with SPAs, giving you access to your optimized image URLs from GraphQL.

Brought to you by [nystudio107](https://nystudio107.com)
