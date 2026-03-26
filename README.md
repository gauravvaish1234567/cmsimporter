
## ext_m2_CmsImporter
This module Adds bin/magento command to import cms blocks/pages from default or given theme ID.
## Installation  
Use the package manager [composer](https://getcomposer.org/) to install the module.  
  
```bash  
composer require pinpoint/cmsimporter 
```  
## Usage
### Where to place cms files to be imported?
To import CMS blocks and pages into magento you must place a `cms_import` folder inside your theme (either your default theme or the `theme_id` you pass with the command).

You have two different folders from CMS Blocks and CMS Pages. You will end up with the following folder structure:
```bash
app/design/vendor/theme/cms_import/
├── blocks/
│   ├── indentifier.phtml
├── pages/
│   ├── indentifier.phtml
``` 
The `.phtml` file will contain the settings & content of that cms block/page.

### How to format a CMS block file
A cms block file should be named as the blocks' `identifier`. For example: `ooter_links_block.phtml`.
```php
<?php  
  $title = "Example Title"; //optional - default will split out identifier
  $isActive = 1; //optional - will default to 1
?>  
Some Example <span>Content</span><br />
For the CMS BLOCK
```

### How to format a CMS page file
A cms page file should be named as the pages' `identifier`. For example: `about-us.phtml`.
```php
<?php  
  $title = 'Example Title'; //optional - default will split out identifier
  $isActive = 1;  //optional - will default to 1
  $pageLayout = '2columns-right'; //optional - will default to 1column
  $metaKeywords = 'some keywords'; //optional
  $metaDescription = 'some description'; //optional
  $contentHeading = 'A section heading'; //optional
  $layoutXml = "  
  <referenceContainer name='example'></referenceContainer>  
  "; //optional
  $themeId = 4; //optional
?>  
some example<br/>
cms <span>page</span> content<br/> 
that will be<br/>  
shown
```

**YOU MUST ALWAYS HAVE THE `<?php ?>` TAGS IN YOUR FILE!**

### How to trigger an import
Pages and blocks will only be imported if the file has been updated after the block/page was last updated.

**YOU WILL NEED TO ASK SOMEONE TO ADD THIS TO A DEPLOYHQ STEP FOR THE PROJECT**

Import using default theme:
```bash
php bin/magento pinpoint:importcms
```
Import using `theme_id`:
```bash
php bin/magento pinpoint:importcms 4
```
# cmsimporter
